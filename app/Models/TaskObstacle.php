<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskObstacle extends Model
{
    protected $fillable = [
        'task_id', 'occurred_on', 'type', 'description', 'impact', 'assigned_to', 'status',
    ];

    protected $casts = ['occurred_on' => 'date'];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
