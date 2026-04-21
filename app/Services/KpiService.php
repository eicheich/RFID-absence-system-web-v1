<?php

namespace App\Services;

use App\Models\KpiScore;
use App\Models\KpiThreshold;
use App\Models\TaskAssignment;
use App\Models\TaskCompletion;
use Carbon\Carbon;

class KpiService
{
    public function __construct(private TaskService $taskService) {}

    /**
     * Hitung KPI berdasarkan task completion rate bulan ini
     */
    public function calculate(int $employeeId): KpiScore
    {
        $now = Carbon::now();
        $year = $now->year;
        $month = $now->month;

        // Ambil semua hari kerja bulan ini yang sudah lewat
        $start = Carbon::create($year, $month, 1);
        $end   = $now->copy()->startOfDay();

        $totalDays = 0;
        $eligibleDays = 0; // hari dengan task completion >= 70%
        $totalRate = 0;

        $current = $start->copy();
        while ($current->lte($end)) {
            if ($current->isWeekday()) {
                $tasks = $this->taskService->getTasksForEmployee($employeeId, $current);

                // Hanya hitung hari yang punya task
                if ($tasks->total > 0) {
                    $totalDays++;
                    $totalRate += $tasks->completion_rate;

                    if ($tasks->is_eligible) {
                        $eligibleDays++;
                    }
                }
            }
            $current->addDay();
        }

        // KPI = rata-rata completion rate semua hari yang punya task
        $taskScore = $totalDays > 0
            ? round($totalRate / $totalDays, 2)
            : 100; // kalau tidak ada task sama sekali, full score

        // Validasi threshold
        $isValid = $this->validateThreshold($taskScore);
        $tapOutAllowed = $isValid;

        $kpi = KpiScore::updateOrCreate(
            [
                'employee_id' => $employeeId,
                'year' => $year,
                'month' => $month,
            ],
            [
                'attendance_score' => $taskScore,   // repurpose kolom ini
                'punctuality_score' => $eligibleDays > 0
                    ? round(($eligibleDays / max($totalDays, 1)) * 100, 2)
                    : 0,
                'total_score' => $taskScore,
                'status' => $isValid ? 'valid' : 'invalid',
                'tap_out_allowed' => $tapOutAllowed,
                'calculated_at' => now(),
            ]
        );

        return $kpi;
    }

    public function validateThreshold(float $taskScore): bool
    {
        $threshold = KpiThreshold::where('metric', 'task_completion')
            ->where('is_active', true)
            ->first();

        if (!$threshold) return true;

        return $taskScore >= $threshold->min_value;
    }
}
