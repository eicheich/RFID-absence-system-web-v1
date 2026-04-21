<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskCompletion extends Model
{
    protected $fillable = [
        'task_assignment_id', 'employee_id',
        'completion_date', 'is_done', 'report', 'submitted_at',
    ];

    protected $casts = [
        'completion_date' => 'date',
        'is_done' => 'boolean',
        'submitted_at' => 'datetime',
    ];

    public function assignment()
    {
        return $this->belongsTo(TaskAssignment::class, 'task_assignment_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
