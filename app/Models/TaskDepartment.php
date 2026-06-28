<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskDepartment extends Model
{
    protected $fillable = ['task_id', 'department_id', 'responsibility', 'note'];

    public const RESPONSIBILITIES = [
        'primary' => 'مسؤول رئيسي',
        'execution' => 'تنفيذ',
        'financial' => 'قرار/اعتماد مالي',
        'advisory' => 'استشاري',
        'approval' => 'موافقة',
        'support' => 'دعم',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
