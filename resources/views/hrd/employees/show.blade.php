@extends('layouts.hrd')
@section('title', 'Detail Karyawan')

@section('content')

    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('hrd.employees.index') }}" class="btn btn-square view">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div class="ms-auto d-flex gap-2">
            <a href="{{ route('hrd.employees.edit', $employee) }}" class="btn btn-square edit" title="Edit">
                <i class="bi bi-pencil"></i>
            </a>
        </div>
    </div>

    <div class="row g-4">

        {{-- Profil Card --}}
        <div class="col-md-4">
            <div class="card table-card overflow-hidden">
                <div class="p-4 text-center" style="background:linear-gradient(135deg,#1e293b,#1e3a5f)">
                    <div class="rounded-circle bg-primary d-flex align-items-center
                            justify-content-center fw-bold text-white mx-auto mb-3"
                        style="width:72px;height:72px;font-size:1.8rem">
                        {{ strtoupper(substr($employee->name, 0, 1)) }}
                    </div>
                    <h6 class="text-white fw-semibold mb-1">{{ $employee->name }}</h6>
                    <div class="text-white-50 small">{{ $employee->position ?? '-' }}</div>
                    <div class="mt-2">
                        <span
                            class="badge rounded-pill px-3 py-2
                        {{ $employee->status === 'active' ? 'badge-present' : 'badge-absent' }}">
                            {{ ucfirst($employee->status) }}
                        </span>
                    </div>
                </div>
                <div class="p-4">
                    <table class="table table-sm mb-0" style="font-size:0.875rem">
                        <tr>
                            <td class="text-muted border-0 ps-0">Kode</td>
                            <td class="border-0 fw-medium font-monospace">
                                {{ $employee->employee_code }}
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">Departemen</td>
                            <td class="fw-medium">{{ $employee->department ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">Email</td>
                            <td class="fw-medium">{{ $employee->user->email ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">No. HP</td>
                            <td class="fw-medium">{{ $employee->phone ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">Bergabung</td>
                            <td class="fw-medium">
                                {{ $employee->join_date?->format('d M Y') ?? '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">Alamat</td>
                            <td class="fw-medium">{{ $employee->address ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Kartu RFID --}}
            <div class="card table-card mt-3 p-4">
                <h6 class="fw-semibold mb-3">
                    <i class="bi bi-credit-card-2-front me-2"></i>Kartu RFID
                </h6>
                @forelse($employee->rfidCards as $card)
                    <div class="panel d-flex align-items-center justify-content-between p-3 mb-2">
                        <div>
                            <div class="small text-muted mb-1">UID</div>
                            <div class="fw-medium">
                                <code class="text-white-50">{{ $card->uid }}</code>
                            </div>
                            <div class="small text-muted mt-1">
                                Didaftar: {{ $card->registered_at?->format('d M Y') ?? '-' }}
                            </div>
                        </div>
                        <span
                            class="badge rounded-pill px-3 py-2
                    {{ $card->status === 'active' ? 'badge-present' : 'badge-blocked' }}">
                            {{ ucfirst($card->status) }}
                        </span>
                    </div>
                @empty
                    <div class="panel text-center py-3 text-muted small">
                        <i class="bi bi-credit-card-2-front d-block fs-4 mb-1"></i>
                        Belum ada kartu terdaftar
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Statistik & Riwayat --}}
        <div class="col-md-8">

            {{-- Stat bulan ini --}}
            @php
                $month = now()->month;
                $year = now()->year;
                $thisMonth = $employee->attendances
                    ->where('date', '>=', now()->startOfMonth())
                    ->where('date', '<=', now()->endOfMonth());
                $kpiNow = $employee->kpiScores->where('year', $year)->where('month', $month)->first();
            @endphp

            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="card stat-card p-3 text-center">
                        <div class="text-muted small">Hadir Bulan Ini</div>
                        <div class="fs-3 fw-semibold text-success">
                            {{ $thisMonth->whereIn('status', ['present', 'late'])->count() }}
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card stat-card p-3 text-center">
                        <div class="text-muted small">Terlambat</div>
                        <div class="fs-3 fw-semibold text-warning">
                            {{ $thisMonth->where('status', 'late')->count() }}
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card stat-card p-3 text-center">
                        <div class="text-muted small">Tidak Hadir</div>
                        <div class="fs-3 fw-semibold text-danger">
                            {{ $thisMonth->where('status', 'absent')->count() }}
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card stat-card p-3 text-center">
                        <div class="text-muted small">Skor KPI</div>
                        <div
                            class="fs-3 fw-semibold
                        {{ ($kpiNow?->total_score ?? 0) >= 80 ? 'text-success' : 'text-danger' }}">
                            {{ $kpiNow ? number_format($kpiNow->total_score, 1) : '-' }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- 10 Absensi Terakhir --}}
            <div class="card table-card mb-3">
                <div
                    class="card-header bg-white d-flex justify-content-between
                        align-items-center py-3 px-4">
                    <h6 class="mb-0 fw-semibold">Absensi Terakhir</h6>
                    <a href="{{ route('hrd.attendances.show', $employee) }}" class="btn btn-sm btn-outline-secondary">
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
                            @forelse($employee->attendances->sortByDesc('date')->take(10) as $att)
                                <tr>
                                    <td>
                                        <div class="fw-medium">{{ $att->date->format('d M Y') }}</div>
                                        <small class="text-muted">
                                            {{ $att->date->translatedFormat('l') }}
                                        </small>
                                    </td>
                                    <td>{{ $att->tap_in?->format('H:i') ?? '-' }}</td>
                                    <td>{{ $att->tap_out?->format('H:i') ?? '-' }}</td>
                                    <td class="text-muted">
                                        @if ($att->work_duration)
                                            {{ floor($att->work_duration / 60) }}j
                                            {{ $att->work_duration % 60 }}m
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
