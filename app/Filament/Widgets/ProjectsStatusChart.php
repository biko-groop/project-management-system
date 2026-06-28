<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use Filament\Widgets\ChartWidget;

class ProjectsStatusChart extends ChartWidget
{
    protected static ?string $heading = 'المشاريع حسب الحالة';

    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $statuses = ['pending', 'in_progress', 'completed', 'cancelled'];
        $counts = Project::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'datasets' => [
                [
                    'label' => __('Projects'),
                    'data' => array_map(fn ($s) => (int) ($counts[$s] ?? 0), $statuses),
                    'backgroundColor' => ['#f59e0b', '#3b82f6', '#22c55e', '#ef4444'],
                ],
            ],
            'labels' => [__('Pending'), __('In Progress'), __('Completed'), __('Cancelled')],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
