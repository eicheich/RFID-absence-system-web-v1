<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RfidCard;
use App\Models\Employee;
use Illuminate\Support\Str;

class RfidCardSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::all();

        foreach ($employees as $idx => $emp) {
            RfidCard::firstOrCreate([
                'employee_id' => $emp->id,
            ], [
                'uid' => strtoupper(Str::random(10)),
                'employee_id' => $emp->id,
                'status' => 'active',
                'registered_at' => now()->subDays(rand(1, 365)),
            ]);
        }
    }
}
