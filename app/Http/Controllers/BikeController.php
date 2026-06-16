<?php

namespace App\Http\Controllers;

use App\Services\MaintenanceService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

final class BikeController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $account = DB::table('strava_accounts')->where('user_id', $user->id)->first();

        $bikeRows = DB::table('bikes as b')
            ->leftJoin('activities as a', 'a.bike_id', '=', 'b.id')
            ->select([
                'b.id',
                'b.user_id',
                'b.strava_gear_id',
                'b.name',
                'b.type',
                'b.active',
                'b.created_at',
                'b.updated_at',
                DB::raw('COALESCE(SUM(a.distance_m), 0) / 1000 AS km'),
            ])
            ->where('b.user_id', $user->id)
            ->groupBy(['b.id', 'b.user_id', 'b.strava_gear_id', 'b.name', 'b.type', 'b.active', 'b.created_at', 'b.updated_at'])
            ->orderByDesc('b.active')
            ->orderBy('b.name')
            ->get();

        return view('bikes.index', [
            'account' => $account,
            'bikeRows' => $bikeRows,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'bike_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:190'],
            'type' => ['required', 'string', Rule::in(['road', 'gravel', 'mtb', 'indoor', 'other'])],
            'active' => ['nullable', 'string'],
        ]);

        $bike = DB::table('bikes')->where('id', (int) $validated['bike_id'])->where('user_id', $user->id)->first();

        if (!$bike) {
            abort(404, __('bikes.notFound'));
        }

        $active = $request->boolean('active');

        DB::table('bikes')
            ->where('id', $bike->id)
            ->where('user_id', $user->id)
            ->update([
                'name' => trim((string) $validated['name']),
                'type' => $validated['type'],
                'active' => $active,
                'updated_at' => now(),
            ]);

        if (!$active) {
            DB::table('maintenance_alerts')
                ->where('user_id', $user->id)
                ->where('bike_id', $bike->id)
                ->where('status', 'open')
                ->update([
                    'status' => 'completed',
                    'updated_at' => now(),
                ]);
        }

        return redirect('/bikes')->with('success', __('flash.bikeUpdated'));
    }

    public function show(Request $request, int $bike, MaintenanceService $maintenanceService): View
    {
        $user = $request->user();

        $bikeRow = DB::table('bikes')->where('id', $bike)->where('user_id', $user->id)->first();

        if (!$bikeRow) {
            abort(404, __('bikes.notFound'));
        }

        $ruleRows = DB::table('maintenance_rules')
            ->where('bike_id', $bikeRow->id)
            ->where('user_id', $user->id)
            ->where('active', true)
            ->orderBy('name')
            ->get();

        return view('bikes.rules', [
            'bike' => $bikeRow,
            'bikeId' => $bikeRow->id,
            'ruleRows' => $ruleRows,
            'ruleTemplates' => config('ridewrench.rule_templates', []),
            'service' => $maintenanceService,
        ]);
    }

    public function storeRule(Request $request, int $bike): RedirectResponse
    {
        $user = $request->user();

        $bikeRow = DB::table('bikes')->where('id', $bike)->where('user_id', $user->id)->first();

        if (!$bikeRow) {
            abort(404, __('bikes.notFound'));
        }

        $templateKey = (string) $request->input('template', '');
        $templates = config('ridewrench.rule_templates', []);
        $template = $templates[$templateKey] ?? null;

        $name = trim((string) $request->input('name', ''));

        if ($name === '' && $template) {
            $name = __($template['name_key']);
        }

        if ($name === '') {
            return redirect('/bikes/' . $bikeRow->id)->with('error', __('flash.ruleNameRequired'));
        }

        $ruleKind = (string) $request->input('rule_kind', $template['rule_kind'] ?? 'distance');

        if (!in_array($ruleKind, ['distance', 'time', 'combined'], true)) {
            $ruleKind = 'distance';
        }

        $distanceKm = trim((string) $request->input('distance_km', ''));
        $intervalDays = trim((string) $request->input('interval_days', ''));

        if ($distanceKm === '' && $template && $template['distance_km'] !== null) {
            $distanceKm = (string) $template['distance_km'];
        }

        if ($intervalDays === '' && $template && $template['interval_days'] !== null) {
            $intervalDays = (string) $template['interval_days'];
        }

        $ruleId = DB::table('maintenance_rules')->insertGetId([
            'user_id' => $user->id,
            'bike_id' => $bikeRow->id,
            'name' => $name,
            'rule_kind' => $ruleKind,
            'distance_km' => $distanceKm !== '' ? (float) $distanceKm : null,
            'interval_days' => $intervalDays !== '' ? (int) $intervalDays : null,
            'email_enabled' => $request->boolean('email_enabled'),
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $startDate = trim((string) $request->input('start_date', ''));

        if ($startDate !== '') {
            DB::table('maintenance_events')->insert([
                'user_id' => $user->id,
                'bike_id' => $bikeRow->id,
                'rule_id' => $ruleId,
                'performed_at' => $startDate . ' 00:00:00',
                'note' => __('maintenance.initialStartDate'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect('/bikes/' . $bikeRow->id)->with('success', __('flash.ruleAdded'));
    }
}
