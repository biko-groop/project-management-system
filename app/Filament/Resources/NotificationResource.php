<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationResource\Pages;
use App\Filament\Resources\NotificationResource\RelationManagers;
use App\Models\Notification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NotificationResource extends Resource
{
    protected static ?string $model = Notification::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell';

    protected static ?string $navigationGroup = 'الإدارة والإعدادات';

    protected static ?string $navigationLabel = 'الإشعارات';

    protected static ?string $modelLabel = 'إشعار';

    protected static ?string $pluralModelLabel = 'الإشعارات';

    protected static ?int $navigationSort = 11;

    // الإشعارات: للأدمن فقط
    public static function canAccess(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('المستخدم')
                    ->relationship('user', 'name')
                    ->getOptionLabelFromRecordUsing(fn (\App\Models\User $r) => $r->select_label)
                    ->searchable(['name', 'job_title'])
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('title')
                    ->label('العنوان')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('message')
                    ->label('الرسالة')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_read')
                    ->label('مقروء'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('User'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable(),
                Tables\Columns\TextColumn::make('message')
                    ->label('الرسالة')
                    ->limit(50)
                    ->wrap(),
                Tables\Columns\IconColumn::make('is_read')
                    ->label('مقروء')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotifications::route('/'),
            'create' => Pages\CreateNotification::route('/create'),
            'edit' => Pages\EditNotification::route('/{record}/edit'),
        ];
    }
}
