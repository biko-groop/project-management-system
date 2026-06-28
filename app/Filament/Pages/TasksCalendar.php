<?php

namespace App\Filament\Pages;

use App\Filament\Resources\TaskResource;
use App\Models\Task;
use Filament\Pages\Page;

class TasksCalendar extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'إدارة المشاريع';

    protected static ?string $navigationLabel = 'التقويم';

    protected static ?string $title = 'تقويم المهام';

    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.pages.tasks-calendar';

    public function getEvents(): array
    {
        $colors = [
            'pending' => '#f59e0b',
            'in_progress' => '#3b82f6',
            'completed' => '#22c55e',
            'cancelled' => '#ef4444',
        ];

        return Task::query()
            ->whereNotNull('due_date')
            ->with('project')
            ->get()
            ->map(fn (Task $t) => [
                'title' => $t->title,
                'start' => $t->due_date->format('Y-m-d'),
                'color' => $colors[$t->status] ?? '#6366f1',
                'url' => TaskResource::getUrl('edit', ['record' => $t->id]),
            ])
            ->values()
            ->toArray();
    }
}
