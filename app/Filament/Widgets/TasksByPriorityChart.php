<?php

namespace App\Filament\Widgets;

use App\Models\Task;
use Filament\Widgets\ChartWidget;

class TasksByPriorityChart extends ChartWidget
{
    protected static ?string $heading = 'المهام حسب الأولوية';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $priorities = ['low', 'medium', 'high', 'urgent'];
        $counts = Task::query()
            ->selectRaw('priority, COUNT(*) as total')
            ->groupBy('priority')
            ->pluck('total', 'priority');

        return [
            'datasets' => [
                [
                    'label' => __('Tasks'),
                    'data' => array_map(fn ($p) => (int) ($counts[$p] ?? 0), $priorities),
                    'backgroundColor' => ['#9ca3af', '#3b82f6', '#f59e0b', '#ef4444'],
                ],
            ],
            'labels' => [__('Low'), __('Medium'), __('High'), __('Urgent')],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
