<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HistoryController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $events = DB::table('maintenance_events')
            ->leftJoin('bikes', 'bikes.id', '=', 'maintenance_events.bike_id')
            ->leftJoin('maintenance_rules', 'maintenance_rules.id', '=', 'maintenance_events.rule_id')
            ->where('maintenance_events.user_id', $user->id)
            ->select([
                'maintenance_events.id',
                'maintenance_events.performed_at',
                'maintenance_events.note',
                'maintenance_events.distance_km',
                'maintenance_events.elapsed_days',
                'bikes.name as bike_name',
                'maintenance_rules.name as rule_name',
            ])
            ->orderByDesc('maintenance_events.performed_at')
            ->orderByDesc('maintenance_events.id')
            ->paginate(25);

        return view('history.index', [
            'user' => $user,
            'events' => $events,
        ]);
    }
}