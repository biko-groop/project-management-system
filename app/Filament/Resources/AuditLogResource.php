<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditLogResource\Pages;
use App\Models\AuditLog;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'الإدارة والإعدادات';

    protected static ?string $navigationLabel = 'سجل التدقيق';

    protected static ?string $modelLabel = 'سجل';

    protected static ?string $pluralModelLabel = 'سجل التدقيق';

    protected static ?int $navigationSort = 16;

    protected const EVENTS = [
        'created' => 'إنشاء',
        'updated' => 'تعديل',
        'deleted' => 'حذف',
    ];

    public static function canAccess(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    // سجل للقراءة فقط
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('التاريخ')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('المستخدم')->default('—')->searchable(),
                Tables\Columns\TextColumn::make('event')
                    ->label('العملية')
                    ->badge()
                    ->formatStateUsing(fn ($state) => self::EVENTS[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'created' => 'success', 'deleted' => 'danger', default => 'info',
                    }),
                Tables\Columns\TextColumn::make('model_label')
                    ->label('النوع')->badge()->color('gray'),
                Tables\Columns\TextColumn::make('description')
                    ->label('الوصف')->searchable()->wrap(),
                Tables\Columns\TextColumn::make('changes')
                    ->label('التغييرات')
                    ->formatStateUsing(fn ($state) => $state ? collect($state)->map(fn ($v, $k) => "{$k}: {$v}")->implode(' | ') : '—')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event')->label('العملية')->options(self::EVENTS),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuditLogs::route('/'),
        ];
    }
}
