<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'progress',
        'start_date',
        'due_date',
        'estimated_hours',
        'actual_hours',
        'project_id',
        'department_id',
        'assigned_to',
        'created_by',
        'delay_reason',
        'delay_needs_support',
        'delay_needs_approval',
        'delay_needs_budget',
        'delay_needs_decision',
        'obstacles',
        'potential_risks',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'progress' => 'integer',
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
        'delay_needs_support' => 'boolean',
        'delay_needs_approval' => 'boolean',
        'delay_needs_budget' => 'boolean',
        'delay_needs_decision' => 'boolean',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    // الأقسام المعنية بالمهمة (مع نوع مسؤولية كل قسم)
    public function departmentLinks(): HasMany
    {
        return $this->hasMany(TaskDepartment::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function obstacles(): HasMany
    {
        return $this->hasMany(TaskObstacle::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(TaskFile::class);
    }

    // المهام التي تعتمد عليها هذه المهمة (يجب إنهاؤها أولاً)
    public function dependencies(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'task_id', 'depends_on_task_id')
            ->withTimestamps();
    }

    // هل المهمة محجوبة؟ (لها اعتمادية غير منجزة)
    public function getIsBlockedAttribute(): bool
    {
        return $this->dependencies()
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->exists();
    }

    public function activities(): HasMany
    {
        return $this->hasMany(TaskActivity::class)->latest();
    }

    // هل المهمة متأخرة؟ (تجاوزت تاريخ النهاية ولم تُنجز)
    public function getIsDelayedAttribute(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && ! in_array($this->status, ['completed', 'cancelled']);
    }

    // عدد أيام التأخير
    public function getDaysDelayedAttribute(): int
    {
        if (! $this->is_delayed) {
            return 0;
        }

        return (int) $this->due_date->diffInDays(now());
    }
}
