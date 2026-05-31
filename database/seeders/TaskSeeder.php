<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TaskTemplate;
use App\Models\TaskAssignment;
use App\Models\TaskCompletion;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $hrd = User::where('email', 'hrd@absensi.com')->first();
        $employees = Employee::all();

        if ($employees->isEmpty()) {
            return;
        }

        // Create multiple templates with varied targets and statuses
        $templatesData = [
            [
                'title' => 'Laporan Harian',
                'description' => 'Isi laporan singkat tugas harian',
                'target_type' => 'all',
                'report_required' => true,
                'carry_over' => false,
                'status' => 'active',
                'scheduled_offset_days' => 0,
            ],
            [
                'title' => 'Cek Inventaris',
                'description' => 'Periksa kondisi inventaris di area kerja',
                'target_type' => 'division',
                'target_value' => 'IT',
                'report_required' => false,
                'carry_over' => false,
                'status' => 'active',
                'scheduled_offset_days' => 1,
            ],
            [
                'title' => 'Laporan Mingguan',
                'description' => 'Ringkasan mingguan pekerjaan',
                'target_type' => 'all',
                'report_required' => true,
                'carry_over' => false,
                'status' => 'active',
                'scheduled_offset_days' => 7,
            ],
            [
                'title' => 'Update Database',
                'description' => 'Perbarui backup dan migrasi kecil',
                'target_type' => 'employee',
                'target_value' => null,
                'report_required' => false,
                'carry_over' => true,
                'status' => 'inactive',
                'scheduled_offset_days' => 3,
            ],
            [
                'title' => 'Survey Kepuasan',
                'description' => 'Kumpulkan feedback singkat',
                'target_type' => 'all',
                'report_required' => false,
                'carry_over' => false,
                'status' => 'active',
                'scheduled_offset_days' => -2,
            ],
        ];

        $templates = [];
        foreach ($templatesData as $data) {
            $scheduled = Carbon::today()->addDays($data['scheduled_offset_days']);
            $tpl = TaskTemplate::firstOrCreate([
                'title' => $data['title'],
                'scheduled_date' => $scheduled->toDateString(),
            ], [
                'created_by' => $hrd?->id ?? 1,
                'description' => $data['description'] ?? null,
                'target_type' => $data['target_type'] ?? 'all',
                'target_value' => $data['target_value'] ?? null,
                'scheduled_date' => $scheduled->toDateString(),
                'report_required' => $data['report_required'] ?? false,
                'carry_over' => $data['carry_over'] ?? false,
                'status' => $data['status'] ?? 'active',
            ]);

            $templates[] = $tpl;
        }

        // Assign templates to many employees so progress shows
        foreach ($employees as $emp) {
            foreach ($templates as $tpl) {
                $assignment = TaskAssignment::firstOrCreate([
                    'task_template_id' => $tpl->id,
                    'employee_id' => $emp->id,
                    'scheduled_date' => $tpl->scheduled_date,
                ], [
                    'report_required' => $tpl->report_required,
                    'carry_over' => $tpl->carry_over,
                    'status' => 'pending',
                ]);

                // Randomly mark some completions
                if (rand(0, 2) > 0) {
                    TaskCompletion::firstOrCreate([
                        'task_assignment_id' => $assignment->id,
                        'employee_id' => $emp->id,
                    ], [
                        'completion_date' => Carbon::parse($tpl->scheduled_date)->toDateString(),
                        'is_done' => rand(0, 1) === 1,
                        'report' => 'Auto-seed report sample.',
                        'submitted_at' => Carbon::now()->subDays(rand(0, 3)),
                        'review_status' => ['pending', 'approved', 'declined'][rand(0, 2)],
                    ]);
                }
            }
        }
    }
}
