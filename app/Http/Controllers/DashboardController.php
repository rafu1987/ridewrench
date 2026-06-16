<?php

namespace App\Http\Controllers;

use App\Services\MaintenanceService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class DashboardController extends Controller
{
    public function index(Request $request, MaintenanceService $maintenanceService): View
    {
        $user = $request->user();

        $bikes = DB::table('bikes')->where('user_id', $user->id)->where('active', true)->orderBy('name')->get();

        $ruleRows = DB::table('maintenance_rules as r')
            ->join('bikes as b', 'b.id', '=', 'r.bike_id')
            ->select(['r.*', 'b.name as bike_name', 'b.type as bike_type'])
            ->where('r.user_id', $user->id)
            ->where('r.active', true)
            ->where('b.active', true)
            ->orderBy('b.name')
            ->orderBy('r.name')
            ->get();

        $account = DB::table('strava_accounts')->where('user_id', $user->id)->first();

        return view('dashboard', [
            'user' => $user,
            'bikes' => $bikes,
            'ruleRows' => $ruleRows,
            'account' => $account,
            'maintenanceService' => $maintenanceService,
        ]);
    }

    public function sync(Request $request, MaintenanceService $maintenanceService): RedirectResponse
    {
        $user = $request->user();

        try {
            $count = $maintenanceService->syncUser((int) $user->id);

            return redirect('/dashboard')->with(
                'success',
                $count > 0 ? __('flash.syncedActivities', [$count]) : __('flash.noNewActivities'),
            );
        } catch (\Throwable $e) {
            $maintenanceService->markSyncError((int) $user->id, $e->getMessage());

            return redirect('/dashboard')->with('error', $e->getMessage());
        }
    }

    public function fullSync(Request $request, MaintenanceService $maintenanceService): RedirectResponse
    {
        $user = $request->user();

        $redirect = (string) $request->input('redirect', '/dashboard');
        $redirectPath = parse_url($redirect, PHP_URL_PATH) ?: '/dashboard';

        if (!str_starts_with($redirectPath, '/')) {
            $redirectPath = '/dashboard';
        }

        try {
            $count = $maintenanceService->syncUser((int) $user->id, true);

            return redirect($redirectPath)->with(
                'success',
                $count > 0 ? __('flash.fullResyncSynced', [$count]) : __('flash.fullResyncNoNewActivities'),
            );
        } catch (\Throwable $e) {
            $maintenanceService->markSyncError((int) $user->id, $e->getMessage());

            return redirect($redirectPath)->with('error', $e->getMessage());
        }
    }
}
