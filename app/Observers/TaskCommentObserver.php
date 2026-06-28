<?php

namespace App\Observers;

use App\Models\TaskActivity;
use App\Models\TaskComment;
use App\Services\NotificationService;

class TaskCommentObserver
{
    public function __construct(private NotificationService $notifications)
    {
    }

    public function created(TaskComment $comment): void
    {
        $task = $comment->task;

        if (! $task) {
            return;
        }

        // سجل في الـ Timeline
        TaskActivity::create([
            'task_id' => $task->id,
            'user_id' => $comment->user_id,
            'event' => 'commented',
            'description' => 'أضاف تعليقاً على المهمة',
        ]);

        // إشعار المعنيين عدا كاتب التعليق
        $this->notifications->sendMany(
            array_diff([$task->assigned_to, $task->created_by], [$comment->user_id]),
            'تعليق جديد',
            'تم إضافة تعليق على المهمة "' . $task->title . '"',
        );
    }
}
