@extends('layouts.karyawan')
@section('title', 'Dashboard')

@section('content')

    {{-- Status Absensi Hari Ini --}}
    <div class="card border-0 rounded-3 shadow-sm mb-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="d-flex align-items-center gap-4 p-4"
                style="background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);">
                <div>
                    @if ($todayAttendance)
                        @if ($todayAttendance->tap_out)
                            <div class="badge rounded-pill px-3 py-2 mb-2"
                                style="background:#dcfce7;color:#16a34a;font-size:0.8rem">
                                <i class="bi bi-check-circle me-1"></i>Absensi Selesai
                            </div>
                        @else
                            <div class="badge rounded-pill px-3 py-2 mb-2"
                                style="background:#fef9c3;color:#ca8a04;font-size:0.8rem">
                                <i class="bi bi-dot me-1"></i>Sedang Bekerja
                            </div>
                        @endif
                    @else
                        <div class="badge rounded-pill px-3 py-2 mb-2"
                            style="background:#fee2e2;color:#dc2626;font-size:0.8rem">
                            <i class="bi bi-x-circle me-1"></i>Belum Absen
                        </div>
                    @endif
                    <h4 class="text-white fw-semibold mb-1">Halo, {{ $employee->name }}!</h4>
                    <p class="text-white-50 mb-0 small">{{ $employee->position ?? 'Karyawan' }} ·
                        {{ $employee->department ?? '-' }}</p>
                </div>
                <div class="ms-auto text-end">
                    @if ($todayAttendance)
                        <div class="text-white-50 small mb-1">Tap In</div>
                        <div class="text-white fw-semibold fs-5">
                            {{ $todayAttendance->tap_in?->format('H:i') ?? '-' }}
                        </div>
                        @if ($todayAttendance->tap_out)
                            <div class="text-white-50 small mt-2 mb-1">Tap Out</div>
                            <div class="text-white fw-semibold fs-5">
                                {{ $todayAttendance->tap_out->format('H:i') }}
                            </div>
                        @endif
                    @else
                        <div class="text-white-50 small">Tap kartu RFID</div>
                        <div class="text-white-50 small">untuk absen</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    {{-- Notifikasi decline --}}
    @php
        $declinedTasks = \App\Models\TaskCompletion::where('employee_id', $employee->id)
            ->where('review_status', 'declined')
            ->whereHas('review', fn($q) => $q->whereDate('reviewed_at', '>=', now()->subDays(3)))
            ->with('assignment.template', 'review')
            ->get();
    @endphp

    @if ($declinedTasks->count() > 0)
        <div class="alert alert-danger border-0 rounded-3 shadow-sm mb-4">
            <div class="fw-semibold mb-2">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{ $declinedTasks->count() }} laporan task kamu di-decline oleh HRD
            </div>
            @foreach ($declinedTasks as $declined)
                <div class="d-flex align-items-start gap-2 mb-2 pb-2 border-bottom">
                    <i class="bi bi-x-circle-fill text-danger mt-1" style="font-size:14px"></i>
                    <div>
                        <div class="small fw-medium">{{ $declined->assignment->template->title }}</div>
                        <div class="small text-muted">{{ $declined->review_note }}</div>
                        <div class="small text-muted mt-1">
                            <i class="bi bi-arrow-repeat me-1"></i>
                            Task revisi ditambahkan ke
                            <strong>
                                {{ $declined->review->revision_due_date?->translatedFormat('l, d F Y') }}
                            </strong>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
    {{-- Stat Cards Bulan Ini --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card stat-card p-4 h-100">
                <div class="icon-box bg-success bg-opacity-10 text-success mb-3">
                    <i class="bi bi-person-check"></i>
                </div>
                <div class="text-muted small">Hadir Bulan Ini</div>
                <div class="fs-3 fw-semibold text-dark">{{ $monthlyStats['present'] }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card p-4 h-100">
                <div class="icon-box bg-danger bg-opacity-10 text-danger mb-3">
                    <i class="bi bi-person-x"></i>
                </div>
                <div class="text-muted small">Tidak Hadir</div>
                <div class="fs-3 fw-semibold text-dark">{{ $monthlyStats['absent'] }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card p-4 h-100">
                <div class="icon-box bg-warning bg-opacity-10 text-warning mb-3">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div class="text-muted small">Terlambat</div>
                <div class="fs-3 fw-semibold text-dark">{{ $monthlyStats['late'] }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card p-4 h-100">
                <div class="icon-box bg-primary bg-opacity-10 text-primary mb-3">
                    <i class="bi bi-graph-up"></i>
                </div>
                <div class="text-muted small">Skor KPI</div>
                <div class="fs-3 fw-semibold {{ ($kpi?->total_score ?? 0) >= 80 ? 'text-success' : 'text-danger' }}">
                    {{ $kpi ? number_format($kpi->total_score, 1) : '-' }}
                </div>
            </div>
        </div>
    </div>

    {{-- KPI Status + Riwayat Absensi --}}
    <div class="row g-3">

        {{-- KPI Card --}}
        <div class="col-md-4">
            <div class="card table-card h-100 p-4">
                <h6 class="fw-semibold mb-3">Status KPI Bulan Ini</h6>
                @if ($kpi)
                    <div class="text-center my-3">
                        <div class="kpi-ring mx-auto mb-2"
                            style="background: {{ $kpi->total_score >= 80 ? '#dcfce7' : '#fee2e2' }}">
                            <span style="color: {{ $kpi->total_score >= 80 ? '#16a34a' : '#dc2626' }}">
                                {{ number_format($kpi->total_score, 0) }}
                            </span>
                            <small class="fw-normal text-muted" style="font-size:0.75rem">/ 100</small>
                        </div>
                        <span
                            class="badge rounded-pill px-3 py-2 {{ $kpi->status === 'valid' ? 'badge-valid' : 'badge-invalid' }}">
                            {{ $kpi->status === 'valid' ? 'KPI Valid' : 'KPI Tidak Valid' }}
                        </span>
                    </div>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>Kehadiran</span>
                            <span class="fw-medium text-dark">{{ number_format($kpi->attendance_score, 1) }}%</span>
                        </div>
                        <div class="progress mb-3" style="height:6px;border-radius:4px">
                            <div class="progress-bar bg-success" style="width:{{ $kpi->attendance_score }}%"></div>
                        </div>
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>Ketepatan Waktu</span>
                            <span class="fw-medium text-dark">{{ number_format($kpi->punctuality_score, 1) }}%</span>
                        </div>
                        <div class="progress" style="height:6px;border-radius:4px">
                            <div class="progress-bar bg-primary" style="width:{{ $kpi->punctuality_score }}%"></div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-graph-up fs-2 d-block mb-2"></i>
                        <small>Belum ada data KPI bulan ini</small>
                    </div>
                @endif
            </div>
        </div>

        {{-- Riwayat Absensi --}}
        <div class="col-md-8">
            <div class="card table-card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 px-4">
                    <h6 class="mb-0 fw-semibold">Absensi Terbaru</h6>
                    <a href="{{ route('karyawan.attendance') }}" class="btn btn-sm btn-outline-secondary">
                        Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Tap In</th>
                                <th>Tap Out</th>
                                <th>Durasi</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentAttendances as $att)
                                <tr>
                                    <td>
                                        <div class="fw-medium">{{ $att->date->format('d M Y') }}</div>
                                        <small class="text-muted">{{ $att->date->translatedFormat('l') }}</small>
                                    </td>
                                    <td>{{ $att->tap_in?->format('H:i') ?? '-' }}</td>
                                    <td>{{ $att->tap_out?->format('H:i') ?? '-' }}</td>
                                    <td class="text-muted">
                                        @if ($att->work_duration)
                                            {{ floor($att->work_duration / 60) }}j {{ $att->work_duration % 60 }}m
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $cls = match ($att->status) {
                                                'present' => 'badge-present',
                                                'late' => 'badge-late',
                                                'absent' => 'badge-absent',
                                                default => 'badge-blocked',
                                            };
                                        @endphp
                                        <span class="badge rounded-pill px-3 {{ $cls }}">
                                            {{ ucfirst($att->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="bi bi-calendar-x d-block fs-3 mb-1"></i>
                                        Belum ada riwayat absensi
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection
