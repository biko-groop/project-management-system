<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    private function guard(): void
    {
        abort_unless(
            auth()->check() && in_array(auth()->user()->role, ['admin', 'manager']),
            403
        );
    }

    public function print(Request $request, ReportService $service)
    {
        $this->guard();
        $type = $request->query('type', 'projects');
        $report = $service->build($type);

        return view('reports.print', compact('report'));
    }

    public function export(Request $request, ReportService $service)
    {
        $this->guard();
        $type = $request->query('type', 'projects');
        $report = $service->build($type);

        $html = view('reports.excel', compact('report'))->render();
        $filename = $type . '-' . now()->format('Ymd_His') . '.xls';

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
