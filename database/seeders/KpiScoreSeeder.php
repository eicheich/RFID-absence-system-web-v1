<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KpiScore;
use App\Models\Employee;
use Carbon\Carbon;

class KpiScoreSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::all();
        $now = Carbon::now();

        foreach ($employees as $emp) {
            $total = rand(70, 100);
            KpiScore::firstOrCreate([
                'employee_id' => $emp->id,
                'year' => $now->year,
                'month' => $now->month,
            ], [
                'attendance_score' => rand(70, 100),
                'punctuality_score' => rand(70, 100),
                'total_score' => $total,
                'status' => $total >= 80 ? 'valid' : 'invalid',
                'tap_out_allowed' => true,
                'calculated_at' => now(),
            ]);
        }
    }
}
