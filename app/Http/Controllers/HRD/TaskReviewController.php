<?php

namespace App\Http\Controllers\HRD;

use App\Http\Controllers\Controller;
use App\Models\TaskAssignment;
use App\Models\TaskCompletion;
use App\Models\TaskReview;
use App\Services\TaskService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TaskReviewController extends Controller
{
    public function __construct(private TaskService $taskService) {}

    /**
     * Daftar semua laporan yang perlu direview
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');
        $date = $request->get('date', Carbon::today()->toDateString());

        $completions = TaskCompletion::with([
            'employee',
            'assignment.template',
            'review'
        ])
            ->where('is_done', true)
            ->when($status !== 'all', fn($q) => $q->where('review_status', $status))
            ->whereDate('completion_date', $date)
            ->latest('submitted_at')
            ->paginate(15);

        $counts = [
            'pending'  => TaskCompletion::where('is_done', true)
                ->where('review_status', 'pending')->count(),
            'approved' => TaskCompletion::where('is_done', true)
                ->where('review_status', 'approved')->count(),
            'declined' => TaskCompletion::where('is_done', true)
                ->where('review_status', 'declined')->count(),
        ];

        return view('hrd.task-reviews.index', compact('completions', 'status', 'date', 'counts'));
    }

    /**
     * Detail laporan satu task
     */
    public function show(TaskCompletion $completion)
    {
        $completion->load('employee', 'assignment.template', 'review.reviewer');
        return view('hrd.task-reviews.show', compact('completion'));
    }

    /**
     * Approve laporan — tidak ada efek ke KPI, hanya status berubah
     */
    public function approve(Request $request, TaskCompletion $completion)
    {
        $completion->update(['review_status' => 'approved', 'review_note' => null]);

        // Catat siapa yang approve
        TaskReview::updateOrCreate(
            ['task_completion_id' => $completion->id],
            [
                'task_assignment_id' => $completion->task_assignment_id,
                'employee_id' => $completion->employee_id,
                'reviewed_by' => auth()->id(),
                'status'  => 'approved',
                'review_date' => Carbon::today(),
                'reviewed_at' => now(),
                'note' => null,
            ]
        );

        return back()->with('success', 'Laporan disetujui.');
    }

    /**
     * Decline laporan — buat task revisi di hari berikutnya
     */
    public function decline(Request $request, TaskCompletion $completion)
    {
        $request->validate([
            'note' => 'required|string|min:10',
        ], [
            'note.required' => 'Catatan alasan decline wajib diisi.',
            'note.min' => 'Catatan minimal 10 karakter.',
        ]);

        $completion->load('assignment.template', 'employee');

        $nextWorkday = Carbon::today()->addWeekday();

        // Buat task revisi di hari berikutnya
        $revisionAssignment = TaskAssignment::create([
            'task_template_id' => $completion->assignment->task_template_id,
            'employee_id' => $completion->employee_id,
            'scheduled_date' => $nextWorkday->toDateString(),
            'is_carry_over' => true,
            'original_assignment_id' => $completion->task_assignment_id,
            'report_required' => true,   // revisi wajib laporan
            'carry_over'  => false,  // revisi tidak carry-over lagi
            'status' => 'pending',
        ]);

        // Update completion & catat review
        $completion->update([
            'review_status' => 'declined',
            'review_note'   => $request->note,
        ]);

        TaskReview::updateOrCreate(
            ['task_completion_id' => $completion->id],
            [
                'task_assignment_id'   => $completion->task_assignment_id,
                'employee_id'          => $completion->employee_id,
                'reviewed_by'          => auth()->id(),
                'status'               => 'declined',
                'note'                 => $request->note,
                'review_date'          => Carbon::today(),
                'reviewed_at'          => now(),
                'revision_due_date'    => $nextWorkday,
                'revision_assignment_id' => $revisionAssignment->id,
            ]
        );

        return back()->with(
            'success',
            'Laporan di-decline. Task revisi sudah ditambahkan ke hari ' .
                $nextWorkday->translatedFormat('l, d F Y') . '.'
        );
    }
}
