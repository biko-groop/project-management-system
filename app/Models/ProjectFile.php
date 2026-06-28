<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectFile extends Model
{
    protected $fillable = [
        'project_id',
        'file_path',
        'file_name',
        'uploaded_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // العلاقة مع المشروع
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    // العلاقة مع المستخدم الذي رفع الملف
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}