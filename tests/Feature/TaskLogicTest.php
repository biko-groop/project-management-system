<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TaskLogicTest extends TestCase
{
    use DatabaseTransactions; // يتراجع بعد الاختبار فلا يلوّث البيانات

    public function test_task_observer_logs_activity_and_notifies(): void
    {
        $admin = User::where('email', 'admin@qurtuba.test')->first();
        $this->actingAs($admin);

        $project = Project::first();
        $assignee = User::where('id', '!=', $admin->id)->first();
        $this->assertNotNull($project);
        $this->assertNotNull($assignee);

        // إنشاء مهمة -> سجل "created" + إشعار للمسؤول
        $task = Task::create([
            'title' => 'مهمة اختبار',
            'status' => 'pending',
            'priority' => 'medium',
            'project_id' => $project->id,
            'assigned_to' => $assignee->id,
            'created_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('task_activities', ['task_id' => $task->id, 'event' => 'created']);
        $this->assertDatabaseHas('notifications', ['user_id' => $assignee->id, 'title' => 'تكليف جديد']);

        // إكمال المهمة -> سجل "completed" + إشعار اكتمال
        $task->update(['status' => 'completed']);

        $this->assertDatabaseHas('task_activities', ['task_id' => $task->id, 'event' => 'completed']);
        $this->assertDatabaseHas('notifications', ['user_id' => $assignee->id, 'title' => 'اكتمال مهمة']);

        // سجل التدقيق يلتقط الإنشاء والتعديل
        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => \App\Models\Task::class,
            'auditable_id' => $task->id,
            'event' => 'created',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => \App\Models\Task::class,
            'auditable_id' => $task->id,
            'event' => 'updated',
        ]);

        fwrite(STDERR, "Observer + Audit Log: working correctly\n");
    }
}
