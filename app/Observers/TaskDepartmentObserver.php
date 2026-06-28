<?php

namespace App\Observers;

use App\Models\TaskDepartment;
use App\Services\NotificationService;

class TaskDepartmentObserver
{
    public function __construct(private NotificationService $notifications)
    {
    }

    public function created(TaskDepartment $link): void
    {
        $department = $link->department;
        $task = $link->task;

        if (! $department || ! $task || ! $department->manager_id) {
            return;
        }

        $role = TaskDepartment::RESPONSIBILITIES[$link->responsibility] ?? $link->responsibility;

        $this->notifications->send(
            $department->manager_id,
            'مهمة تخص قسمك',
            'تمت إضافة قسم "' . $department->name . '" إلى المهمة "' . $task->title . '" بمسؤولية: ' . $role,
        );
    }
}
