@extends('layouts.hrd')
@section('title', 'Data Absensi')

@section('content')

    

    {{-- Filter Tanggal --}}
    <div class="card form-card mb-4 p-4">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Pilih Tanggal</label>
                <input type="date" name="date" value="{{ $date }}" class="form-control">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-1"></i> Cari
                </button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('hrd.attendances.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                </a>
            </div>
            <div class="col-md-2">
                <a href="{{ route('hrd.export.attendance', ['date' => $date]) }}" class="btn btn-success w-100">
                    <i class="bi bi-file-earmark-excel me-1"></i> Export
                </a>
            </div>
        </form>
    </div>

    {{-- Summary Hari Ini --}}
    @php
        $totalPresent = $attendances->where('status', 'present')->count();
        $totalLate = $attendances->where('status', 'late')->count();
        $totalAbsent = $attendances->where('status', 'absent')->count();
        $totalBlocked = $attendances->where('status', 'blocked')->count();
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card stat-card p-3 text-center">
                <div class="text-muted small mb-1">Hadir</div>
                <div class="fs-3 fw-semibold text-success">{{ $totalPresent }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card p-3 text-center">
                <div class="text-muted small mb-1">Terlambat</div>
                <div class="fs-3 fw-semibold text-warning">{{ $totalLate }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card p-3 text-center">
                <div class="text-muted small mb-1">Tidak Hadir</div>
                <div class="fs-3 fw-semibold text-danger">{{ $totalAbsent }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card p-3 text-center">
                <div class="text-muted small mb-1">Diblokir</div>
                <div class="fs-3 fw-semibold text-secondary">{{ $totalBlocked }}</div>
            </div>
        </div>
    </div>

    {{-- Tabel Absensi --}}
    <div class="card table-card">
        <div class="card-header bg-white d-flex align-items-center justify-content-between py-3 px-4">
            <div>
                <h6 class="mb-0 fw-semibold">Absensi Tanggal</h6>
                <small class="text-muted">
                    {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}
                </small>
            </div>
            <span class="badge bg-primary rounded-pill">
                {{ $attendances->total() }} karyawan
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Karyawan</th>
                        <th>Departemen</th>
                        <th>Tap In</th>
                        <th>Tap Out</th>
                        <th>Durasi Kerja</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $att)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary
                                        d-flex align-items-center justify-content-center fw-semibold"
                                        style="width:34px;height:34px;font-size:0.8rem;flex-shrink:0">
                                        {{ strtoupper(substr($att->employee->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-medium">{{ $att->employee->name }}</div>
                                        <small class="text-muted font-monospace">
                                            {{ $att->employee->employee_code }}
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-muted">{{ $att->employee->department ?? '-' }}</td>
                            <td>
                                @if ($att->tap_in)
                                    <span class="fw-medium">{{ $att->tap_in->format('H:i') }}</span>
                                    <br><small class="text-muted">{{ $att->tap_in->format('s') }}s</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if ($att->tap_out)
                                    <span class="fw-medium">{{ $att->tap_out->format('H:i') }}</span>
                                    <br><small class="text-muted">{{ $att->tap_out->format('s') }}s</small>
                                @else
                                    <span class="badge rounded-pill badge-blocked">Belum tap out</span>
                                @endif
                            </td>
                            <td>
                                @if ($att->work_duration)
                                    <span class="fw-medium">
                                        {{ floor($att->work_duration / 60) }}j
                                        {{ $att->work_duration % 60 }}m
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
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
                                <span class="badge rounded-pill px-3 py-2 {{ $cls }}">
                                    {{ ucfirst($att->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('hrd.attendances.show', $att->employee) }}"
                                    class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-calendar-x fs-3 d-block mb-2"></i>
                                Tidak ada data absensi untuk tanggal ini
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $attendances->appends(['date' => $date])->links() }}
        </div>
    </div>

@endsection
