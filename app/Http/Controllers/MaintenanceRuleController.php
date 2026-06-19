<?php

namespace App\Http\Controllers;

use App\Services\MaintenanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class MaintenanceRuleController extends Controller
{
    public function update(Request $request, int $rule): RedirectResponse
    {
        $user = $request->user();

        $ruleRow = $this->findUserRule($rule, (int) $user->id);

        if (!$ruleRow) {
            abort(404, __('rules.notFound'));
        }

        $templateKey = (string) $request->input('template', '');
        $templates = config('ridewrench.rule_templates', []);
        $template = $templates[$templateKey] ?? null;

        $name = trim((string) $request->input('name', ''));

        if ($name === '' && $template) {
            $name = __($template['name_key']);
        }

        if ($name === '') {
            return redirect('/bikes/' . $ruleRow->bike_id)->with('error', 'ruleNameRequired');
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

        DB::table('maintenance_rules')
            ->where('id', $ruleRow->id)
            ->where('user_id', $user->id)
            ->update([
                'name' => $name,
                'rule_kind' => $ruleKind,
                'distance_km' => $distanceKm !== '' ? (float) $distanceKm : null,
                'interval_days' => $intervalDays !== '' ? (int) $intervalDays : null,
                'email_enabled' => $request->boolean('email_enabled'),
                'updated_at' => now(),
            ]);

        $startDate = trim((string) $request->input('start_date', ''));

        if ($startDate !== '') {
            DB::table('maintenance_events')->insert([
                'user_id' => $user->id,
                'bike_id' => $ruleRow->bike_id,
                'rule_id' => $ruleRow->id,
                'performed_at' => $startDate . ' 00:00:00',
                'note' => __('maintenance.initialStartDate'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('maintenance_alerts')
                ->where('user_id', $user->id)
                ->where('rule_id', $ruleRow->id)
                ->where('status', 'open')
                ->delete();
        }

        return redirect('/bikes/' . $ruleRow->bike_id)->with('success', 'ruleUpdated');
    }

    public function reset(Request $request, int $rule): RedirectResponse
    {
        $user = $request->user();

        $ruleRow = $this->findUserRule($rule, (int) $user->id);

        if (!$ruleRow) {
            abort(404, __('rules.notFound'));
        }

        DB::table('maintenance_events')->where('rule_id', $ruleRow->id)->where('user_id', $user->id)->delete();

        DB::table('maintenance_alerts')->where('rule_id', $ruleRow->id)->where('user_id', $user->id)->delete();

        return redirect('/bikes/' . $ruleRow->bike_id)->with('success', 'ruleReset');
    }

    public function delete(Request $request, int $rule): RedirectResponse
    {
        $user = $request->user();

        $ruleRow = $this->findUserRule($rule, (int) $user->id);

        if (!$ruleRow) {
            abort(404, __('rules.notFound'));
        }

        DB::table('maintenance_alerts')->where('rule_id', $ruleRow->id)->where('user_id', $user->id)->delete();

        DB::table('maintenance_events')->where('rule_id', $ruleRow->id)->where('user_id', $user->id)->delete();

        DB::table('maintenance_rules')->where('id', $ruleRow->id)->where('user_id', $user->id)->delete();

        return redirect('/bikes/' . $ruleRow->bike_id)->with('success', 'ruleDeleted');
    }

    public function done(Request $request, int $rule, MaintenanceService $maintenanceService): RedirectResponse
    {
        $user = $request->user();

        $ruleRow = $this->findUserRule($rule, (int) $user->id);

        if (!$ruleRow) {
            abort(404, __('rules.notFound'));
        }

        $stats = $maintenanceService->statsForRule([
            'id' => $ruleRow->id,
            'user_id' => $ruleRow->user_id,
            'bike_id' => $ruleRow->bike_id,
            'name' => $ruleRow->name,
            'rule_kind' => $ruleRow->rule_kind,
            'distance_km' => $ruleRow->distance_km,
            'interval_days' => $ruleRow->interval_days,
        ]);

        DB::table('maintenance_events')->insert([
            'user_id' => $user->id,
            'bike_id' => $ruleRow->bike_id,
            'rule_id' => $ruleRow->id,
            'performed_at' => now(),
            'note' => trim((string) $request->input('note', '')),
            'distance_km' => $stats['distance_km'] ?? null,
            'elapsed_days' => $stats['elapsed_days'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('maintenance_alerts')
            ->where('rule_id', $ruleRow->id)
            ->where('status', 'open')
            ->update([
                'status' => 'completed',
                'updated_at' => now(),
            ]);

        return redirect('/dashboard')->with('success', 'maintenanceDone');
    }

    public function startDate(Request $request, int $rule, MaintenanceService $maintenanceService): RedirectResponse
    {
        $user = $request->user();

        $ruleRow = $this->findUserRule($rule, (int) $user->id);
        $startDate = trim((string) $request->input('start_date', ''));

        if ($ruleRow && $startDate !== '') {
            DB::table('maintenance_events')->where('rule_id', $ruleRow->id)->where('user_id', $user->id)->delete();

            DB::table('maintenance_alerts')->where('rule_id', $ruleRow->id)->where('user_id', $user->id)->delete();

            DB::table('maintenance_events')->insert([
                'user_id' => $user->id,
                'bike_id' => $ruleRow->bike_id,
                'rule_id' => $ruleRow->id,
                'performed_at' => $startDate . ' 00:00:00',
                'note' => __('maintenance.manualStartDate'),
                'distance_km' => null,
                'elapsed_days' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $maintenanceService->createDueAlerts((int) $user->id);

            return redirect('/bikes/' . $ruleRow->bike_id)->with('success', 'startDateSaved');
        }

        return redirect('/bikes/' . ($ruleRow->bike_id ?? ''))->with('error', 'ruleNameRequired');
    }

    public function email(Request $request, int $rule): RedirectResponse
    {
        $user = $request->user();

        $ruleRow = $this->findUserRule($rule, (int) $user->id);

        if (!$ruleRow) {
            abort(404, __('rules.notFound'));
        }

        $emailEnabled = $request->boolean('email_enabled');

        DB::table('maintenance_rules')
            ->where('id', $ruleRow->id)
            ->where('user_id', $user->id)
            ->update([
                'email_enabled' => $emailEnabled,
                'updated_at' => now(),
            ]);

        return redirect('/bikes/' . $ruleRow->bike_id)->with('success', $emailEnabled ? 'ruleEmailEnabled' : 'ruleEmailDisabled');
    }

    private function findUserRule(int $ruleId, int $userId): ?object
    {
        return DB::table('maintenance_rules as r')
            ->join('bikes as b', 'b.id', '=', 'r.bike_id')
            ->select(['r.*', 'b.id as bike_id'])
            ->where('r.id', $ruleId)
            ->where('r.user_id', $userId)
            ->where('b.user_id', $userId)
            ->first();
    }
}
