<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RoleAndUserSeeder extends Seeder
{
    public function run(): void
    {
        // firstOrCreate → aman dijalankan berkali-kali, tidak akan duplikat
        $hrdRole      = Role::firstOrCreate(['name' => 'hrd',      'guard_name' => 'web']);
        $karyawanRole = Role::firstOrCreate(['name' => 'karyawan', 'guard_name' => 'web']);

        // Buat akun HRD (skip kalau sudah ada)
        $hrd = User::firstOrCreate(
            ['email' => 'hrd@absensi.com'],
            [
                'name'     => 'Admin HRD',
                'password' => bcrypt('password123'),
            ]
        );
        $hrd->syncRoles([$hrdRole]);

        $hrd2 = User::firstOrCreate(
            ['email' => 'hrd2@absensi.com'],
            [
                'name'     => 'Admin HRD 2',
                'password' => bcrypt('password123'),
            ]
        );
        $hrd2->syncRoles([$hrdRole]);

        // Buat akun karyawan contoh (skip kalau sudah ada)
        $karyawan = User::firstOrCreate(
            ['email' => 'karyawan@absensi.com'],
            [
                'name'     => 'Karyawan Contoh',
                'password' => bcrypt('password123'),
            ]
        );
        $karyawan->syncRoles([$karyawanRole]);
    }
}
