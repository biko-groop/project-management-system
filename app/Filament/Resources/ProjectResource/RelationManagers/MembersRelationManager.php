<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class MembersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $title = 'الأعضاء';

    protected static ?string $icon = 'heroicon-o-user-group';

    protected const ROLES = [
        'member' => 'عضو',
        'manager' => 'مدير',
        'viewer' => 'مشاهد',
    ];

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('الاسم')->weight('bold')->searchable(),
                Tables\Columns\TextColumn::make('job_title')->label('المسمى الوظيفي')->default('—'),
                Tables\Columns\TextColumn::make('pivot.role')->label('الدور في المشروع')->badge()
                    ->formatStateUsing(fn ($s) => self::ROLES[$s] ?? $s),
                Tables\Columns\TextColumn::make('pivot.joined_at')->label('تاريخ الانضمام')->dateTime('Y-m-d'),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('إضافة عضو')
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['name', 'email'])
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->label('المستخدم')
                            ->getOptionLabelFromRecordUsing(fn (\App\Models\User $r) => $r->select_label),
                        Forms\Components\Select::make('role')->label('الدور')->options(self::ROLES)->default('member')->required()->native(false),
                    ])
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['joined_at'] = now();
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()->label('إزالة'),
            ]);
    }
}
