@extends('layouts.hrd')
@section('title', 'Data KPI')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-0 fw-semibold">Data KPI Karyawan</h5>
            <small class="text-muted">Skor KPI dihitung otomatis dari data absensi</small>
        </div>
    </div>

    {{-- Filter + Setting Threshold --}}
    <div class="row g-3 mb-4">

        {{-- Filter Bulan --}}
        <div class="col-md-8">
            <div class="card form-card p-4">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Bulan</label>
                        <select name="month" class="form-select">
                            @foreach (range(1, 12) as $m)
                                <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tahun</label>
                        <select name="year" class="form-select">
                            @foreach (range(date('Y') - 1, date('Y')) as $y)
                                <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-1"></i> Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Setting Threshold --}}
        <div class="col-md-4">
            <div class="card form-card p-4 h-100">
                <h6 class="fw-semibold mb-3">
                    <i class="bi bi-sliders me-1"></i> Threshold KPI
                </h6>
                <form action="{{ route('hrd.kpi.threshold.update') }}" method="POST">
                    @csrf
                    @foreach ($thresholds as $threshold)
                        <div class="mb-3">
                            <label class="form-label small">
                                {{ $threshold->name }}
                                <span class="text-muted">(min %)</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <input type="hidden" name="thresholds[{{ $loop->index }}][id]"
                                    value="{{ $threshold->id }}">
                                <input type="number" name="thresholds[{{ $loop->index }}][min_value]"
                                    value="{{ $threshold->min_value }}" min="0" max="100" step="1"
                                    class="form-control">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    @endforeach
                    <button type="submit" class="btn btn-sm btn-outline-primary w-100">
                        Simpan Threshold
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Tabel KPI --}}
    <div class="card table-card">
        <div class="card-header bg-white d-flex align-items-center justify-content-between py-3 px-4">
            <div>
                <h6 class="mb-0 fw-semibold">Skor KPI</h6>
                <small class="text-muted">
                    {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }}
                    {{ $year }} — diurutkan skor tertinggi
                </small>
            </div>
            <span class="badge bg-primary rounded-pill">{{ $kpiScores->total() }} karyawan</span>
            <a href="{{ route('hrd.export.kpi', ['month' => $month, 'year' => $year]) }}" class="btn btn-success btn-sm">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Karyawan</th>
                        <th>Kehadiran</th>
                        <th>Ketepatan Waktu</th>
                        <th>Total Skor</th>
                        <th>Status</th>
                        <th>Tap Out</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kpiScores as $i => $kpi)
                        <tr>
                            <td class="text-muted">{{ $kpiScores->firstItem() + $i }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary
                                        d-flex align-items-center justify-content-center fw-semibold"
                                        style="width:34px;height:34px;font-size:0.8rem;flex-shrink:0">
                                        {{ strtoupper(substr($kpi->employee->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-medium">{{ $kpi->employee->name }}</div>
                                        <small class="text-muted">{{ $kpi->employee->department ?? '-' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress" style="width:70px;height:6px;border-radius:3px">
                                        <div class="progress-bar bg-success" style="width:{{ $kpi->attendance_score }}%">
                                        </div>
                                    </div>
                                    <small class="fw-medium">{{ number_format($kpi->attendance_score, 1) }}%</small>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress" style="width:70px;height:6px;border-radius:3px">
                                        <div class="progress-bar bg-primary" style="width:{{ $kpi->punctuality_score }}%">
                                        </div>
                                    </div>
                                    <small class="fw-medium">{{ number_format($kpi->punctuality_score, 1) }}%</small>
                                </div>
                            </td>
                            <td>
                                <span
                                    class="fw-semibold fs-6
                            {{ $kpi->total_score >= 80 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($kpi->total_score, 1) }}
                                </span>
                                <small class="text-muted">/ 100</small>
                            </td>
                            <td>
                                <span
                                    class="badge rounded-pill px-3 py-2
                            {{ $kpi->status === 'valid' ? 'badge-present' : 'badge-absent' }}">
                                    {{ ucfirst($kpi->status) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if ($kpi->tap_out_allowed)
                                    <i class="bi bi-unlock-fill text-success fs-5" title="Tap out diizinkan"></i>
                                @else
                                    <i class="bi bi-lock-fill text-danger fs-5" title="Tap out diblokir"></i>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('hrd.kpi.show', $kpi->employee) }}"
                                    class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-graph-up fs-3 d-block mb-2"></i>
                                Belum ada data KPI untuk periode ini.<br>
                                <small>Data KPI otomatis terbuat saat karyawan melakukan tap-out.</small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $kpiScores->appends(['month' => $month, 'year' => $year])->links() }}
        </div>
    </div>

@endsection
