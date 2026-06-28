<?php

namespace App\Filament\Widgets;

use App\Models\Task;
use Filament\Widgets\ChartWidget;

class TasksStatusChart extends ChartWidget
{
    protected static ?string $heading = 'المهام حسب الحالة';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $statuses = ['pending', 'in_progress', 'completed', 'cancelled'];
        $counts = Task::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'datasets' => [
                [
                    'label' => __('Tasks'),
                    'data' => array_map(fn ($s) => (int) ($counts[$s] ?? 0), $statuses),
                    'backgroundColor' => ['#f59e0b', '#3b82f6', '#22c55e', '#ef4444'],
                ],
            ],
            'labels' => [__('Pending'), __('In Progress'), __('Completed'), __('Cancelled')],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
