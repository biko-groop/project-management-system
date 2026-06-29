<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'الإدارة والإعدادات';

    protected static ?string $navigationLabel = 'المستخدمون';

    protected static ?string $modelLabel = 'مستخدم';

    protected static ?string $pluralModelLabel = 'المستخدمون';

    protected static ?int $navigationSort = 10;

    // المستخدمون: للأدمن فقط
    public static function canAccess(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('Name'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label(__('Email'))
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->label(__('Phone'))
                    ->tel()
                    ->maxLength(255),
                Forms\Components\TextInput::make('job_title')
                    ->label(__('Job Title'))
                    ->maxLength(255),
                Forms\Components\Select::make('department_id')
                    ->label(__('Department'))
                    ->relationship('department', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('manager_id')
                    ->label(__('Direct Manager'))
                    ->relationship('manager', 'name')
                    ->getOptionLabelFromRecordUsing(fn (\App\Models\User $r) => $r->select_label)
                    ->searchable(['name', 'job_title'])
                    ->preload(),
                Forms\Components\FileUpload::make('avatar')
                    ->label(__('Avatar'))
                    ->image()
                    ->avatar()
                    ->disk('public')
                    ->directory('avatars')
                    ->visibility('public')
                    ->maxSize(2048),
                Forms\Components\TextInput::make('password')
                    ->label(__('Password'))
                    ->password()
                    ->revealable()
                    ->required(fn (string $context): bool => $context === 'create')
                    ->dehydrated(fn ($state): bool => filled($state))
                    ->dehydrateStateUsing(fn ($state): string => \Illuminate\Support\Facades\Hash::make($state))
                    ->helperText(__('Leave blank to keep current password'))
                    ->maxLength(255),
                Forms\Components\Select::make('role')
                    ->label(__('Role'))
                    ->options([
                        'admin' => 'مدير النظام',
                        'manager' => 'مدير',
                        'user' => 'مستخدم',
                    ])
                    ->required()
                    ->native(false)
                    ->default('user'),
                Forms\Components\Toggle::make('is_active')
                    ->label(__('Active'))
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->label(__('Avatar'))
                    ->circular()
                    ->disk('public')
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name)),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->description(fn ($record) => $record->job_title)
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('Email'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label(__('Phone'))
                    ->default('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('department.name')
                    ->label(__('Department'))
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('role')
                    ->label(__('Role'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => ['admin' => 'مدير النظام', 'manager' => 'مدير', 'user' => 'مستخدم'][$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'admin' => 'danger',
                        'manager' => 'warning',
                        default => 'info',
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
