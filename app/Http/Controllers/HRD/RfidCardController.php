<?php

namespace App\Http\Controllers\HRD;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\RfidCard;
use Illuminate\Http\Request;

class RfidCardController extends Controller
{
    public function index()
    {
        $cards     = RfidCard::with('employee')->latest()->paginate(10);
        $employees = Employee::where('status', 'active')->get();

        return view('hrd.rfid-cards.index', compact('cards', 'employees'));
    }

    // Assign kartu ke karyawan
    public function assign(Request $request, $id)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
        ]);

        $card = RfidCard::findOrFail($id);

        // Nonaktifkan kartu lama karyawan ini kalau ada
        RfidCard::where('employee_id', $request->employee_id)
            ->where('id', '!=', $card->id)
            ->update(['status' => 'inactive']);

        $card->update([
            'employee_id'   => $request->employee_id,
            'status'        => 'active',
            'registered_at' => now(),
        ]);

        return redirect()->route('hrd.rfid-cards.index')
            ->with('success', 'Kartu berhasil di-assign ke karyawan.');
    }

    public function edit(RfidCard $rfidCard)
    {
        $employees = Employee::where('status', 'active')->get();
        return view('hrd.rfid-cards.edit', compact('rfidCard', 'employees'));
    }

    public function update(Request $request, RfidCard $rfidCard)
    {
        $request->validate([
            'status'      => 'required|in:active,inactive',
            'employee_id' => 'nullable|exists:employees,id',
        ]);

        if ($request->employee_id && $request->employee_id != $rfidCard->employee_id) {
            RfidCard::where('employee_id', $request->employee_id)
                ->where('id', '!=', $rfidCard->id)
                ->update(['status' => 'inactive']);
        }

        $rfidCard->update([
            'status'      => $request->status,
            'employee_id' => $request->employee_id,
        ]);

        return redirect()->route('hrd.rfid-cards.index')
            ->with('success', 'Kartu RFID berhasil diupdate.');
    }

    public function destroy(RfidCard $rfidCard)
    {
        $rfidCard->delete();
        return redirect()->route('hrd.rfid-cards.index')
            ->with('success', 'Kartu berhasil dihapus.');
    }
}
