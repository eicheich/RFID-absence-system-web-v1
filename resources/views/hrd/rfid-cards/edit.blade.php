@extends('layouts.hrd')
@section('title', 'Edit Kartu RFID')

@section('content')

<div class="row justify-content-center">
<div class="col-lg-6">

    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('hrd.rfid-cards.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h5 class="mb-0 fw-semibold">Edit Kartu RFID</h5>
            <small class="text-muted font-monospace">{{ $rfidCard->uid }}</small>
        </div>
    </div>

    <div class="card form-card p-4">
        <form action="{{ route('hrd.rfid-cards.update', $rfidCard) }}" method="POST">
            @csrf @method('PUT')

            <div class="mb-3">
                <label class="form-label">UID Kartu</label>
                <input type="text" class="form-control font-monospace bg-light"
                       value="{{ $rfidCard->uid }}" disabled>
            </div>

            <div class="mb-3">
                <label class="form-label">Karyawan Terhubung</label>
                <select name="employee_id" class="form-select">
                    <option value="">-- Tidak di-assign --</option>
                    @foreach($employees as $emp)
                    <option value="{{ $emp->id }}"
                        {{ $rfidCard->employee_id == $emp->id ? 'selected' : '' }}>
                        {{ $emp->name }} ({{ $emp->employee_code }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label">Status Kartu</label>
                <select name="status" class="form-select">
                    <option value="active"
                        {{ $rfidCard->status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive"
                        {{ $rfidCard->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-check-lg me-1"></i> Simpan
                </button>
                <a href="{{ route('hrd.rfid-cards.index') }}"
                   class="btn btn-outline-secondary px-4">Batal</a>
            </div>
        </form>
    </div>

</div>
</div>
@endsection
