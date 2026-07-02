<?php

namespace App\Http\Controllers;

use App\Exports\ZendyLogsExport;
use App\Services\ZendyTrackingService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ZendyController extends Controller
{
    public function __construct(private ZendyTrackingService $tracking) {}

    public function home(Request $request)
    {
        $this->tracking->recordReturnIfApplicable($request, auth()->user());

        return view('zendy.home', [
            'activeClickId' => $request->session()->get(ZendyTrackingService::SESSION_CLICK_ID),
            'activeLaunchedAt' => $request->session()->get(ZendyTrackingService::SESSION_LAUNCHED_AT),
        ]);
    }

    public function launch(Request $request)
    {
        $user = auth()->user();

        $this->tracking->logAccess($request, 'zendy_launch', $user, [
            'destination' => config('zendy.redirect_url'),
        ]);

        return view('zendy.launch', [
            'redirectUrl' => config('zendy.redirect_url'),
            'clickId' => $request->session()->get(ZendyTrackingService::SESSION_CLICK_ID),
            'launchedAt' => $request->session()->get(ZendyTrackingService::SESSION_LAUNCHED_AT),
        ]);
    }

    public function go(Request $request)
    {
        $user = auth()->user();

        $this->tracking->logAccess($request, 'go_to_zendy', $user, [
            'destination' => config('zendy.redirect_url'),
        ]);

        return redirect()->away(config('zendy.redirect_url'));
    }

    public function index(Request $request)
    {
        $logs = $this->tracking->baseQuery($request)
            ->with('actor')
            ->latest()
            ->paginate(20);

        return view('zendy.index', compact('logs'));
    }

    public function exportLogs(Request $request)
    {
        $logs = $this->tracking->baseQuery($request)
            ->with('actor')
            ->latest()
            ->get();

        $filename = 'zendy-activity-logs-'.now()->format('Y-m-d').'.xlsx';

        return Excel::download(new ZendyLogsExport($logs), $filename);
    }

    public function activity(Request $request)
    {
        $logs = $this->tracking->baseQuery($request)
            ->where('actor_user_id', auth()->id())
            ->latest()
            ->paginate(15);

        return view('zendy.activity', compact('logs'));
    }

    public function exportActivity(Request $request)
    {
        $logs = $this->tracking->baseQuery($request)
            ->where('actor_user_id', auth()->id())
            ->latest()
            ->get();

        $filename = 'my-zendy-activity-'.now()->format('Y-m-d').'.xlsx';

        return Excel::download(new ZendyLogsExport($logs, simple: true), $filename);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'course'     => 'required|string|max:100',
            'campus'     => 'required|string|max:100',
            'email'      => 'required|email',
        ]);

        $this->tracking->logAccess($request, 'zendy_form_submission', null, $validated);

        return response()->json(['success' => true]);
    }

    public function sessionEnd(Request $request)
    {
        $validated = $request->validate([
            'click_id' => 'required|uuid',
            'duration_seconds' => 'required|integer|min:0|max:604800',
        ]);

        $this->tracking->recordTabClose(
            $request,
            auth()->user(),
            $validated['click_id'],
            (int) $validated['duration_seconds'],
        );

        return response()->noContent();
    }
}
