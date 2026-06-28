<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    private function manages(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager']);
    }

    public function viewAny(User $user): bool
    {
        return $this->manages($user);
    }

    public function view(User $user, Project $project): bool
    {
        return $this->manages($user);
    }

    public function create(User $user): bool
    {
        return $this->manages($user);
    }

    public function update(User $user, Project $project): bool
    {
        return $this->manages($user);
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->role === 'admin' || $project->created_by === $user->id;
    }
}
