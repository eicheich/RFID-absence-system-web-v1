<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\TaskAssignment;
use App\Models\TaskCompletion;
use App\Services\TaskService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(private TaskService $taskService) {}

    public function index(Request $request)
    {
        $employee = auth()->user()->employee;
        if (!$employee) return redirect()->route('karyawan.dashboard');

        $date  = Carbon::parse($request->get('date', Carbon::today()->toDateString()));
        $tasks = $this->taskService->getTasksForEmployee($employee->id, $date);

        // Cek apakah sudah bisa tap-out
        $tapOutCheck = $this->taskService->canTapOut($employee->id, $date);

        return view('karyawan.tasks', compact('tasks', 'date', 'tapOutCheck'));
    }

    public function submit(Request $request)
    {
        $employee = auth()->user()->employee;

        $request->validate([
            'tasks'                     => 'required|array',
            'tasks.*.assignment_id'     => 'required|exists:task_assignments,id',
            'tasks.*.is_done'           => 'boolean',
            'tasks.*.report'            => 'nullable|string',
            'tasks.*.attachment_url'    => 'nullable|url',
            'tasks.*.attachment_file'   => 'nullable|file|max:10240', // max 10MB
        ]);

        $today = Carbon::today();

        foreach ($request->tasks as $i => $taskData) {
            $assignment = TaskAssignment::find($taskData['assignment_id']);
            if ($assignment->employee_id !== $employee->id) continue;

            $isDone = isset($taskData['is_done']) && $taskData['is_done'];

            if ($isDone && $assignment->isReportRequired() && empty($taskData['report'])) {
                return back()->withErrors([
                    'report' => 'Laporan wajib diisi untuk task: ' . $assignment->template->title
                ]);
            }

            // Handle file upload
            $attachmentPath = null;
            $attachmentName = null;
            if ($request->hasFile("tasks.$i.attachment_file")) {
                $file = $request->file("tasks.$i.attachment_file");
                $attachmentName = $file->getClientOriginalName();
                $attachmentPath = $file->store("task-attachments/{$employee->id}", 'public');
            }

            TaskCompletion::updateOrCreate(
                [
                    'task_assignment_id' => $assignment->id,
                    'completion_date'    => $today->toDateString(),
                ],
                [
                    'employee_id'     => $employee->id,
                    'is_done'         => $isDone,
                    'report'          => $taskData['report'] ?? null,
                    'attachment_path' => $attachmentPath,
                    'attachment_name' => $attachmentName,
                    'attachment_url'  => $taskData['attachment_url'] ?? null,
                    'submitted_at'    => now(),
                    'review_status'   => 'pending',
                    'review_note'     => null,
                ]
            );

            if ($isDone) $assignment->update(['status' => 'done']);
        }

        return back()->with('success', 'Task berhasil disimpan.');
    }
}
