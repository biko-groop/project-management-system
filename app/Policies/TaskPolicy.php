<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager', 'user']);
    }

    public function view(User $user, Task $task): bool
    {
        if (in_array($user->role, ['admin', 'manager'])) {
            return true;
        }

        return $task->assigned_to === $user->id || $task->created_by === $user->id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager', 'user']);
    }

    public function update(User $user, Task $task): bool
    {
        if (in_array($user->role, ['admin', 'manager'])) {
            return true;
        }

        return $task->assigned_to === $user->id || $task->created_by === $user->id;
    }

    public function delete(User $user, Task $task): bool
    {
        return in_array($user->role, ['admin', 'manager']);
    }
}
