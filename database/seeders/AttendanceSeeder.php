<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\RfidCard;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::all();

        // Seed attendance for today and past 6 days (7 days total)
        for ($d = 0; $d < 7; $d++) {
            $date = Carbon::today()->subDays($d);

            foreach ($employees as $emp) {
                $card = RfidCard::where('employee_id', $emp->id)->first();

                // Randomize presence: mostly present, some absent
                $isPresent = rand(0, 10) > 1; // ~80% present

                if ($isPresent) {
                    $tapIn = $date->copy()->setTime(8, rand(0, 30));
                    $tapOut = $date->copy()->setTime(17, rand(0, 59));
                    $status = (rand(0, 10) > 2) ? 'present' : 'late';
                    $workDuration = $tapOut->diffInMinutes($tapIn);

                    Attendance::firstOrCreate([
                        'employee_id' => $emp->id,
                        'date' => $date->toDateString(),
                    ], [
                        'rfid_card_id' => $card?->id,
                        'tap_in' => $tapIn,
                        'tap_out' => $tapOut,
                        'status' => $status,
                        'work_duration' => $workDuration,
                    ]);
                } else {
                    Attendance::firstOrCreate([
                        'employee_id' => $emp->id,
                        'date' => $date->toDateString(),
                    ], [
                        'rfid_card_id' => $card?->id,
                        'tap_in' => null,
                        'tap_out' => null,
                        'status' => 'absent',
                        'work_duration' => 0,
                    ]);
                }
            }
        }
    }
}
