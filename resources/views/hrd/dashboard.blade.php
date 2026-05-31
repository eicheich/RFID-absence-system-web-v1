@extends('layouts.hrd')
@section('title', 'Dashboard')

@section('content')

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card">
                <div class="metric-icon" style="background:#4f6bd8"><i class="bi bi-person"></i></div>
                <div class="metric-value">{{ $stats['total_employees'] }}</div>
                <div class="metric-label">Total Karyawan</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card">
                <div class="metric-icon" style="background:#ec4899"><i class="bi bi-bell"></i></div>
                <div class="metric-value">{{ $stats['present_today'] }}</div>
                <div class="metric-label">Absensi Hari Ini</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card">
                <div class="metric-icon" style="background:#84cc16"><i class="bi bi-stopwatch"></i></div>
                <div class="metric-value">{{ $stats['late_today'] }}</div>
                <div class="metric-label">Terlambat</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="metric-card">
                <div class="metric-icon" style="background:#f59e0b"><i class="bi bi-exclamation-triangle"></i></div>
                <div class="metric-value">{{ $stats['kpi_invalid'] }}</div>
                <div class="metric-label">KPI Invalid</div>
            </div>
        </div>
    </div>

    <div class="panel mb-4">
        <div class="px-4 pt-4 pb-3 d-flex flex-wrap align-items-end justify-content-between gap-2">
            <div>
                <h5 class="mb-1" style="font-weight:600">Absensi Hari Ini</h5>
                <div class="muted" style="font-size:0.86rem">{{ now()->translatedFormat('l, d F Y') }}</div>
            </div>
            <a href="{{ route('hrd.attendances.index') }}" class="btn btn-sm"
                style="background:rgba(93,120,255,0.18);border:1px solid rgba(93,120,255,0.35);color:#dce4ff;border-radius:10px">
                Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="table-responsive">
            <table class="table dash-table mb-0">
                <thead>
                    <tr>
                        <th>Karyawan</th>
                        <th>Departemen</th>
                        <th>Tap-In</th>
                        <th>Tap-Out</th>
                        <th>Durasi</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentAttendances as $att)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $att->employee->name }}</div>
                            </td>
                            <td class="muted">{{ $att->employee->department ?? '-' }}</td>
                            <td>{{ $att->tap_in?->format('H:i') ?? '-' }}</td>
                            <td>{{ $att->tap_out?->format('H:i') ?? '-' }}</td>
                            <td class="muted">
                                {{ $att->work_duration ? floor($att->work_duration / 60) . 'h ' . $att->work_duration % 60 . 'm' : '-' }}
                            </td>
                            <td>
                                @php
                                    $statusClass = 'status-off';
                                    $statusText = 'Belum Lengkap';

                                    if ($att->status === 'present') {
                                        $statusClass = 'status-ok';
                                        $statusText = 'Tepat Waktu';
                                    }

                                    if ($att->status === 'late') {
                                        $statusClass = 'status-late';
                                        $statusText = 'Terlambat';
                                    }
                                @endphp
                                <span class="status-pill {{ $statusClass }}">{{ $statusText }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 muted">
                                Belum ada absensi hari ini
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
