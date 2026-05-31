@extends('layouts.hrd')
@section('title', 'Review Laporan Task')

@section('content')

    {{-- Summary badges --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4">
            <div class="card stat-card p-3 text-center">
                <div class="text-muted small">Menunggu Review</div>
                <div class="fs-3 fw-semibold text-warning">{{ $counts['pending'] }}</div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card stat-card p-3 text-center">
                <div class="text-muted small">Disetujui</div>
                <div class="fs-3 fw-semibold text-success">{{ $counts['approved'] }}</div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card stat-card p-3 text-center">
                <div class="text-muted small">Di-decline</div>
                <div class="fs-3 fw-semibold text-danger">{{ $counts['declined'] }}</div>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="card form-card mb-4 p-4">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Tanggal Submit</label>
                <input type="date" name="date" value="{{ $date }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Status Review</label>
                <select name="status" class="form-select">
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Menunggu Review</option>
                    <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Disetujui</option>
                    <option value="declined" {{ $status === 'declined' ? 'selected' : '' }}>Di-decline</option>
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-1"></i> Filter
                </button>
            </div>
        </form>
    </div>

    {{-- Tabel --}}
    <div class="card table-card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Karyawan</th>
                        <th>Task</th>
                        <th>Laporan</th>
                        <th>Lampiran</th>
                        <th>Waktu Submit</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($completions as $comp)
                        <tr>
                            <td>
                                <div class="fw-medium">{{ $comp->employee->name }}</div>
                                <small class="text-muted">{{ $comp->employee->department ?? '-' }}</small>
                            </td>
                            <td>
                                <div class="fw-medium" style="max-width:200px">
                                    {{ $comp->assignment->template->title }}
                                </div>
                                @if ($comp->assignment->is_carry_over)
                                    <span class="badge rounded-pill"
                                        style="background:#fef9c3;color:#ca8a04;font-size:11px">
                                        Carry-over
                                    </span>
                                @endif
                            </td>
                            <td style="max-width:200px">
                                @if ($comp->report)
                                    <p class="text-muted small mb-0"
                                        style="overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical">
                                        {{ $comp->report }}
                                    </p>
                                @else
                                    <span class="text-muted fst-italic small">Tidak ada laporan teks</span>
                                @endif
                            </td>
                            <td>
                                @if ($comp->attachment_path)
                                    <a href="{{ asset('storage/' . $comp->attachment_path) }}" target="_blank"
                                        class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-file-earmark me-1"></i>
                                        {{ Str::limit($comp->attachment_name, 15) }}
                                    </a>
                                @elseif($comp->attachment_url)
                                    <a href="{{ $comp->attachment_url }}" target="_blank"
                                        class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-box-arrow-up-right me-1"></i>Buka Link
                                    </a>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="small">{{ $comp->submitted_at?->format('H:i') }}</div>
                                <small class="text-muted">{{ $comp->completion_date->format('d M Y') }}</small>
                            </td>
                            <td>
                                @php
                                    $badgeClass = match ($comp->review_status) {
                                        'approved' => 'badge-present',
                                        'declined' => 'badge-absent',
                                        default => 'badge-late',
                                    };
                                    $badgeLabel = match ($comp->review_status) {
                                        'approved' => 'Disetujui',
                                        'declined' => 'Declined',
                                        default => 'Pending',
                                    };
                                @endphp
                                <span class="badge rounded-pill px-3 py-2 {{ $badgeClass }}">
                                    {{ $badgeLabel }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('hrd.task-reviews.show', $comp) }}"
                                    class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye me-1"></i> Review
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                Tidak ada laporan untuk filter ini
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $completions->appends(request()->query())->links() }}
        </div>
    </div>

@endsection
