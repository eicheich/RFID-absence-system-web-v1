@extends('layouts.hrd')
@section('title', 'Detail KPI')

@section('content')

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('hrd.kpi.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h5 class="mb-0 fw-semibold">Detail KPI — {{ $employee->name }}</h5>
        <small class="text-muted">{{ $employee->department ?? '-' }} · {{ $employee->position ?? '-' }}</small>
    </div>
</div>

<div class="row g-4">

    {{-- KPI Bulan Ini --}}
    <div class="col-md-4">
        <div class="card table-card p-4">
            <h6 class="fw-semibold mb-4">KPI Bulan Ini</h6>
            <div class="text-center mb-4">
                <div style="width:120px;height:120px;border-radius:50%;margin:0 auto 12px;
                            display:flex;flex-direction:column;align-items:center;
                            justify-content:center;
                            background:{{ $currentKpi->total_score >= 80 ? '#dcfce7' : '#fee2e2' }}">
                    <span style="font-size:2rem;font-weight:700;
                                 color:{{ $currentKpi->total_score >= 80 ? '#16a34a' : '#dc2626' }}">
                        {{ number_format($currentKpi->total_score, 0) }}
                    </span>
                    <small class="text-muted" style="font-size:0.75rem">/ 100</small>
                </div>
                <span class="badge rounded-pill px-3 py-2
                    {{ $currentKpi->status === 'valid' ? 'badge-present' : 'badge-absent' }}">
                    {{ $currentKpi->status === 'valid' ? 'KPI Valid ✓' : 'KPI Tidak Valid ✗' }}
                </span>
            </div>

            <div class="mb-2">
                <div class="d-flex justify-content-between small mb-1">
                    <span class="text-muted">Kehadiran (60%)</span>
                    <span class="fw-medium">{{ number_format($currentKpi->attendance_score, 1) }}%</span>
                </div>
                <div class="progress mb-3" style="height:8px;border-radius:4px">
                    <div class="progress-bar bg-success"
                         style="width:{{ $currentKpi->attendance_score }}%"></div>
                </div>
                <div class="d-flex justify-content-between small mb-1">
                    <span class="text-muted">Ketepatan Waktu (40%)</span>
                    <span class="fw-medium">{{ number_format($currentKpi->punctuality_score, 1) }}%</span>
                </div>
                <div class="progress" style="height:8px;border-radius:4px">
                    <div class="progress-bar bg-primary"
                         style="width:{{ $currentKpi->punctuality_score }}%"></div>
                </div>
            </div>

            <div class="mt-3 pt-3 border-top">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="small text-muted">Status Tap Out</span>
                    @if($currentKpi->tap_out_allowed)
                        <span class="badge badge-present rounded-pill px-3">
                            <i class="bi bi-unlock me-1"></i>Diizinkan
                        </span>
                    @else
                        <span class="badge badge-absent rounded-pill px-3">
                            <i class="bi bi-lock me-1"></i>Diblokir
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Riwayat KPI --}}
    <div class="col-md-8">
        <div class="card table-card">
            <div class="card-header bg-white d-flex align-items-center
                        justify-content-between py-3 px-4">
                <h6 class="mb-0 fw-semibold">Riwayat KPI {{ $year }}</h6>
                <form method="GET" class="d-flex gap-2">
                    <select name="year" class="form-select form-select-sm"
                            style="width:100px" onchange="this.form.submit()">
                        @foreach(range(date('Y')-1, date('Y')) as $y)
                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Bulan</th>
                            <th>Kehadiran</th>
                            <th>Ketepatan</th>
                            <th>Total Skor</th>
                            <th>Status</th>
                            <th>Tap Out</th>
                            <th>Dihitung</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kpiScores as $kpi)
                        <tr>
                            <td class="fw-medium">
                                {{ \Carbon\Carbon::create()->month($kpi->month)->translatedFormat('F') }}
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress"
                                         style="width:60px;height:6px;border-radius:3px">
                                        <div class="progress-bar bg-success"
                                             style="width:{{ $kpi->attendance_score }}%"></div>
                                    </div>
                                    <small>{{ number_format($kpi->attendance_score, 1) }}%</small>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress"
                                         style="width:60px;height:6px;border-radius:3px">
                                        <div class="progress-bar bg-primary"
                                             style="width:{{ $kpi->punctuality_score }}%"></div>
                                    </div>
                                    <small>{{ number_format($kpi->punctuality_score, 1) }}%</small>
                                </div>
                            </td>
                            <td>
                                <span class="fw-semibold
                                    {{ $kpi->total_score >= 80 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($kpi->total_score, 1) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge rounded-pill px-3
                                    {{ $kpi->status === 'valid' ? 'badge-present' : 'badge-absent' }}">
                                    {{ ucfirst($kpi->status) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($kpi->tap_out_allowed)
                                    <i class="bi bi-unlock-fill text-success"></i>
                                @else
                                    <i class="bi bi-lock-fill text-danger"></i>
                                @endif
                            </td>
                            <td class="text-muted small">
                                {{ $kpi->calculated_at?->format('d M, H:i') ?? '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-graph-up fs-3 d-block mb-2"></i>
                                Belum ada data KPI tahun {{ $year }}
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
