<?php

namespace App\Filament\Resources\TaskResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ObstaclesRelationManager extends RelationManager
{
    protected static string $relationship = 'obstacles';

    protected static ?string $title = 'المعوقات';

    protected static ?string $icon = 'heroicon-o-exclamation-triangle';

    protected const STATUSES = [
        'open' => 'مفتوح',
        'in_progress' => 'قيد المعالجة',
        'resolved' => 'تم الحل',
        'closed' => 'مغلق',
    ];

    protected const IMPACTS = [
        'low' => 'منخفض',
        'medium' => 'متوسط',
        'high' => 'عالٍ',
    ];

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\DatePicker::make('occurred_on')->label(__('Date'))->default(now()),
            Forms\Components\TextInput::make('type')->label(__('Type'))->maxLength(255),
            Forms\Components\Textarea::make('description')->label(__('Description'))->required()->rows(2)->columnSpanFull(),
            Forms\Components\Select::make('impact')->label(__('Impact'))->options(self::IMPACTS)->default('medium')->native(false)->required(),
            Forms\Components\Select::make('assigned_to')->label(__('Assigned To'))->relationship('assignee', 'name')->getOptionLabelFromRecordUsing(fn (\App\Models\User $r) => $r->select_label)->searchable(['name', 'job_title'])->preload(),
            Forms\Components\Select::make('status')->label(__('Status'))->options(self::STATUSES)->default('open')->native(false)->required(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                Tables\Columns\TextColumn::make('description')->label(__('Description'))->wrap(),
                Tables\Columns\TextColumn::make('type')->label(__('Type'))->badge()->color('gray'),
                Tables\Columns\TextColumn::make('impact')->label(__('Impact'))->badge()
                    ->color(fn ($state) => match ($state) { 'high' => 'danger', 'low' => 'gray', default => 'warning' }),
                Tables\Columns\TextColumn::make('assignee.name')->label(__('Assigned To'))->default('—'),
                Tables\Columns\TextColumn::make('status')->label(__('Status'))->badge()
                    ->color(fn ($state) => match ($state) { 'resolved', 'closed' => 'success', 'in_progress' => 'info', default => 'danger' }),
                Tables\Columns\TextColumn::make('occurred_on')->label(__('Date'))->date(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label(__('Status'))->options(self::STATUSES),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label(__('Add Obstacle')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
