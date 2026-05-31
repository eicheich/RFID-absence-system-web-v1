<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleAndUserSeeder::class,
            KpiThresholdSeeder::class,
            EmployeeSeeder::class,
            RfidCardSeeder::class,
            AttendanceSeeder::class,
            TaskSeeder::class,
            KpiScoreSeeder::class,
        ]);
    }
}
