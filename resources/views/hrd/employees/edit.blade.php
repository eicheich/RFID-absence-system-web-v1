@extends('layouts.hrd')
@section('title', 'Edit Karyawan')

@section('content')

<div class="row justify-content-center">
<div class="col-lg-8">

    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('hrd.employees.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h5 class="mb-0 fw-semibold">Edit Karyawan</h5>
            <small class="text-muted">{{ $employee->name }} · {{ $employee->employee_code }}</small>
        </div>
    </div>

    <div class="card form-card">
        <div class="card-body p-4">
            <form action="{{ route('hrd.employees.update', $employee) }}" method="POST">
                @csrf @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="name"
                               value="{{ old('name', $employee->name) }}"
                               class="form-control @error('name') is-invalid @enderror"
                               required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Kode Karyawan <span class="text-danger">*</span></label>
                        <input type="text" name="employee_code"
                               value="{{ old('employee_code', $employee->employee_code) }}"
                               class="form-control font-monospace @error('employee_code') is-invalid @enderror"
                               required>
                        @error('employee_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Departemen</label>
                        <input type="text" name="department"
                               value="{{ old('department', $employee->department) }}"
                               class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Jabatan</label>
                        <input type="text" name="position"
                               value="{{ old('position', $employee->position) }}"
                               class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">No. HP</label>
                        <input type="text" name="phone"
                               value="{{ old('phone', $employee->phone) }}"
                               class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tanggal Masuk</label>
                        <input type="date" name="join_date"
                               value="{{ old('join_date', $employee->join_date?->format('Y-m-d')) }}"
                               class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active"
                                {{ $employee->status === 'active' ? 'selected' : '' }}>
                                Active
                            </option>
                            <option value="inactive"
                                {{ $employee->status === 'inactive' ? 'selected' : '' }}>
                                Inactive
                            </option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" rows="2"
                                  class="form-control">{{ old('address', $employee->address) }}</textarea>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check-lg me-1"></i> Update
                    </button>
                    <a href="{{ route('hrd.employees.show', $employee) }}"
                       class="btn btn-outline-secondary px-4">Batal</a>
                </div>
            </form>
        </div>
    </div>

</div>
</div>
@endsection
