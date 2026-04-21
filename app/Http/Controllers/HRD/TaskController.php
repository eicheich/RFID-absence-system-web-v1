<?php

namespace App\Http\Controllers\HRD;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\TaskAssignment;
use App\Models\TaskTemplate;
use App\Services\TaskService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(private TaskService $taskService) {}

    public function index(Request $request)
    {
        $month     = $request->get('month', Carbon::now()->month);
        $year      = $request->get('year',  Carbon::now()->year);

        $templates = TaskTemplate::with('creator')
            ->whereYear('scheduled_date', $year)
            ->whereMonth('scheduled_date', $month)
            ->orderBy('scheduled_date')
            ->paginate(15);

        return view('hrd.tasks.index', compact('templates', 'month', 'year'));
    }

    public function create()
    {
        $employees   = Employee::where('status', 'active')->get();
        $departments = Employee::where('status', 'active')
            ->distinct()->pluck('department')->filter();

        return view('hrd.tasks.create', compact('employees', 'departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'  => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_type' => 'required|in:all,division,employee',
            'target_value' => 'nullable|string',
            'scheduled_date' => 'required|date',
            'report_required' => 'boolean',
            'report_instruction'=> 'nullable|string',
            'carry_over' => 'boolean',
        ]);

        $template = TaskTemplate::create([
            'created_by' => auth()->id(),
            'title'  => $request->title,
            'description' => $request->description,
            'target_type' => $request->target_type,
            'target_value' => $request->target_value,
            'scheduled_date' => $request->scheduled_date,
            'report_required' => $request->boolean('report_required'),
            'report_instruction' => $request->report_instruction,
            'carry_over'  => $request->boolean('carry_over', true),
        ]);

        // Distribute task ke karyawan yang ditarget
        $this->taskService->distributeTask($template);

        return redirect()->route('hrd.tasks.index')
            ->with('success', 'Task berhasil dibuat dan didistribusikan ke karyawan.');
    }

    public function show(TaskTemplate $task)
    {
        $task->load('assignments.employee', 'assignments.completion');

        $stats = [
            'total' => $task->assignments->count(),
            'done' => $task->assignments->filter(fn($a) =>
                $a->completion && $a->completion->is_done)->count(),
            'pending' => $task->assignments->where('status', 'pending')->count(),
            'carried' => $task->assignments->where('status', 'carried_over')->count(),
        ];

        return view('hrd.tasks.show', compact('task', 'stats'));
    }

    public function edit(TaskTemplate $task)
    {
        $employees = Employee::where('status', 'active')->get();
        $departments = Employee::where('status', 'active')
            ->distinct()->pluck('department')->filter();

        return view('hrd.tasks.edit', compact('task', 'employees', 'departments'));
    }

    public function update(Request $request, TaskTemplate $task)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'report_required' => 'boolean',
            'report_instruction'=> 'nullable|string',
            'carry_over'  => 'boolean',
            'status' => 'required|in:active,inactive',
        ]);

        $task->update([
            'title'  => $request->title,
            'description' => $request->description,
            'report_required' => $request->boolean('report_required'),
            'report_instruction' => $request->report_instruction,
            'carry_over' => $request->boolean('carry_over', true),
            'status' => $request->status,
        ]);

        return redirect()->route('hrd.tasks.index')
            ->with('success', 'Task berhasil diupdate.');
    }

    public function destroy(TaskTemplate $task)
    {
        $task->delete();
        return redirect()->route('hrd.tasks.index')
            ->with('success', 'Task berhasil dihapus.');
    }

    // Monitor progress task semua karyawan hari ini
    public function monitor(Request $request)
    {
        $date  = $request->get('date', Carbon::today()->toDateString());
        $employees = Employee::where('status', 'active')->get();

        $progress = $employees->map(function ($emp) use ($date) {
            $tasks = $this->taskService->getTasksForEmployee($emp->id, Carbon::parse($date));
            return [
                'employee' => $emp,
                'total' => $tasks->total,
                'done'  => $tasks->done,
                'completion_rate' => $tasks->completion_rate,
                'is_eligible' => $tasks->is_eligible,
            ];
        })->filter(fn($p) => $p['total'] > 0);

        return view('hrd.tasks.monitor', compact('progress', 'date'));
    }
}