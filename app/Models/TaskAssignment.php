<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskAssignment extends Model
{
    protected $fillable = [
        'task_template_id',
        'employee_id',
        'scheduled_date',
        'report_required',
        'carry_over',
        'is_carry_over',
        'original_assignment_id',
        'status',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'report_required' => 'boolean',
        'carry_over' => 'boolean',
        'is_carry_over' => 'boolean',
    ];

    public function template()
    {
        return $this->belongsTo(TaskTemplate::class, 'task_template_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function completion()
    {
        return $this->hasOne(TaskCompletion::class);
    }

    public function originalAssignment()
    {
        return $this->belongsTo(TaskAssignment::class, 'original_assignment_id');
    }

    // Cek apakah laporan wajib — override per karyawan atau ikut template
    public function isReportRequired(): bool
    {
        if (!is_null($this->report_required)) {
            return $this->report_required;
        }
        return $this->template->report_required;
    }

    // Cek apakah carry-over — override per karyawan atau ikut template
    public function isCarryOver(): bool
    {
        if (!is_null($this->carry_over)) {
            return $this->carry_over;
        }
        return $this->template->carry_over;
    }
}
