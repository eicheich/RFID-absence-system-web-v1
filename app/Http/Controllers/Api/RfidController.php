<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\RfidCard;
use App\Services\KpiService;
use App\Services\TaskService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RfidController extends Controller
{
    public function __construct(
        private KpiService  $kpiService,
        private TaskService $taskService
    ) {}
    /**
     * Proses 1.0 DFD — Registrasi kartu RFID baru
     * Arduino kirim UID kartu, HRD nanti assign ke karyawan lewat website
     */
    public function register(Request $request)
    {
        $request->validate(['uid' => 'required|string']);

        $uid  = strtoupper(trim($request->uid));
        $card = RfidCard::where('uid', $uid)->first();

        if ($card) {
            return response()->json([
                'success' => false,
                'message' => 'Kartu sudah terdaftar.',
                'data'    => ['uid' => $uid, 'status' => $card->status],
            ]);
        }

        $card = RfidCard::create([
            'uid'           => $uid,
            'employee_id'   => null,    // belum di-assign ke karyawan
            'status'        => 'active',
            'registered_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kartu berhasil didaftarkan. Silakan assign ke karyawan di website.',
            'data'    => ['uid' => $uid],
        ], 201);
    }

    /**
     * Proses 2.0 DFD — Tap-in atau tap-out otomatis
     * Kalau belum absen hari ini → tap-in
     * Kalau sudah tap-in → tap-out (cek KPI dulu)
     */
    public function checkin(Request $request)
    {
        $request->validate(['uid' => 'required|string']);

        $uid  = strtoupper(trim($request->uid));
        $card = RfidCard::with('employee')->where('uid', $uid)->first();

        // Kartu tidak ditemukan
        if (!$card) {
            return response()->json([
                'success' => false,
                'message' => 'Kartu tidak dikenali.',
                'action'  => 'unknown',
            ], 404);
        }

        // Kartu belum di-assign ke karyawan
        if (!$card->employee_id) {
            return response()->json([
                'success' => false,
                'message' => 'Kartu belum terdaftar ke karyawan manapun.',
                'action'  => 'unassigned',
            ]);
        }

        // Kartu nonaktif
        if ($card->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Kartu tidak aktif.',
                'action'  => 'inactive',
            ]);
        }

        $employee  = $card->employee;
        $today     = Carbon::today();
        $now       = Carbon::now();
        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        // Belum tap-in hari ini → proses tap-in
        if (!$attendance) {
            // Tentukan status: terlambat jika lewat jam 08:30
            $lateThreshold = Carbon::today()->setTime(8, 30);
            $status        = $now->gt($lateThreshold) ? 'late' : 'present';

            Attendance::create([
                'employee_id'  => $employee->id,
                'rfid_card_id' => $card->id,
                'date'         => $today,
                'tap_in'       => $now,
                'status' => $status,
            ]);

            return response()->json([
                'success' => true,
                'action' => 'tap_in',
                'status' => $status,
                'message' => 'Tap-in berhasil. ' . ($status === 'late' ? 'Anda terlambat.' : 'Tepat waktu!'),
                'employee' => $employee->name,
                'time' => $now->format('H:i:s'),
            ]);
        }
        // Tap-out sekarang hanya cek task, tidak cek KPI bulanan
        if ($attendance->tap_in && !$attendance->tap_out) {

            // Hanya cek task — KPI bulanan tidak memblokir tap-out
            $taskCheck = $this->taskService->canTapOut($employee->id, Carbon::today());

            if (!$taskCheck['allowed']) {
                $message = match ($taskCheck['reason']) {
                    'insufficient_tasks' => 'Selesaikan minimal 70% task hari ini. '
                        . 'Baru ' . number_format($taskCheck['rate'], 1) . '% selesai.',
                    'missing_reports'    => 'Ada ' . $taskCheck['missing']
                        . ' laporan task yang wajib diisi.',
                    default => 'Task belum memenuhi syarat tap-out.',
                };

                return response()->json([
                    'success'         => false,
                    'action'          => 'task_incomplete',
                    'message'         => $message,
                    'completion_rate' => $taskCheck['rate'],
                ]);
            }

            // Langsung proses tap-out tanpa cek KPI bulanan
            $workDuration = $attendance->tap_in->diffInMinutes(now());
            $kpi = $this->kpiService->calculate($employee->id); // tetap hitung untuk rekap

            $attendance->update([
                'tap_out'              => now(),
                'work_duration'        => $workDuration,
                'task_submitted'       => true,
                'task_completion_rate' => $taskCheck['rate'],
            ]);

            $carried = $this->taskService->carryOverUnfinishedTasks($employee->id, Carbon::today());

            return response()->json([
                'success'       => true,
                'action'        => 'tap_out',
                'message'       => 'Tap-out berhasil.',
                'employee'      => $employee->name,
                'time'          => now()->format('H:i:s'),
                'work_duration' => $workDuration . ' menit',
                'task_rate'     => number_format($taskCheck['rate'], 1) . '%',
                'kpi_score'     => $kpi->total_score,
                'carried_over'  => $carried,
            ]);
        }

        // Sudah tap-in dan tap-out keduanya
        return response()->json([
            'success' => false,
            'action'  => 'already_done',
            'message' => 'Absensi hari ini sudah lengkap.',
        ]);
    }

    /**
     * Proses 4.4 DFD — Cek status izin tap-out
     * Arduino bisa poll endpoint ini sebelum membuka pintu
     */
    public function status(string $uid)
    {
        $uid  = strtoupper(trim($uid));
        $card = RfidCard::with('employee')->where('uid', $uid)->first();

        if (!$card || !$card->employee_id) {
            return response()->json([
                'success' => false,
                'allowed' => false,
                'message' => 'Kartu tidak dikenali.',
            ], 404);
        }

        $kpi = $this->kpiService->calculate($card->employee->id);

        return response()->json([
            'success'    => true,
            'allowed'    => $kpi->tap_out_allowed,
            'kpi_score'  => $kpi->total_score,
            'status'     => $kpi->status,
            'employee'   => $card->employee->name,
        ]);
    }
}
