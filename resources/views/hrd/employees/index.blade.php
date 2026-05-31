@extends('layouts.hrd')
@section('title', 'Data Karyawan')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="{{ route('hrd.employees.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Tambah Karyawan
        </a>
    </div>

    <div class="card table-card">
        <div class="table-responsive">
            <table class="table dash-table mb-0">
                <thead>
                    <tr>
                        <th>Karyawan</th>
                        <th>Departemen</th>
                        <th>Jabatan</th>
                        <th>No. HP</th>
                        <th>Kartu RFID</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $emp)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-semibold"
                                        style="width:36px;height:36px;font-size:0.85rem;flex-shrink:0">
                                        {{ strtoupper(substr($emp->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-medium">{{ $emp->name }}</div>
                                        <small class="text-muted font-monospace">{{ $emp->employee_code }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-muted">{{ $emp->department ?? '-' }}</td>
                            <td class="text-muted">{{ $emp->position ?? '-' }}</td>
                            <td class="text-muted">{{ $emp->phone ?? '-' }}</td>
                            <td>
                                @if ($emp->rfidCards->where('status', 'active')->count())
                                    <span class="badge rounded-pill px-3 badge-present">
                                        <i class="bi bi-check-circle me-1"></i>Terdaftar
                                    </span>
                                @else
                                    <span class="badge rounded-pill px-3 badge-blocked">Belum ada</span>
                                @endif
                            </td>
                            <td>
                                <span
                                    class="badge rounded-pill px-3 {{ $emp->status === 'active' ? 'badge-present' : 'badge-blocked' }}">
                                    {{ ucfirst($emp->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('hrd.employees.show', $emp) }}" class="btn btn-square view"
                                        title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('hrd.employees.edit', $emp) }}" class="btn btn-square edit"
                                        title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('hrd.employees.destroy', $emp) }}" method="POST"
                                        onsubmit="return confirm('Hapus karyawan {{ $emp->name }}?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-square delete" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-people fs-3 d-block mb-2"></i>
                                Belum ada data karyawan.
                                <a href="{{ route('hrd.employees.create') }}">Tambah sekarang</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">{{ $employees->links() }}</div>
    </div>

@endsection
