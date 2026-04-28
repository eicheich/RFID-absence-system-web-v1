<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskReview extends Model
{
    protected $fillable = [
        'task_completion_id',
        'task_assignment_id',
        'employee_id',
        'reviewed_by',
        'status',
        'note',
        'review_date',
        'reviewed_at',
        'revision_due_date',
        'revision_assignment_id',
    ];

    protected $casts = [
        'review_date'       => 'date',
        'revision_due_date' => 'date',
        'reviewed_at'       => 'datetime',
    ];

    public function completion()
    {
        return $this->belongsTo(TaskCompletion::class, 'task_completion_id');
    }

    public function assignment()
    {
        return $this->belongsTo(TaskAssignment::class, 'task_assignment_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function revisionAssignment()
    {
        return $this->belongsTo(TaskAssignment::class, 'revision_assignment_id');
    }
}
