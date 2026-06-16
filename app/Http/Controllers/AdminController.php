<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

final class AdminController extends Controller
{
    public function index()
    {
        $stats = [
            'users' => DB::table('users')->count(),
            'strava_accounts' => DB::table('strava_accounts')->count(),
            'bikes' => DB::table('bikes')->count(),
            'active_bikes' => DB::table('bikes')->where('active', true)->count(),
            'activities' => DB::table('activities')->count(),
            'rules' => DB::table('maintenance_rules')->count(),
            'open_alerts' => DB::table('maintenance_alerts')->where('status', 'open')->count(),
        ];

        $cronRuns = DB::table('cron_runs')->orderByDesc('started_at')->limit(10)->get()->map(fn($row) => (array) $row)->all();

        $lastCronRun = $cronRuns[0] ?? null;

        $stravaAccounts = DB::table('strava_accounts')
            ->leftJoin('users', 'users.id', '=', 'strava_accounts.user_id')
            ->select([
                'strava_accounts.athlete_id',
                'strava_accounts.athlete_name',
                'strava_accounts.expires_at',
                'strava_accounts.last_synced_at',
                'strava_accounts.last_full_synced_at',
                'strava_accounts.last_sync_error',
                'users.name as user_name',
                'users.email',
            ])
            ->orderByDesc('strava_accounts.updated_at')
            ->limit(20)
            ->get()
            ->map(fn($row) => (array) $row)
            ->all();

        $webhookEvents = DB::table('strava_webhook_events')
            ->leftJoin('users', 'users.id', '=', 'strava_webhook_events.user_id')
            ->select([
                'strava_webhook_events.athlete_id',
                'strava_webhook_events.object_type',
                'strava_webhook_events.aspect_type',
                'strava_webhook_events.object_id',
                'strava_webhook_events.status',
                'strava_webhook_events.error',
                'strava_webhook_events.received_at',
                'users.name as user_name',
                'users.email',
            ])
            ->orderByDesc('strava_webhook_events.received_at')
            ->limit(20)
            ->get()
            ->map(fn($row) => (array) $row)
            ->all();

        $recentUsers = DB::table('users')
            ->select([
                'name',
                'email',
                'language',
                'created_at',
                DB::raw(
                    'CASE WHEN two_factor_secret IS NOT NULL AND two_factor_confirmed_at IS NOT NULL THEN 1 ELSE 0 END as two_factor_enabled',
                ),
            ])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(fn($row) => (array) $row)
            ->all();

        $recentAlerts = DB::table('maintenance_alerts')
            ->leftJoin('users', 'users.id', '=', 'maintenance_alerts.user_id')
            ->leftJoin('bikes', 'bikes.id', '=', 'maintenance_alerts.bike_id')
            ->leftJoin('maintenance_rules', 'maintenance_rules.id', '=', 'maintenance_alerts.rule_id')
            ->select([
                'maintenance_alerts.status',
                'maintenance_alerts.due_reason',
                'users.email',
                'bikes.name as bike_name',
                'maintenance_rules.name as rule_name',
            ])
            ->orderByDesc('maintenance_alerts.created_at')
            ->limit(10)
            ->get()
            ->map(fn($row) => (array) $row)
            ->all();

        return view('admin.index', [
            'stats' => $stats,
            'lastCronRun' => $lastCronRun,
            'cronRuns' => $cronRuns,
            'stravaAccounts' => $stravaAccounts,
            'webhookEvents' => $webhookEvents,
            'recentUsers' => $recentUsers,
            'recentAlerts' => $recentAlerts,
        ]);
    }
}
