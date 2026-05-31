@extends('layouts.hrd')
@section('title', 'Manajemen Task')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
  
    <div class="d-flex gap-2">
        <a href="{{ route('hrd.tasks.monitor') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-bar-chart me-1"></i> Monitor Hari Ini
        </a>
        <a href="{{ route('hrd.tasks.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Buat Task
        </a>
    </div>
</div>

{{-- Filter --}}
<div class="card form-card mb-4 p-4">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label">Bulan</label>
            <select name="month" class="form-select">
                @foreach(range(1,12) as $m)
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

<div class="card table-card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Judul Task</th>
                    <th>Jadwal</th>
                    <th>Target</th>
                    <th>Laporan Wajib</th>
                    <th>Carry-over</th>
                    <th>Progress</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($templates as $task)
                @php
                    $total = $task->assignments->count();
                    $done  = $task->assignments->filter(fn($a) =>
                        $a->completion && $a->completion->is_done)->count();
                    $pct   = $total > 0 ? round(($done/$total)*100) : 0;
                @endphp
                <tr>
                    <td>
                        <div class="fw-medium">{{ $task->title }}</div>
                        @if($task->description)
                        <small class="text-muted">
                            {{ Str::limit($task->description, 50) }}
                        </small>
                        @endif
                    </td>
                    <td>
                        <div class="fw-medium">
                            {{ $task->scheduled_date->format('d M Y') }}
                        </div>
                        <small class="text-muted">
                            {{ $task->scheduled_date->translatedFormat('l') }}
                        </small>
                    </td>
                    <td>
                        @php
                            $targetLabel = match($task->target_type) {
                                'all'      => 'Semua Karyawan',
                                'division' => 'Divisi: ' . $task->target_value,
                                'employee' => 'Per Orang',
                            };
                        @endphp
                        <span class="badge rounded-pill px-3 badge-blocked">
                            {{ $targetLabel }}
                        </span>
                    </td>
                    <td class="text-center">
                        @if($task->report_required)
                            <i class="bi bi-check-circle-fill text-success"></i>
                        @else
                            <i class="bi bi-dash-circle text-muted"></i>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($task->carry_over)
                            <i class="bi bi-arrow-repeat text-primary"></i>
                        @else
                            <i class="bi bi-x-circle text-muted"></i>
                        @endif
                    </td>
                    <td style="min-width:120px">
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height:6px;border-radius:3px">
                                <div class="progress-bar {{ $pct >= 70 ? 'bg-success' : 'bg-warning' }}"
                                     style="width:{{ $pct }}%"></div>
                            </div>
                            <small class="fw-medium" style="min-width:35px">
                                {{ $done }}/{{ $total }}
                            </small>
                        </div>
                    </td>
                    <td>
                        <span class="badge rounded-pill px-3
                            {{ $task->status === 'active' ? 'badge-present' : 'badge-blocked' }}">
                            {{ ucfirst($task->status) }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('hrd.tasks.show', $task) }}"
                               class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('hrd.tasks.edit', $task) }}"
                               class="btn btn-sm btn-outline-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('hrd.tasks.destroy', $task) }}"
                                  method="POST"
                                  onsubmit="return confirm('Hapus task ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="bi bi-list-task fs-3 d-block mb-2"></i>
                        Belum ada task untuk periode ini.
                        <a href="{{ route('hrd.tasks.create') }}">Buat sekarang</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $templates->links() }}</div>
</div>

@endsection
