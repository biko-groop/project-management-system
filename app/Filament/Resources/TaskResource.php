<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Filament\Resources\TaskResource\RelationManagers;
use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables;
use Filament\Tables\Table;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';

    protected static ?string $navigationGroup = 'إدارة المشاريع';

    protected static ?string $navigationLabel = 'المهام';

    protected static ?string $modelLabel = 'مهمة';

    protected static ?string $pluralModelLabel = 'المهام';

    protected static ?int $navigationSort = 2;

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

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')
                ->label(__('Title'))
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),
            Forms\Components\Textarea::make('description')
                ->label(__('Description'))
                ->rows(3)
                ->columnSpanFull(),
            Forms\Components\Select::make('project_id')
                ->label(__('Project'))
                ->relationship('project', 'name')
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\Select::make('department_id')
                ->label(__('Department'))
                ->relationship('department', 'name')
                ->searchable()
                ->preload(),
            Forms\Components\Select::make('assigned_to')
                ->label(__('Assigned To'))
                ->relationship('assignedUser', 'name')
                ->searchable()
                ->preload(),
            Forms\Components\Select::make('status')
                ->label(__('Status'))
                ->options(self::STATUSES)
                ->default('pending')
                ->native(false)
                ->required()
                // منع البدء/الإنهاء إذا كانت هناك مهام تابعة غير منجزة
                ->rule(fn () => function (string $attribute, $value, \Closure $fail) {
                    if (! in_array($value, ['in_progress', 'completed'])) {
                        return;
                    }
                    $recordId = request()->route('record');
                    if ($recordId && ($task = \App\Models\Task::find($recordId)) && $task->is_blocked) {
                        $fail('لا يمكن بدء أو إنهاء هذه المهمة قبل إنجاز المهام التي تعتمد عليها.');
                    }
                }),
            Forms\Components\Select::make('priority')
                ->label(__('Priority'))
                ->options(self::PRIORITIES)
                ->default('medium')
                ->native(false)
                ->required(),
            Forms\Components\TextInput::make('progress')
                ->label(__('Progress') . ' %')
                ->numeric()->minValue(0)->maxValue(100)->default(0)->suffix('%'),
            Forms\Components\DatePicker::make('start_date')->label(__('Start Date')),
            Forms\Components\DatePicker::make('due_date')->label(__('Due Date')),
            Forms\Components\TextInput::make('estimated_hours')
                ->label(__('Estimated Hours'))->numeric()->minValue(0),
            Forms\Components\TextInput::make('actual_hours')
                ->label(__('Actual Hours'))->numeric()->minValue(0),
            Forms\Components\Select::make('created_by')
                ->label(__('Created By'))
                ->relationship('creator', 'name')
                ->default(fn () => auth()->id())
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\Section::make('الاعتماديات (المهام السابقة)')
                ->description('المهام التي يجب إنهاؤها قبل بدء هذه المهمة')
                ->schema([
                    Forms\Components\Select::make('dependencies')
                        ->hiddenLabel()
                        ->relationship('dependencies', 'title', fn (Builder $query) => $query->whereKeyNot(request()->route('record')))
                        ->multiple()
                        ->searchable()
                        ->preload(),
                ])
                ->collapsible(),
            Forms\Components\Section::make('الأقسام المعنية')
                ->description('للمهام المشتركة بين أكثر من قسم: أضف كل قسم وحدّد نوع مسؤوليته (تنفيذ، قرار مالي، استشاري، موافقة...)')
                ->schema([
                    Forms\Components\Repeater::make('departmentLinks')
                        ->relationship()
                        ->hiddenLabel()
                        ->schema([
                            Forms\Components\Select::make('department_id')
                                ->label('القسم')
                                ->relationship('department', 'name')
                                ->required()->searchable()->preload(),
                            Forms\Components\Select::make('responsibility')
                                ->label('نوع المسؤولية')
                                ->options(\App\Models\TaskDepartment::RESPONSIBILITIES)
                                ->default('execution')->required()->native(false),
                            Forms\Components\TextInput::make('note')
                                ->label('ملاحظة / تفاصيل')
                                ->columnSpanFull(),
                        ])
                        ->columns(2)
                        ->addActionLabel('+ إضافة قسم معني')
                        ->itemLabel(fn (array $state): ?string => \App\Models\TaskDepartment::RESPONSIBILITIES[$state['responsibility'] ?? ''] ?? null)
                        ->collapsible()
                        ->cloneable()
                        ->defaultItems(0),
                ])
                ->collapsible(),
            Forms\Components\Section::make(__('Obstacles & Risks'))
                ->description(__('Fill in if any, otherwise leave as "None"'))
                ->schema([
                    Forms\Components\Textarea::make('obstacles')
                        ->label(__('Obstacles'))
                        ->rows(2)->columnSpanFull()
                        ->default('لا توجد'),
                    Forms\Components\Textarea::make('potential_risks')
                        ->label(__('Potential Risks'))
                        ->rows(2)->columnSpanFull()
                        ->default('لا توجد'),
                ])
                ->columns(1)
                ->collapsible(),
            Forms\Components\Section::make(__('Delay Reason'))
                ->description(__('Required when the task is delayed'))
                ->schema([
                    Forms\Components\Textarea::make('delay_reason')
                        ->label(__('Delay Reason'))
                        ->rows(2)->columnSpanFull()
                        // إلزامي عند إكمال مهمة تجاوزت تاريخ النهاية
                        ->required(fn (Get $get): bool => $get('status') === 'completed'
                            && filled($get('due_date'))
                            && \Illuminate\Support\Carbon::parse($get('due_date'))->isPast())
                        ->helperText(__('Required when closing a delayed task')),
                    Forms\Components\Toggle::make('delay_needs_support')->label(__('Needs Support')),
                    Forms\Components\Toggle::make('delay_needs_approval')->label(__('Needs Approval')),
                    Forms\Components\Toggle::make('delay_needs_budget')->label(__('Needs Budget')),
                    Forms\Components\Toggle::make('delay_needs_decision')->label(__('Needs Decision')),
                ])
                ->columns(2)
                ->collapsible()
                ->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('Title'))
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('project.name')
                    ->label(__('Project'))
                    ->sortable(),
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
                        'urgent' => 'danger',
                        'high' => 'warning',
                        'low' => 'gray',
                        default => 'info',
                    }),
                Tables\Columns\TextColumn::make('assignedUser.name')
                    ->label(__('Assigned To'))
                    ->default('—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('progress')
                    ->label(__('Progress'))
                    ->formatStateUsing(fn ($state) => $state . '%')
                    ->badge()
                    ->color(fn ($state) => $state >= 100 ? 'success' : ($state >= 50 ? 'info' : 'gray')),
                Tables\Columns\TextColumn::make('due_date')
                    ->label(__('Due Date'))
                    ->date()
                    ->sortable()
                    ->description(fn ($record) => $record->is_delayed ? __('Delayed') . ' (' . $record->days_delayed . ' ' . __('days') . ')' : null)
                    ->color(fn ($record) => $record->is_delayed ? 'danger' : null),
                Tables\Columns\IconColumn::make('is_blocked')
                    ->label('محجوبة')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->trueColor('danger')
                    ->falseColor('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('departmentLinks.department.name')
                    ->label('الأقسام المعنية')
                    ->badge()
                    ->separator(',')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('obstacles')
                    ->label(__('Obstacles'))
                    ->formatStateUsing(fn ($state) => filled($state) ? $state : 'لا توجد')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('potential_risks')
                    ->label(__('Potential Risks'))
                    ->formatStateUsing(fn ($state) => filled($state) ? $state : 'لا توجد')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label(__('Status'))->options(self::STATUSES),
                Tables\Filters\SelectFilter::make('priority')->label(__('Priority'))->options(self::PRIORITIES),
                Tables\Filters\SelectFilter::make('project_id')
                    ->label(__('Project'))
                    ->relationship('project', 'name'),
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
            RelationManagers\ObstaclesRelationManager::class,
            RelationManagers\CommentsRelationManager::class,
            RelationManagers\FilesRelationManager::class,
            RelationManagers\ActivitiesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}
