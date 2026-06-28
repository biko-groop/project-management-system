<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

/**
 * خدمة مركزية لإنشاء الإشعارات داخل النظام.
 */
class NotificationService
{
    /** إرسال إشعار لمستخدم واحد */
    public function send(?int $userId, string $title, string $message): void
    {
        if (! $userId) {
            return;
        }

        Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'is_read' => false,
        ]);
    }

    /** إرسال إشعار لعدة مستخدمين (مع تجاهل المكرّر والفارغ) */
    public function sendMany(array $userIds, string $title, string $message): void
    {
        foreach (array_unique(array_filter($userIds)) as $userId) {
            $this->send($userId, $title, $message);
        }
    }

    /** إشعار لكل المدراء والأدمن */
    public function notifyManagers(string $title, string $message): void
    {
        $ids = User::whereIn('role', ['admin', 'manager'])->pluck('id')->all();
        $this->sendMany($ids, $title, $message);
    }
}
