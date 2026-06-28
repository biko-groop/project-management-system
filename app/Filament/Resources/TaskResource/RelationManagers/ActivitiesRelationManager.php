<?php

namespace App\Filament\Resources\TaskResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ActivitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'activities';

    protected static ?string $title = 'السجل الزمني';

    protected static ?string $icon = 'heroicon-o-clock';

    protected const EVENTS = [
        'created' => 'تم الإنشاء',
        'updated' => 'تم التعديل',
        'status_changed' => 'تغيّرت الحالة',
        'assigned' => 'تغيير المسؤول',
        'completed' => 'تم الإنهاء',
        'commented' => 'تعليق',
        'obstacle_added' => 'إضافة معوق',
    ];

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                Tables\Columns\TextColumn::make('event')
                    ->label(__('Event'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => self::EVENTS[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'completed' => 'success',
                        'status_changed', 'assigned' => 'info',
                        'created' => 'primary',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('description')->label(__('Description'))->wrap(),
                Tables\Columns\TextColumn::make('user.name')->label(__('User'))->default('—'),
                Tables\Columns\TextColumn::make('created_at')->label(__('Date'))->dateTime()->since(),
            ])
            ->paginated([10, 25])
            ->defaultSort('created_at', 'desc');
    }
}
