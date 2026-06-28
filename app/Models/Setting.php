<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'logo',
        'app_name',
        'primary_color',
        'sidebar_theme',
    ];

    /** الحصول على صف الإعدادات (مع إنشائه إن لم يوجد) */
    public static function current(): self
    {
        return static::first() ?? static::create([
            'app_name' => 'نظام إدارة المشاريع',
            'primary_color' => 'indigo',
            'sidebar_theme' => 'light',
        ]);
    }
}