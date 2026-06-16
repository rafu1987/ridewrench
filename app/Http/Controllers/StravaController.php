<?php

namespace App\Http\Controllers;

use App\Services\MaintenanceService;
use App\Services\StravaClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class StravaController extends Controller
{
    public function connect(Request $request, StravaClient $client): RedirectResponse
    {
        $state = Str::random(40);

        $request->session()->put('strava_state', $state);

        $redirect = (string) $request->query('redirect', $request->headers->get('referer', '/dashboard'));
        $redirectPath = parse_url($redirect, PHP_URL_PATH) ?: '/dashboard';

        if (!str_starts_with($redirectPath, '/')) {
            $redirectPath = '/dashboard';
        }

        $request->session()->put('strava_redirect', $redirectPath);

        return redirect()->away($client->authUrl($state));
    }

    public function callback(Request $request, StravaClient $client, MaintenanceService $maintenanceService): RedirectResponse
    {
        $user = $request->user();

        $redirectPath = (string) $request->session()->pull('strava_redirect', '/dashboard');

        if (!str_starts_with($redirectPath, '/')) {
            $redirectPath = '/dashboard';
        }

        $expectedState = (string) $request->session()->pull('strava_state', '');

        if (!hash_equals($expectedState, (string) $request->query('state', ''))) {
            return redirect('/settings')->with('error', __('flash.invalidStravaState'));
        }

        if (!$request->query('code')) {
            return redirect('/settings')->with('error', __('flash.invalidStravaState'));
        }

        try {
            $token = $client->tokenFromCode((string) $request->query('code'));
            $athlete = $token['athlete'] ?? [];

            if (!is_array($athlete)) {
                $athlete = [];
            }

            $athleteId = (int) ($athlete['id'] ?? 0);
            $athleteName = trim((string) ($athlete['firstname'] ?? '') . ' ' . (string) ($athlete['lastname'] ?? ''));

            if ($athleteName === '') {
                $athleteName = 'Strava athlete ' . $athleteId;
            }

            DB::table('strava_accounts')->updateOrInsert(
                [
                    'user_id' => $user->id,
                ],
                [
                    'athlete_id' => $athleteId,
                    'athlete_name' => $athleteName,
                    'access_token' => (string) $token['access_token'],
                    'refresh_token' => (string) $token['refresh_token'],
                    'expires_at' => (int) $token['expires_at'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );

            $maintenanceService->syncUser((int) $user->id);

            return redirect($redirectPath)->with('success', __('flash.stravaConnectedSynced'));
        } catch (\Throwable $e) {
            $maintenanceService->markSyncError((int) $user->id, $e->getMessage());

            return redirect('/settings')->with('error', $e->getMessage());
        }
    }

    public function disconnect(Request $request, StravaClient $client): RedirectResponse
    {
        $user = $request->user();

        $redirect = (string) $request->input('redirect', '/settings');
        $redirectPath = parse_url($redirect, PHP_URL_PATH) ?: '/settings';

        if (!str_starts_with($redirectPath, '/')) {
            $redirectPath = '/settings';
        }

        $account = DB::table('strava_accounts')->where('user_id', $user->id)->first();

        if (!$account) {
            return redirect($redirectPath)->with('status', __('flash.stravaAlreadyDisconnected'));
        }

        try {
            $client->deauthorize((string) $account->access_token);
        } catch (\Throwable $e) {
            Log::warning('Strava deauthorize failed for user ' . $user->id . ': ' . $e->getMessage());
        }

        DB::table('strava_accounts')->where('user_id', $user->id)->delete();

        return redirect($redirectPath)->with('success', __('flash.stravaDisconnected'));
    }
}
