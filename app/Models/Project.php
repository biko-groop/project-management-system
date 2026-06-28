<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    // منشئ المشروع
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected $fillable = [
        'name',
        'description',
        'status',
        'priority',
        'progress',
        'start_date',
        'end_date',
        'created_by',
        'manager_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'progress' => 'integer',
    ];

    // مدير المشروع
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    // الأقسام المشاركة
    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'department_project')->withTimestamps();
    }

    // هل المشروع متأخر؟
    public function getIsDelayedAttribute(): bool
    {
        return $this->end_date
            && $this->end_date->isPast()
            && ! in_array($this->status, ['completed', 'cancelled']);
    }

    // العلاقة مع المستخدمين عبر ProjectMember
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    // العلاقة مع المهام
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    // العلاقة مع الفرق
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'project_team')
            ->withTimestamps();
    }

    // ملفات المشروع
    public function files(): HasMany
    {
        return $this->hasMany(ProjectFile::class);
    }

    // نسبة الإنجاز المحسوبة من المهام (مكتملة / الإجمالي)
    public function getCompletionRateAttribute(): int
    {
        $total = $this->tasks()->count();
        if ($total === 0) {
            return 0;
        }

        return (int) round($this->tasks()->where('status', 'completed')->count() / $total * 100);
    }
}