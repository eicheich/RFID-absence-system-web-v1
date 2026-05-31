@extends('layouts.hrd')
@section('title', 'Detail Review Laporan — ' . ($completion->employee->name ?? '') . ' — ' . ($completion->completion_date?->format('d M Y') ?? ''))

@section('content')

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('hrd.task-reviews.index') }}" class="btn btn-square view">
        <i class="bi bi-arrow-left"></i>
    </a>
</div>

<div class="row g-4">

    {{-- Detail Laporan --}}
    <div class="col-md-8">
        <div class="card table-card p-4 mb-4">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h6 class="fw-semibold mb-1">
                        {{ $completion->assignment->template->title }}
                    </h6>
                    <div class="d-flex gap-2 flex-wrap">
                        @if($completion->assignment->is_carry_over)
                            <span class="badge rounded-pill"
                                  style="background:#fef9c3;color:#ca8a04;font-size:11px">
                                <i class="bi bi-arrow-repeat me-1"></i>Carry-over
                            </span>
                        @endif
                        @php
                            $badgeClass = match($completion->review_status) {
                                'approved' => 'badge-present',
                                'declined' => 'badge-absent',
                                default    => 'badge-late',
                            };
                        @endphp
                        <span class="badge rounded-pill px-3 {{ $badgeClass }}">
                            {{ ucfirst($completion->review_status) }}
                        </span>
                    </div>
                </div>
                <small class="text-muted">
                    Submit: {{ $completion->submitted_at?->format('H:i, d M Y') }}
                </small>
            </div>

            {{-- Deskripsi task --}}
            @if($completion->assignment->template->description)
            <div class="p-3 rounded mb-3"
                 style="background:rgba(255,255,255,0.02);border-left:3px solid #2563eb">
                <small class="text-muted fw-medium d-block mb-1">Deskripsi Task:</small>
                <p class="mb-0 small">{{ $completion->assignment->template->description }}</p>
            </div>
            @endif

            {{-- Laporan teks --}}
            <div class="mb-3">
                <label class="form-label fw-medium">Laporan Karyawan</label>
                @if($completion->report)
                    <div class="p-3 rounded border" style="background:rgba(255,255,255,0.02);min-height:80px;border-color:rgba(255,255,255,0.04)">
                        {{ $completion->report }}
                    </div>
                @else
                    <div class="p-3 rounded border text-muted fst-italic" style="background:rgba(255,255,255,0.02);border-color:rgba(255,255,255,0.04)">
                        Tidak ada laporan teks
                    </div>
                @endif
            </div>

            {{-- Lampiran --}}
            @if($completion->attachment_path || $completion->attachment_url)
            <div class="mb-3">
                <label class="form-label fw-medium">Lampiran</label>
                <div>
                    @if($completion->attachment_path)
                        <a href="{{ asset('storage/' . $completion->attachment_path) }}"
                           target="_blank"
                           class="btn btn-outline-secondary">
                            <i class="bi bi-file-earmark-arrow-down me-2"></i>
                            {{ $completion->attachment_name }}
                        </a>
                    @endif
                    @if($completion->attachment_url)
                        <a href="{{ $completion->attachment_url }}"
                           target="_blank"
                           class="btn btn-outline-primary">
                            <i class="bi bi-box-arrow-up-right me-2"></i>
                            Buka Link Lampiran
                        </a>
                    @endif
                </div>
            </div>
            @endif

            {{-- Catatan decline sebelumnya --}}
            @if($completion->review_note)
            <div class="alert alert-danger border-0 rounded-3">
                <small class="fw-semibold d-block mb-1">
                    <i class="bi bi-exclamation-triangle me-1"></i>Catatan Decline:
                </small>
                <small>{{ $completion->review_note }}</small>
                @if($completion->review)
                <div class="mt-1">
                    <small class="text-muted">
                        oleh {{ $completion->review->reviewer->name }}
                        pada {{ $completion->review->reviewed_at?->format('H:i, d M Y') }}
                    </small>
                </div>
                @endif
            </div>
            @endif
        </div>

        {{-- Panel Review (hanya tampil kalau belum di-review atau pending) --}}
        @if($completion->review_status === 'pending' || $completion->review_status === 'approved')
        <div class="card table-card p-4">
            <h6 class="fw-semibold mb-3">Tindakan Review</h6>

            {{-- Tombol Approve --}}
            <form action="{{ route('hrd.task-reviews.approve', $completion) }}"
                  method="POST" class="d-inline me-2"
                  onsubmit="return confirm('Setujui laporan ini?')">
                @csrf
                <button type="submit" class="btn btn-success px-4">
                    <i class="bi bi-check-circle me-1"></i> Setujui Laporan
                </button>
            </form>

            <button class="btn btn-danger px-4" type="button"
                    onclick="document.getElementById('decline-form').classList.toggle('d-none')">
                <i class="bi bi-x-circle me-1"></i> Decline & Minta Revisi
            </button>

            {{-- Form Decline --}}
            <div id="decline-form" class="d-none mt-3">
                <form action="{{ route('hrd.task-reviews.decline', $completion) }}"
                      method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-medium">
                            Catatan untuk Karyawan
                            <span class="text-danger">*</span>
                        </label>
                        <textarea name="note" rows="3"
                                  class="form-control @error('note') is-invalid @enderror"
                                  placeholder="Jelaskan apa yang perlu diperbaiki atau dilengkapi oleh karyawan...">{{ old('note') }}</textarea>
                        @error('note')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Task revisi akan otomatis ditambahkan ke hari kerja berikutnya
                        </small>
                    </div>
                    <button type="submit" class="btn btn-danger px-4">
                        <i class="bi bi-send me-1"></i> Kirim Decline & Buat Task Revisi
                    </button>
                    <button type="button" class="btn btn-outline-secondary px-4 ms-2"
                            onclick="document.getElementById('decline-form').classList.add('d-none')">
                        Batal
                    </button>
                </form>
            </div>
        </div>

        @elseif($completion->review_status === 'declined' && $completion->review?->revisionAssignment)
        <div class="alert alert-warning border-0 rounded-3 shadow-sm">
            <div class="fw-semibold mb-1">
                <i class="bi bi-arrow-repeat me-1"></i>Task Revisi Sudah Dibuat
            </div>
            <small>
                Task revisi dijadwalkan pada:
                <strong>
                    {{ $completion->review->revision_due_date->translatedFormat('l, d F Y') }}
                </strong>
            </small>
        </div>
        @endif

    </div>

    {{-- Info Karyawan --}}
    <div class="col-md-4">
        <div class="card table-card p-4 mb-3">
            <h6 class="fw-semibold mb-3">Info Karyawan</h6>
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary
                            d-flex align-items-center justify-content-center fw-semibold"
                     style="width:48px;height:48px;font-size:1.1rem;flex-shrink:0">
                    {{ strtoupper(substr($completion->employee->name, 0, 1)) }}
                </div>
                <div>
                    <div class="fw-semibold">{{ $completion->employee->name }}</div>
                    <small class="text-muted">{{ $completion->employee->position ?? '-' }}</small>
                </div>
            </div>
            <table class="table table-sm mb-0" style="font-size:0.875rem">
                <tr>
                    <td class="text-muted border-0 ps-0">Kode</td>
                    <td class="border-0 font-monospace">{{ $completion->employee->employee_code }}</td>
                </tr>
                <tr>
                    <td class="text-muted ps-0">Departemen</td>
                    <td>{{ $completion->employee->department ?? '-' }}</td>
                </tr>
            </table>
        </div>

        {{-- Info Task --}}
        <div class="card table-card p-4">
            <h6 class="fw-semibold mb-3">Info Task</h6>
            <table class="table table-sm mb-0" style="font-size:0.875rem">
                <tr>
                    <td class="text-muted border-0 ps-0">Jadwal</td>
                    <td class="border-0">
                        {{ $completion->assignment->scheduled_date->format('d M Y') }}
                    </td>
                </tr>
                <tr>
                    <td class="text-muted ps-0">Laporan Wajib</td>
                    <td>
                        @if($completion->assignment->isReportRequired())
                            <span class="text-danger">Ya</span>
                        @else
                            <span class="text-muted">Tidak</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="text-muted ps-0">Carry-over</td>
                    <td>{{ $completion->assignment->is_carry_over ? 'Ya' : 'Tidak' }}</td>
                </tr>
            </table>
        </div>
    </div>
</div>

@endsection
