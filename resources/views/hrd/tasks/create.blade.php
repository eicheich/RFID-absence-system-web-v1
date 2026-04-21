@extends('layouts.hrd')
@section('title', 'Buat Task')

@section('content')

<div class="row justify-content-center">
<div class="col-lg-8">

    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('hrd.tasks.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h5 class="mb-0 fw-semibold">Buat Task Baru</h5>
            <small class="text-muted">Task akan otomatis terdistribusi ke karyawan yang ditarget</small>
        </div>
    </div>

    <div class="card form-card p-4">
        <form action="{{ route('hrd.tasks.store') }}" method="POST">
            @csrf

            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Judul Task <span class="text-danger">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}"
                           class="form-control @error('title') is-invalid @enderror"
                           placeholder="Contoh: Review laporan mingguan" required>
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label">Deskripsi Task</label>
                    <textarea name="description" rows="3"
                              class="form-control"
                              placeholder="Jelaskan detail task yang harus dikerjakan...">{{ old('description') }}</textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Jadwal Pengerjaan <span class="text-danger">*</span></label>
                    <input type="date" name="scheduled_date"
                           value="{{ old('scheduled_date') }}"
                           class="form-control @error('scheduled_date') is-invalid @enderror"
                           required>
                    @error('scheduled_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Target Karyawan <span class="text-danger">*</span></label>
                    <select name="target_type" id="target_type"
                            class="form-select" onchange="toggleTarget(this.value)" required>
                        <option value="">Pilih target...</option>
                        <option value="all"      {{ old('target_type') === 'all'      ? 'selected' : '' }}>
                            Semua Karyawan
                        </option>
                        <option value="division" {{ old('target_type') === 'division' ? 'selected' : '' }}>
                            Per Divisi
                        </option>
                        <option value="employee" {{ old('target_type') === 'employee' ? 'selected' : '' }}>
                            Per Karyawan
                        </option>
                    </select>
                </div>

                {{-- Target value (muncul kalau division/employee) --}}
                <div class="col-12" id="target_division" style="display:none">
                    <label class="form-label">Pilih Divisi</label>
                    <select name="target_value" class="form-select">
                        <option value="">Pilih divisi...</option>
                        @foreach($departments as $dept)
                        <option value="{{ $dept }}"
                            {{ old('target_value') === $dept ? 'selected' : '' }}>
                            {{ $dept }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12" id="target_employee" style="display:none">
                    <label class="form-label">Pilih Karyawan</label>
                    <select name="target_value" class="form-select">
                        <option value="">Pilih karyawan...</option>
                        @foreach($employees as $emp)
                        <option value="{{ $emp->id }}"
                            {{ old('target_value') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->name }} ({{ $emp->employee_code }})
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Opsi laporan --}}
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="report_required"
                               id="report_required" value="1"
                               onchange="toggleReportInstruction(this.checked)"
                               {{ old('report_required') ? 'checked' : '' }}>
                        <label class="form-check-label" for="report_required">
                            Wajibkan laporan deskriptif
                        </label>
                    </div>
                    <small class="text-muted">
                        Karyawan harus mengisi laporan teks sebelum mencentang task selesai
                    </small>
                </div>

                <div class="col-12" id="report_instruction_wrap" style="display:none">
                    <label class="form-label">Panduan Isi Laporan</label>
                    <textarea name="report_instruction" rows="2"
                              class="form-control"
                              placeholder="Contoh: Jelaskan progress yang dicapai dan kendala yang dihadapi">{{ old('report_instruction') }}</textarea>
                </div>

                {{-- Carry-over --}}
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="carry_over"
                               id="carry_over" value="1"
                               {{ old('carry_over', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="carry_over">
                            Aktifkan carry-over (sisa task dilanjut ke hari berikutnya)
                        </label>
                    </div>
                </div>
            </div>

            <div class="alert alert-info border-0 rounded-3 mt-4 py-2 px-3">
                <small>
                    <i class="bi bi-info-circle me-1"></i>
                    Task akan otomatis terdistribusi ke karyawan setelah disimpan.
                    Karyawan wajib menyelesaikan minimal <strong>70%</strong> task
                    di hari yang dijadwalkan sebelum bisa tap-out.
                </small>
            </div>

            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-check-lg me-1"></i> Buat & Distribusikan
                </button>
                <a href="{{ route('hrd.tasks.index') }}"
                   class="btn btn-outline-secondary px-4">Batal</a>
            </div>
        </form>
    </div>

</div>
</div>

<script>
function toggleTarget(val) {
    document.getElementById('target_division').style.display =
        val === 'division' ? 'block' : 'none';
    document.getElementById('target_employee').style.display =
        val === 'employee' ? 'block' : 'none';
}
function toggleReportInstruction(checked) {
    document.getElementById('report_instruction_wrap').style.display =
        checked ? 'block' : 'none';
}
// Init on load
toggleTarget('{{ old('target_type', '') }}');
toggleReportInstruction({{ old('report_required') ? 'true' : 'false' }});
</script>

@endsection