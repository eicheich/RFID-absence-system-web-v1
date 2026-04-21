@extends('layouts.hrd')
@section('title', 'Detail Absensi')

@section('content')

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('hrd.attendances.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h5 class="mb-0 fw-semibold">Detail Absensi — {{ $employee->name }}</h5>
        <small class="text-muted">{{ $employee->department ?? '-' }} · {{ $employee->position ?? '-' }}</small>
    </div>
</div>

{{-- Filter Bulan --}}
<div class="card form-card mb-4 p-4">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label">Bulan</label>
            <select name="month" class="form-select">
                @foreach(range(1, 12) as $m)
                <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Tahun</label>
            <select name="year" class="form-select">
                @foreach(range(date('Y')-1, date('Y')) as $y)
                <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-search me-1"></i> Filter
            </button>
        </div>
    </form>
</div>

{{-- Summary --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card stat-card p-3 text-center">
            <div class="text-muted small">Hadir</div>
            <div class="fs-3 fw-semibold text-success">{{ $summary['present'] }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card p-3 text-center">
            <div class="text-muted small">Tidak Hadir</div>
            <div class="fs-3 fw-semibold text-danger">{{ $summary['absent'] }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card p-3 text-center">
            <div class="text-muted small">Terlambat</div>
            <div class="fs-3 fw-semibold text-warning">{{ $summary['late'] }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card p-3 text-center">
            <div class="text-muted small">Total Jam Kerja</div>
            <div class="fs-3 fw-semibold text-primary">
                {{ floor(collect($attendances)->sum('work_duration') / 60) }}j
            </div>
        </div>
    </div>
</div>

{{-- Tabel Detail --}}
<div class="card table-card">
    <div class="card-header bg-white py-3 px-4">
        <h6 class="mb-0 fw-semibold">
            Rekap
            {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }}
            {{ $year }}
        </h6>
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
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $att)
                <tr>
                    <td>
                        <div class="fw-medium">{{ $att->date->format('d M Y') }}</div>
                        <small class="text-muted">{{ $att->date->translatedFormat('l') }}</small>
                    </td>
                    <td>{{ $att->tap_in?->format('H:i:s') ?? '-' }}</td>
                    <td>{{ $att->tap_out?->format('H:i:s') ?? '-' }}</td>
                    <td>
                        @if($att->work_duration)
                            {{ floor($att->work_duration / 60) }}j {{ $att->work_duration % 60 }}m
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        @php
                            $cls = match($att->status) {
                                'present' => 'badge-present',
                                'late'    => 'badge-late',
                                'absent'  => 'badge-absent',
                                default   => 'badge-blocked',
                            };
                        @endphp
                        <span class="badge rounded-pill px-3 py-2 {{ $cls }}">
                            {{ ucfirst($att->status) }}
                        </span>
                    </td>
                    <td class="text-muted small">{{ $att->notes ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="bi bi-calendar-x fs-3 d-block mb-2"></i>
                        Tidak ada data absensi bulan ini
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
