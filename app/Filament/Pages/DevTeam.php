<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class DevTeam extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-code-bracket-square';

    protected static ?string $navigationLabel = 'فريق التطوير';

    protected static ?string $title = 'فريق التطوير';

    protected static ?string $navigationGroup = 'الإدارة والإعدادات';

    protected static ?int $navigationSort = 99;

    protected static string $view = 'filament.pages.dev-team';

    // تظهر بعنوانها الخاص دون رأس صفحة افتراضي
    public function getHeading(): string
    {
        return '';
    }
}
