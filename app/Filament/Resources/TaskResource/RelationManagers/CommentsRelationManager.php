<?php

namespace App\Filament\Resources\TaskResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    protected static ?string $title = 'التعليقات';

    protected static ?string $icon = 'heroicon-o-chat-bubble-left-right';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Hidden::make('user_id')->default(fn () => auth()->id()),
            Forms\Components\Textarea::make('body')
                ->label(__('Comment'))
                ->required()
                ->rows(3)
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('body')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label(__('User'))->weight('bold'),
                Tables\Columns\TextColumn::make('body')->label(__('Comment'))->wrap(),
                Tables\Columns\TextColumn::make('created_at')->label(__('Date'))->dateTime()->since(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('Add Comment'))
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = auth()->id();
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) => $record->user_id === auth()->id() || auth()->user()?->role === 'admin'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
