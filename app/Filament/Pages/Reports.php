<?php

namespace App\Filament\Pages;

use App\Services\ReportService;
use Filament\Pages\Page;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function exportCsv(): StreamedResponse
    {
        $report = $this->getReport();
        $filename = $this->type . '-' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($report) {
            $out = fopen('php://output', 'w');
            // BOM لدعم العربية في Excel
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $report['headers']);
            foreach ($report['rows'] as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
