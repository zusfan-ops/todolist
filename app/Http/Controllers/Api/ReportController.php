<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WeeklyReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    public function weekly(Request $request, WeeklyReportService $reports)
    {
        $weekParam = $request->query('week', now()->format('o-\WW'));

        $filename = $reports->generatePdf($request->user(), $weekParam);
        $data = $reports->build($request->user(), $weekParam);

        return response()->json([
            'data' => [
                ...$data,
                'pdf_url' => Storage::disk('public')->url($filename),
            ],
        ]);
    }
}
