<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure at least one employee linked to sample user
        $user = User::where('email', 'karyawan@absensi.com')->first();

        if ($user) {
            Employee::firstOrCreate([
                'employee_code' => 'EMP001'
            ], [
                'user_id' => $user->id,
                'name' => $user->name,
                'employee_code' => 'EMP001',
                'department' => 'Umum',
                'position' => 'Staf',
                'phone' => '081234567890',
                'address' => 'Jl. Contoh No 1',
                'join_date' => now()->subYears(1)->toDateString(),
                'status' => 'active',
            ]);
        }

        // Create additional sample employees (with user accounts)
        $karyawanRole = Role::where('name', 'karyawan')->first();
        for ($i = 2; $i <= 6; $i++) {
            $code = 'EMP' . str_pad($i, 3, '0', STR_PAD_LEFT);
            $email = strtolower('employee' . $i . '@example.test');

            $user = User::firstOrCreate([
                'email' => $email,
            ], [
                'name' => 'Karyawan ' . $i,
                'password' => bcrypt('password123'),
            ]);

            if ($karyawanRole) {
                $user->syncRoles([$karyawanRole]);
            }

            Employee::firstOrCreate([
                'employee_code' => $code
            ], [
                'user_id' => $user->id,
                'name' => 'Karyawan ' . $i,
                'employee_code' => $code,
                'department' => ['IT', 'HR', 'Operasional', 'Keuangan', 'Support'][($i - 2) % 5],
                'position' => ['Staf', 'Koordinator', 'Supervisor'][($i - 2) % 3],
                'phone' => '0812' . rand(1000000, 9999999),
                'address' => 'Kota Contoh',
                'join_date' => now()->subMonths(rand(1, 36))->toDateString(),
                'status' => 'active',
            ]);
        }
    }
}
