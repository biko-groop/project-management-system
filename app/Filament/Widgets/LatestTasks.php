<?php

namespace App\Filament\Widgets;

use App\Models\Task;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestTasks extends BaseWidget
{
    protected static ?string $heading = 'أحدث المهام';

    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Task::query()->latest()->limit(10))
            ->columns([
                Tables\Columns\TextColumn::make('title')->label(__('Title'))->weight('bold')->wrap(),
                Tables\Columns\TextColumn::make('project.name')->label(__('Project')),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => ['pending' => 'قيد الانتظار', 'in_progress' => 'قيد التنفيذ', 'completed' => 'مكتمل', 'cancelled' => 'ملغى'][$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'completed' => 'success',
                        'in_progress' => 'info',
                        'cancelled' => 'danger',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('priority')
                    ->label(__('Priority'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => ['low' => 'منخفض', 'medium' => 'متوسط', 'high' => 'عالٍ', 'urgent' => 'عاجل'][$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'urgent' => 'danger',
                        'high' => 'warning',
                        'low' => 'gray',
                        default => 'info',
                    }),
                Tables\Columns\TextColumn::make('assignedUser.name')->label(__('Assigned To'))->default('—'),
                Tables\Columns\TextColumn::make('due_date')->label(__('Due Date'))->date(),
            ])
            ->paginated(false);
    }
}
