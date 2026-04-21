<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskTemplate extends Model
{
    protected $fillable = [
        'created_by', 'title', 'description', 'target_type',
        'target_value', 'scheduled_date', 'report_required',
        'report_instruction', 'carry_over', 'status',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'report_required' => 'boolean',
        'carry_over' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignments()
    {
        return $this->hasMany(TaskAssignment::class);
    }

    // Resolve apakah report wajib (ikut template)
    public function isReportRequired(): bool
    {
        return $this->report_required;
    }
}
