<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    protected static ?string $navigationGroup = 'إدارة المشاريع';

    protected static ?string $navigationLabel = 'المشاريع';

    protected static ?string $modelLabel = 'مشروع';

    protected static ?string $pluralModelLabel = 'المشاريع';

    protected static ?int $navigationSort = 1;

    protected const STATUSES = [
        'pending' => 'قيد الانتظار',
        'in_progress' => 'قيد التنفيذ',
        'completed' => 'مكتمل',
        'cancelled' => 'ملغى',
    ];

    protected const PRIORITIES = [
        'low' => 'منخفض',
        'medium' => 'متوسط',
        'high' => 'عالٍ',
        'urgent' => 'عاجل',
    ];

    // المشاريع: للأدمن والمدير
    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role, ['admin', 'manager']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label(__('Project Name'))
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),
            Forms\Components\Textarea::make('description')
                ->label(__('Description'))
                ->rows(3)
                ->columnSpanFull(),
            Forms\Components\Select::make('status')
                ->label(__('Status'))
                ->options(self::STATUSES)
                ->default('pending')
                ->native(false)
                ->required(),
            Forms\Components\Select::make('priority')
                ->label(__('Priority'))
                ->options([
                    'low' => __('Low'), 'medium' => __('Medium'),
                    'high' => __('High'), 'urgent' => __('Urgent'),
                ])
                ->default('medium')
                ->native(false)
                ->required(),
            Forms\Components\TextInput::make('progress')
                ->label(__('Progress') . ' %')
                ->numeric()->minValue(0)->maxValue(100)->default(0)->suffix('%'),
            Forms\Components\Select::make('manager_id')
                ->label(__('Project Manager'))
                ->relationship('manager', 'name')
                ->getOptionLabelFromRecordUsing(fn (\App\Models\User $r) => $r->select_label)
                ->searchable(['name', 'job_title'])->preload(),
            Forms\Components\Select::make('created_by')
                ->label(__('Created By'))
                ->relationship('creator', 'name')
                ->getOptionLabelFromRecordUsing(fn (\App\Models\User $r) => $r->select_label)
                ->default(fn () => auth()->id())
                ->searchable(['name', 'job_title'])->preload()->required(),
            Forms\Components\Select::make('departments')
                ->label(__('Departments'))
                ->relationship('departments', 'name')
                ->multiple()->searchable()->preload(),
            Forms\Components\DatePicker::make('start_date')->label(__('Start Date')),
            Forms\Components\DatePicker::make('end_date')->label(__('End Date')),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Project Name'))
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => self::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'completed' => 'success',
                        'in_progress' => 'info',
                        'cancelled' => 'danger',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('priority')
                    ->label(__('Priority'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => self::PRIORITIES[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'urgent' => 'danger', 'high' => 'warning', 'low' => 'gray', default => 'info',
                    }),
                Tables\Columns\TextColumn::make('progress')
                    ->label(__('Progress'))
                    ->formatStateUsing(fn ($state) => $state . '%')
                    ->badge()
                    ->color(fn ($state) => $state >= 100 ? 'success' : ($state >= 50 ? 'info' : 'gray')),
                Tables\Columns\IconColumn::make('is_delayed')
                    ->label(__('Delayed'))
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success'),
                Tables\Columns\TextColumn::make('tasks_count')
                    ->label(__('Tasks'))
                    ->counts('tasks')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label(__('Created By'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')->label(__('Start Date'))->date()->sortable(),
                Tables\Columns\TextColumn::make('end_date')->label(__('End Date'))->date()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options(self::STATUSES),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TasksRelationManager::class,
            RelationManagers\MembersRelationManager::class,
            RelationManagers\FilesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
