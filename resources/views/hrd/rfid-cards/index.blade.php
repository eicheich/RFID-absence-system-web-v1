@extends('layouts.hrd')
@section('title', 'Kartu RFID')

@section('content')



<div class="card table-card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>UID Kartu</th>
                    <th>Karyawan</th>
                    <th>Terdaftar Sejak</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cards as $card)
                <tr>
                    <td><code class="text-dark">{{ $card->uid }}</code></td>
                    <td>
                        @if($card->employee)
                            <div class="fw-medium">{{ $card->employee->name }}</div>
                            <small class="text-muted">{{ $card->employee->department ?? '' }}</small>
                        @else
                            <span class="text-muted fst-italic">Belum di-assign</span>
                        @endif
                    </td>
                    <td class="text-muted small">
                        {{ $card->registered_at?->format('d M Y, H:i') ?? '-' }}
                    </td>
                    <td>
                        <span class="badge rounded-pill px-3 {{ $card->status === 'active' ? 'badge-active' : 'badge-inactive' }}">
                            {{ ucfirst($card->status) }}
                        </span>
                    </td>
                    <td>
                        @if(!$card->employee_id)
                        <form action="{{ route('hrd.rfid-cards.assign', $card->id) }}"
                              method="POST" class="d-flex gap-2 align-items-center">
                            @csrf
                            <select name="employee_id" class="form-select form-select-sm" style="width:180px" required>
                                <option value="">Pilih karyawan...</option>
                                @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="bi bi-link-45deg"></i> Assign
                            </button>
                        </form>
                        @else
                        <div class="d-flex gap-1">
                            <a href="{{ route('hrd.rfid-cards.edit', $card) }}"
                               class="btn btn-sm btn-outline-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('hrd.rfid-cards.destroy', $card) }}" method="POST"
                                  onsubmit="return confirm('Hapus kartu ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-5 text-muted">
                        <i class="bi bi-credit-card-2-front fs-3 d-block mb-2"></i>
                        Belum ada kartu terdaftar.<br>
                        <small>Tap kartu ke mesin RFID untuk mendaftarkan.</small>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $cards->links() }}</div>
</div>

@endsection
