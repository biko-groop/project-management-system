<?php

namespace App\Observers;

use App\Models\Task;
use App\Models\TaskActivity;
use App\Services\NotificationService;

class TaskObserver
{
    public function __construct(private NotificationService $notifications)
    {
    }

    public function created(Task $task): void
    {
        $this->log($task, 'created', 'تم إنشاء المهمة: ' . $task->title);

        if ($task->assigned_to) {
            $this->notifications->send(
                $task->assigned_to,
                'تكليف جديد',
                'تم إسناد مهمة "' . $task->title . '" إليك',
            );
        }
    }

    public function updated(Task $task): void
    {
        // تغيير الحالة
        if ($task->wasChanged('status')) {
            $this->log($task, 'status_changed', 'تغيّرت الحالة إلى: ' . $task->status);

            if ($task->status === 'completed') {
                $this->log($task, 'completed', 'تم إنهاء المهمة');
                $this->notifications->sendMany(
                    [$task->created_by, $task->assigned_to],
                    'اكتمال مهمة',
                    'تم إكمال المهمة "' . $task->title . '"',
                );
            } else {
                $this->notifications->sendMany(
                    [$task->created_by, $task->assigned_to],
                    'تغيير حالة',
                    'تغيّرت حالة المهمة "' . $task->title . '" إلى ' . $task->status,
                );
            }
        }

        // تغيير المسؤول
        if ($task->wasChanged('assigned_to') && $task->assigned_to) {
            $this->log($task, 'assigned', 'تم تغيير المسؤول');
            $this->notifications->send(
                $task->assigned_to,
                'تكليف جديد',
                'تم إسناد مهمة "' . $task->title . '" إليك',
            );
        }

        // تعديل عام
        if (! $task->wasChanged('status') && ! $task->wasChanged('assigned_to')) {
            $this->log($task, 'updated', 'تم تعديل المهمة');
        }
    }

    private function log(Task $task, string $event, string $description): void
    {
        TaskActivity::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'event' => $event,
            'description' => $description,
        ]);
    }
}
