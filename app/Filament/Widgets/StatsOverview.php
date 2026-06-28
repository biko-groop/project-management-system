<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use App\Models\Task;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = -2;

    protected function getStats(): array
    {
        $today = Carbon::today();

        $projectsTotal = Project::count();
        $projectsActive = Project::where('status', 'in_progress')->count();
        $projectsCompleted = Project::where('status', 'completed')->count();
        $projectsDelayed = Project::whereNotIn('status', ['completed', 'cancelled'])
            ->whereDate('end_date', '<', $today)->count();

        $tasksTotal = Task::count();
        $tasksInProgress = Task::where('status', 'in_progress')->count();
        $tasksCompleted = Task::where('status', 'completed')->count();
        $tasksDelayed = Task::whereNotIn('status', ['completed', 'cancelled'])
            ->whereDate('due_date', '<', $today)->count();

        $completionRate = $tasksTotal > 0 ? round($tasksCompleted / $tasksTotal * 100) : 0;
        $delayRate = $tasksTotal > 0 ? round($tasksDelayed / $tasksTotal * 100) : 0;

        return [
            Stat::make(__('Projects'), $projectsTotal)
                ->description(__('Active') . ": {$projectsActive} • " . __('Completed') . ": {$projectsCompleted}")
                ->descriptionIcon('heroicon-m-folder')
                ->color('primary'),

            Stat::make(__('Delayed Projects'), $projectsDelayed)
                ->description(__('Past due date'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($projectsDelayed > 0 ? 'danger' : 'success'),

            Stat::make(__('Tasks'), $tasksTotal)
                ->description(__('In Progress') . ": {$tasksInProgress} • " . __('Completed') . ": {$tasksCompleted}")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('info'),

            Stat::make(__('Delayed Tasks'), $tasksDelayed)
                ->description(__('Delay Rate') . ": {$delayRate}%")
                ->descriptionIcon('heroicon-m-clock')
                ->color($tasksDelayed > 0 ? 'danger' : 'success'),

            Stat::make(__('Completion Rate'), "{$completionRate}%")
                ->description(__('Completed tasks ratio'))
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('success'),
        ];
    }
}
