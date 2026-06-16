<?php

namespace App\Http\Controllers;

use App\Services\MaintenanceService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class AlertController extends Controller
{
    public function index(Request $request, MaintenanceService $maintenanceService): View
    {
        $user = $request->user();

        $maintenanceService->createDueAlerts((int) $user->id);

        $alertRows = DB::table('maintenance_alerts as a')
            ->join('bikes as b', 'b.id', '=', 'a.bike_id')
            ->join('maintenance_rules as r', 'r.id', '=', 'a.rule_id')
            ->select(['a.*', 'b.name as bike_name', 'b.type as bike_type', 'r.name as rule_name'])
            ->where('a.user_id', $user->id)
            ->where('a.status', 'open')
            ->where('b.active', true)
            ->orderByDesc('a.created_at')
            ->limit(50)
            ->get();

        return view('alerts.index', [
            'alertRows' => $alertRows,
        ]);
    }
}
