<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiScore extends Model
{
    protected $fillable = [
        'employee_id', 'year', 'month',
        'attendance_score', 'punctuality_score',
        'total_score', 'status', 'tap_out_allowed', 'calculated_at',
    ];

    protected $casts = [
        'tap_out_allowed' => 'boolean',
        'calculated_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
