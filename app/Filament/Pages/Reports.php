<?php

namespace App\Filament\Pages;

use App\Services\ReportService;
use Filament\Pages\Page;

class Reports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'الإدارة والإعدادات';

    protected static ?string $navigationLabel = 'التقارير';

    protected static ?string $title = 'التقارير';

    protected static ?int $navigationSort = 15;

    protected static string $view = 'filament.pages.reports';

    public string $type = 'projects';

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role, ['admin', 'manager']);
    }

    public function getTypes(): array
    {
        return ReportService::TYPES;
    }

    public function getReport(): array
    {
        return app(ReportService::class)->build($this->type);
    }
}
