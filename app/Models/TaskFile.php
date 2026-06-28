<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskFile extends Model
{
    protected $fillable = [
        'task_id',
        'file_path',
        'file_name',
        'uploaded_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // العلاقة مع المهمة
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    // العلاقة مع المستخدم الذي رفع الملف
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}