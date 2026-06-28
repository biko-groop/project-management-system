<?php

namespace App\Filament\Pages;

use App\Models\Project;
use App\Models\Task;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class KanbanBoard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-view-columns';

    protected static ?string $navigationGroup = 'إدارة المشاريع';

    protected static ?string $navigationLabel = 'لوحة المهام';

    protected static ?string $title = 'لوحة المهام (Kanban)';

    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.pages.kanban-board';

    public ?int $projectId = null;

    public const STATUSES = [
        'pending' => ['label' => 'قيد الانتظار', 'color' => '#f59e0b'],
        'in_progress' => ['label' => 'قيد التنفيذ', 'color' => '#3b82f6'],
        'completed' => ['label' => 'مكتمل', 'color' => '#22c55e'],
        'cancelled' => ['label' => 'ملغى', 'color' => '#ef4444'],
    ];

    public function getProjectsProperty(): array
    {
        return Project::orderBy('name')->pluck('name', 'id')->toArray();
    }

    /** الأعمدة مع مهامها */
    public function getBoard(): array
    {
        $tasks = Task::query()
            ->with(['assignedUser', 'project'])
            ->when($this->projectId, fn ($q) => $q->where('project_id', $this->projectId))
            ->orderByRaw("FIELD(priority,'urgent','high','medium','low')")
            ->get()
            ->groupBy('status');

        $board = [];
        foreach (self::STATUSES as $key => $meta) {
            $board[$key] = [
                'label' => $meta['label'],
                'color' => $meta['color'],
                'tasks' => $tasks->get($key, collect()),
            ];
        }

        return $board;
    }

    /** تحديث حالة مهمة (عند السحب أو من القائمة) */
    public function updateStatus(int $taskId, string $status): void
    {
        if (! array_key_exists($status, self::STATUSES)) {
            return;
        }

        $task = Task::find($taskId);
        if (! $task) {
            return;
        }

        $task->update(['status' => $status]);

        Notification::make()
            ->title('تم تحديث حالة المهمة')
            ->body($task->title . ' → ' . self::STATUSES[$status]['label'])
            ->success()
            ->send();
    }
}
