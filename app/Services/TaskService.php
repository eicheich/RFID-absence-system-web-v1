<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\TaskAssignment;
use App\Models\TaskCompletion;
use App\Models\TaskTemplate;
use Carbon\Carbon;

class TaskService
{
    /**
     * Distribute task template ke karyawan yang ditarget
     */
    public function distributeTask(TaskTemplate $template): void
    {
        $employees = $this->resolveTargetEmployees($template);

        foreach ($employees as $employee) {
            TaskAssignment::firstOrCreate(
                [
                    'task_template_id' => $template->id,
                    'employee_id' => $employee->id,
                    'scheduled_date' => $template->scheduled_date,
                ],
                [
                    'status' => 'pending',
                    'is_carry_over' => false,
                ]
            );
        }
    }

    /**
     * Ambil semua task untuk karyawan di tanggal tertentu
     * (termasuk carry-over dari hari sebelumnya)
     */
    public function getTasksForEmployee(int $employeeId, Carbon $date): object
    {
        $assignments = TaskAssignment::with(['template', 'completion'])
            ->where('employee_id', $employeeId)
            ->where('scheduled_date', $date->toDateString())
            ->where('status', '!=', 'carried_over')
            ->get();

        $total    = $assignments->count();
        $done     = $assignments->filter(
            fn($a) =>
            $a->completion && $a->completion->is_done
        )->count();
        $rate     = $total > 0 ? round(($done / $total) * 100, 2) : 0;

        return (object) [
            'assignments' => $assignments,
            'total' => $total,
            'done' => $done,
            'completion_rate' => $rate,
            'is_eligible' => $rate >= 70,
        ];
    }

    /**
     * Cek apakah karyawan boleh tap-out
     * Task wajib diisi minimal 70% + laporan wajib harus diisi
     */
    public function canTapOut(int $employeeId, Carbon $date): array
    {
        $tasks = $this->getTasksForEmployee($employeeId, $date);

        // Kalau tidak ada task hari ini → boleh tap-out
        if ($tasks->total === 0) {
            return ['allowed' => true, 'reason' => 'no_tasks', 'rate' => 0];
        }

        // Cek laporan wajib yang belum diisi
        $missingReports = $tasks->assignments->filter(function ($a) {
            if (!$a->completion || !$a->completion->is_done) return false;
            // Task selesai tapi laporan wajib belum diisi
            return $a->isReportRequired() && empty($a->completion->report);
        });

        if ($missingReports->count() > 0) {
            return [
                'allowed' => false,
                'reason' => 'missing_reports',
                'rate'  => $tasks->completion_rate,
                'missing' => $missingReports->count(),
            ];
        }

        if (!$tasks->is_eligible) {
            return [
                'allowed' => false,
                'reason' => 'insufficient_tasks',
                'rate' => $tasks->completion_rate,
                'done' => $tasks->done,
                'total' => $tasks->total,
            ];
        }

        return [
            'allowed' => true,
            'reason' => 'eligible',
            'rate' => $tasks->completion_rate,
        ];
    }

    /**
     * Carry-over task yang belum selesai ke hari berikutnya
     * Dipanggil saat tap-out berhasil
     */
    public function carryOverUnfinishedTasks(int $employeeId, Carbon $date): int
    {
        $nextDay     = $date->copy()->addWeekday(); // skip weekend
        $unfinished  = TaskAssignment::with('template')
            ->where('employee_id', $employeeId)
            ->where('scheduled_date', $date->toDateString())
            ->where('status', 'pending')
            ->get()
            ->filter(fn($a) => $a->isCarryOver());

        $carried = 0;
        foreach ($unfinished as $assignment) {
            // Tandai task lama sebagai carried_over
            $assignment->update(['status' => 'carried_over']);

            // Buat task baru di hari berikutnya
            TaskAssignment::firstOrCreate(
                [
                    'task_template_id' => $assignment->task_template_id,
                    'employee_id'  => $employeeId,
                    'scheduled_date' => $nextDay->toDateString(),
                ],
                [
                    'status' => 'pending',
                    'is_carry_over' => true,
                    'original_assignment_id' => $assignment->id,
                    'report_required' => $assignment->report_required,
                    'carry_over' => $assignment->carry_over,
                ]
            );
            $carried++;
        }

        return $carried;
    }

    /**
     * Resolve daftar karyawan berdasarkan target_type template
     */
    private function resolveTargetEmployees(TaskTemplate $template)
    {
        return match ($template->target_type) {
            'all'      => Employee::where('status', 'active')->get(),
            'division' => Employee::where('status', 'active')
                ->where('department', $template->target_value)
                ->get(),
            'employee' => Employee::where('status', 'active')
                ->where('id', $template->target_value)
                ->get(),
            default    => collect(),
        };
    }
}
