<?php

namespace App\Http\Controllers;

use App\Exports\ZendyReportsExport;
use App\Services\ZendyTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ZendyReportController extends Controller
{
    public function __construct(private ZendyTrackingService $tracking) {}

    public function index(Request $request)
    {
        return view('zendy.reports', $this->buildReportData($request));
    }

    public function export(Request $request)
    {
        $filename = 'zendy-reports-'.now()->format('Y-m-d').'.xlsx';

        return Excel::download(
            new ZendyReportsExport($this->buildReportData($request)),
            $filename,
        );
    }

    private function buildReportData(Request $request): array
    {
        $baseQuery = $this->tracking->baseQuery($request);

        $logs = (clone $baseQuery)->orderBy('created_at', 'desc')->paginate(15);

        $exportLogs = (clone $baseQuery)
            ->with('actor')
            ->orderBy('created_at', 'desc')
            ->get();

        $launchActions = ['go_to_zendy', 'zendy_launch', 'zendy_sso', 'zendy_form_submission'];

        $totalLaunches = (clone $baseQuery)->whereIn('action', $launchActions)->count();
        $uniqueUsers = (clone $baseQuery)->whereNotNull('actor_user_id')->distinct('actor_user_id')->count('actor_user_id');
        $estimatedReturns = (clone $baseQuery)->whereIn('action', ['zendy_return', 'zendy_tab_close'])->count();

        $avgDuration = (clone $baseQuery)
            ->whereIn('action', ['zendy_return', 'zendy_tab_close'])
            ->get()
            ->avg(fn ($log) => $log->metadata['estimated_duration_seconds'] ?? null);

        $submissionsByCourse = (clone $baseQuery)
            ->select('course', DB::raw('count(*) as total'))
            ->whereNotNull('course')
            ->groupBy('course')
            ->orderByDesc('total')
            ->get();

        $submissionsByCampus = (clone $baseQuery)
            ->select('campus', DB::raw('count(*) as total'))
            ->whereNotNull('campus')
            ->groupBy('campus')
            ->orderByDesc('total')
            ->get();

        $submissionsByAction = (clone $baseQuery)
            ->select('action', DB::raw('count(*) as total'))
            ->groupBy('action')
            ->orderByDesc('total')
            ->get();

        $submissionsOverTime = (clone $baseQuery)
            ->whereIn('action', $launchActions)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return compact(
            'logs',
            'exportLogs',
            'totalLaunches',
            'uniqueUsers',
            'estimatedReturns',
            'avgDuration',
            'submissionsByCourse',
            'submissionsByCampus',
            'submissionsByAction',
            'submissionsOverTime',
        );
    }
}
