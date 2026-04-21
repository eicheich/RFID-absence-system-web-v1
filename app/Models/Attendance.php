<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'employee_id', 'rfid_card_id', 'date',
        'tap_in', 'tap_out', 'status', 'work_duration', 'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'tap_in' => 'datetime',
        'tap_out' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function rfidCard()
    {
        return $this->belongsTo(RfidCard::class);
    }
}
