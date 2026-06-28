<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TasksRelationManager extends RelationManager
{
    protected static string $relationship = 'tasks';

    protected static ?string $title = 'المهام';

    protected static ?string $icon = 'heroicon-o-check-circle';

    protected const STATUSES = [
        'pending' => 'قيد الانتظار', 'in_progress' => 'قيد التنفيذ',
        'completed' => 'مكتمل', 'cancelled' => 'ملغى',
    ];

    protected const PRIORITIES = [
        'low' => 'منخفض', 'medium' => 'متوسط', 'high' => 'عالٍ', 'urgent' => 'عاجل',
    ];

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->label('العنوان')->required()->maxLength(255)->columnSpanFull(),
            Forms\Components\Select::make('status')->label('الحالة')->options(self::STATUSES)->default('pending')->native(false)->required(),
            Forms\Components\Select::make('priority')->label('الأولوية')->options(self::PRIORITIES)->default('medium')->native(false)->required(),
            Forms\Components\Select::make('assigned_to')->label('المسؤول')->relationship('assignedUser', 'name')->searchable()->preload(),
            Forms\Components\DatePicker::make('due_date')->label('تاريخ النهاية'),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('العنوان')->weight('bold')->wrap(),
                Tables\Columns\TextColumn::make('status')->label('الحالة')->badge()
                    ->formatStateUsing(fn ($s) => self::STATUSES[$s] ?? $s)
                    ->color(fn ($s) => match ($s) { 'completed' => 'success', 'in_progress' => 'info', 'cancelled' => 'danger', default => 'warning' }),
                Tables\Columns\TextColumn::make('priority')->label('الأولوية')->badge()
                    ->formatStateUsing(fn ($s) => self::PRIORITIES[$s] ?? $s)
                    ->color(fn ($s) => match ($s) { 'urgent' => 'danger', 'high' => 'warning', 'low' => 'gray', default => 'info' }),
                Tables\Columns\TextColumn::make('assignedUser.name')->label('المسؤول')->default('—'),
                Tables\Columns\TextColumn::make('due_date')->label('تاريخ النهاية')->date()
                    ->color(fn ($record) => $record->is_delayed ? 'danger' : null),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('إضافة مهمة')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['created_by'] = auth()->id();
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
