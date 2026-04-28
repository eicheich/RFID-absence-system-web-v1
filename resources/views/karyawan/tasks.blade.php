@extends('layouts.karyawan')
@section('title', 'Task Hari Ini')

@section('content')

    {{-- Header status --}}
    <div class="card border-0 rounded-3 shadow-sm mb-4 overflow-hidden">
        <div class="p-4 d-flex align-items-center gap-3" style="background:linear-gradient(135deg,#0f172a,#1e3a5f)">
            <div>
                <div class="mb-1">
                    @if ($tapOutCheck['allowed'])
                        <span class="badge rounded-pill px-3 py-2" style="background:#dcfce7;color:#16a34a">
                            <i class="bi bi-check-circle me-1"></i>Boleh Tap Out
                        </span>
                    @else
                        <span class="badge rounded-pill px-3 py-2" style="background:#fee2e2;color:#dc2626">
                            <i class="bi bi-lock me-1"></i>Belum Boleh Tap Out
                        </span>
                    @endif
                </div>
                <h5 class="text-white fw-semibold mb-1">
                    Task {{ $date->translatedFormat('l, d F Y') }}
                </h5>
                <p class="text-white-50 small mb-0">
                    Selesaikan minimal 70% task sebelum tap-out
                </p>
            </div>
            <div class="ms-auto text-end">
                <div class="text-white-50 small">Progress</div>
                <div class="text-white fw-bold fs-4">
                    {{ $tasks->done }}/{{ $tasks->total }}
                </div>
                <div class="text-white-50 small">
                    {{ number_format($tasks->completion_rate, 1) }}%
                </div>
            </div>
        </div>
        {{-- Progress bar --}}
        <div class="progress rounded-0" style="height:6px">
            <div class="progress-bar {{ $tasks->completion_rate >= 70 ? 'bg-success' : 'bg-warning' }}"
                style="width:{{ $tasks->completion_rate }}%"></div>
        </div>
    </div>

    @if (!$tapOutCheck['allowed'] && isset($tapOutCheck['reason']))
        <div
            class="alert border-0 rounded-3 shadow-sm mb-4
    {{ $tapOutCheck['reason'] === 'insufficient_tasks' ? 'alert-warning' : 'alert-danger' }}">
            <i class="bi bi-exclamation-triangle me-2"></i>
            @if ($tapOutCheck['reason'] === 'insufficient_tasks')
                Kamu baru menyelesaikan {{ number_format($tapOutCheck['rate'], 1) }}% task.
                Minimal 70% harus selesai untuk tap-out.
            @elseif($tapOutCheck['reason'] === 'missing_reports')
                Ada {{ $tapOutCheck['missing'] }} laporan task wajib yang belum diisi.
            @endif
        </div>
    @endif

    {{-- Form task --}}
    @if ($tasks->total > 0)
        <form action="{{ route('karyawan.tasks.submit') }}" method="POST">
            @csrf

            <div class="card table-card mb-4">
                <div class="card-header bg-white py-3 px-4 d-flex justify-content-between">
                    <h6 class="mb-0 fw-semibold">Daftar Task</h6>
                    <small class="text-muted">
                        Centang task yang sudah selesai, lalu klik Simpan
                    </small>
                </div>
                <div class="p-3">
                    @foreach ($tasks->assignments as $i => $assignment)
                        <input type="hidden" name="tasks[{{ $i }}][assignment_id]"
                            value="{{ $assignment->id }}">

                        <div class="card mb-3 border"
                            style="border-color: {{ $assignment->completion?->is_done ? 'var(--bs-success)' : 'var(--bs-border-color)' }} !important">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="form-check mt-1">
                                        <input class="form-check-input" type="checkbox"
                                            name="tasks[{{ $i }}][is_done]" value="1"
                                            id="task_{{ $assignment->id }}"
                                            onchange="toggleReport(this, {{ $i }})"
                                            {{ $assignment->completion?->is_done ? 'checked' : '' }}>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <label class="fw-medium mb-0" for="task_{{ $assignment->id }}">
                                                {{ $assignment->template->title }}
                                            </label>
                                            @if ($assignment->is_carry_over)
                                                <span class="badge rounded-pill"
                                                    style="background:#fef9c3;color:#ca8a04;font-size:11px">
                                                    <i class="bi bi-arrow-repeat me-1"></i>Carry-over
                                                </span>
                                            @endif
                                            @if ($assignment->isReportRequired())
                                                <span class="badge rounded-pill"
                                                    style="background:#fee2e2;color:#dc2626;font-size:11px">
                                                    Laporan Wajib
                                                </span>
                                            @endif
                                        </div>
                                        @if ($assignment->template->description)
                                            <p class="text-muted small mb-2">
                                                {{ $assignment->template->description }}
                                            </p>
                                        @endif

                                        {{-- Laporan --}}
                                        <div id="report_wrap_{{ $i }}"
                                            style="display: {{ $assignment->completion?->is_done || $assignment->isReportRequired() ? 'block' : 'none' }}">
                                            @if ($assignment->template->report_instruction)
                                                <p class="text-muted small mb-1">
                                                    <i class="bi bi-info-circle me-1"></i>
                                                    {{ $assignment->template->report_instruction }}
                                                </p>
                                            @endif
                                            <textarea name="tasks[{{ $i }}][report]" rows="2" class="form-control form-control-sm"
                                                placeholder="{{ $assignment->isReportRequired()
                                                    ? 'Wajib diisi — tuliskan laporan pengerjaan...'
                                                    : 'Opsional — tambahkan catatan jika perlu...' }}">{{ $assignment->completion?->report }}</textarea>
                                            {{-- Tambahkan setelah textarea laporan --}}
                                            <div class="mt-2">
                                                <label class="form-label small text-muted">
                                                    <i class="bi bi-paperclip me-1"></i>Lampiran (opsional)
                                                </label>
                                                <div class="row g-2">
                                                    <div class="col-md-6">
                                                        <input type="file"
                                                            name="tasks[{{ $i }}][attachment_file]"
                                                            class="form-control form-control-sm"
                                                            accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.zip">
                                                        <small class="text-muted">Upload file (max 10MB)</small>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <input type="url"
                                                            name="tasks[{{ $i }}][attachment_url]"
                                                            value="{{ $assignment->completion?->attachment_url }}"
                                                            class="form-control form-control-sm"
                                                            placeholder="Atau tempel link Google Drive...">
                                                        <small class="text-muted">Link Drive / URL</small>
                                                    </div>
                                                </div>
                                                {{-- Tampilkan lampiran yang sudah ada --}}
                                                @if ($assignment->completion?->attachment_path)
                                                    <div class="mt-1">
                                                        <a href="{{ asset('storage/' . $assignment->completion->attachment_path) }}"
                                                            target="_blank" class="btn btn-sm btn-outline-secondary">
                                                            <i class="bi bi-file-earmark me-1"></i>
                                                            {{ $assignment->completion->attachment_name }}
                                                        </a>
                                                    </div>
                                                @elseif($assignment->completion?->attachment_url)
                                                    <div class="mt-1">
                                                        <a href="{{ $assignment->completion->attachment_url }}"
                                                            target="_blank" class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-box-arrow-up-right me-1"></i>Buka Link Lampiran
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        @if ($assignment->completion?->submitted_at)
                                            <small class="text-muted mt-1 d-block">
                                                <i class="bi bi-clock me-1"></i>
                                                Disimpan: {{ $assignment->completion->submitted_at->format('H:i') }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="card-footer bg-white p-3">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-1"></i> Simpan Progress
                    </button>
                    <small class="text-muted ms-2">
                        Progress tersimpan dan bisa diupdate sampai tap-out
                    </small>
                </div>
            </div>
        </form>
    @else
        <div class="card table-card p-5 text-center text-muted">
            <i class="bi bi-list-check fs-2 d-block mb-2"></i>
            Tidak ada task untuk hari ini.
            <br>
            <small>Kamu bebas tap-out kapan saja.</small>
        </div>
    @endif

    <script>
        function toggleReport(checkbox, index) {
            const wrap = document.getElementById('report_wrap_' + index);
            if (wrap) {
                wrap.style.display = checkbox.checked ? 'block' : 'none';
            }
        }
    </script>

@endsection
