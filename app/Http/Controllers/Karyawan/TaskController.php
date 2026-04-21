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
        if (!$employee) return redirect()->route('karyawan.dashboard');

        $request->validate([
            'tasks'              => 'required|array',
            'tasks.*.assignment_id' => 'required|exists:task_assignments,id',
            'tasks.*.is_done'    => 'boolean',
            'tasks.*.report'     => 'nullable|string',
        ]);

        $today = Carbon::today();

        foreach ($request->tasks as $taskData) {
            $assignment = TaskAssignment::find($taskData['assignment_id']);

            // Pastikan task ini milik karyawan ini
            if ($assignment->employee_id !== $employee->id) continue;

            $isDone = isset($taskData['is_done']) && $taskData['is_done'];

            // Cek laporan wajib
            if ($isDone && $assignment->isReportRequired()
                && empty($taskData['report'])) {
                return back()->withErrors([
                    'report' => 'Laporan wajib diisi untuk task: '
                        . $assignment->template->title
                ]);
            }

            TaskCompletion::updateOrCreate(
                [
                    'task_assignment_id' => $assignment->id,
                    'completion_date'    => $today->toDateString(),
                ],
                [
                    'employee_id'  => $employee->id,
                    'is_done'      => $isDone,
                    'report'       => $taskData['report'] ?? null,
                    'submitted_at' => now(),
                ]
            );

            // Update status assignment
            if ($isDone) {
                $assignment->update(['status' => 'done']);
            }
        }

        return back()->with('success', 'Task berhasil disimpan.');
    }
}