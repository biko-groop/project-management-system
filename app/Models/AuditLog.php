<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'event', 'auditable_type', 'auditable_id', 'description', 'changes', 'created_at',
    ];

    protected $casts = [
        'changes' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getModelLabelAttribute(): string
    {
        return [
            \App\Models\Project::class => 'مشروع',
            \App\Models\Task::class => 'مهمة',
            \App\Models\User::class => 'مستخدم',
            \App\Models\Department::class => 'قسم',
        ][$this->auditable_type] ?? class_basename($this->auditable_type);
    }
}
