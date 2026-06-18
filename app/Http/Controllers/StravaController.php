<?php

namespace App\Http\Controllers;

use App\Services\MaintenanceService;
use App\Services\StravaClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

final class StravaController extends Controller
{
    public function connect(Request $request): RedirectResponse
    {
        $redirect = (string) $request->query('redirect', '/dashboard');

        if (!str_starts_with($redirect, '/')) {
            $redirect = '/dashboard';
        }

        $request->session()->put('strava_redirect_after_connect', $redirect);

        return Socialite::driver('strava')
            ->scopes(['read', 'activity:read_all'])
            ->redirect();
    }

    public function callback(Request $request, MaintenanceService $maintenanceService): RedirectResponse
    {
        $redirect = (string) $request->session()->pull('strava_redirect_after_connect', '/dashboard');

        if (!str_starts_with($redirect, '/')) {
            $redirect = '/dashboard';
        }

        if ($request->has('error')) {
            return redirect($redirect)->with('error', 'stravaConnectFailed');
        }

        try {
            $socialiteUser = Socialite::driver('strava')->user();
        } catch (\Throwable $e) {
            Log::error('Strava OAuth callback failed: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return redirect($redirect)->with('error', 'stravaConnectFailed');
        }

        $user = $request->user();

        if (!$user) {
            return redirect('/login')->with('error', 'auth.loginRequired');
        }

        $athlete = $socialiteUser->user ?? [];

        $athleteId = (int) ($socialiteUser->getId() ?: $athlete['id'] ?? 0);

        if ($athleteId <= 0) {
            return redirect($redirect)->with('error', 'stravaConnectFailed');
        }

        $athleteName = trim(
            (string) ($socialiteUser->getName() ?: ($athlete['firstname'] ?? '') . ' ' . ($athlete['lastname'] ?? '')),
        );

        DB::table('strava_accounts')->updateOrInsert(
            ['user_id' => $user->id],
            [
                'athlete_id' => $athleteId,
                'athlete_name' => $athleteName !== '' ? $athleteName : null,
                'access_token' => $socialiteUser->token,
                'refresh_token' => $socialiteUser->refreshToken,
                'expires_at' => (int) ($socialiteUser->expiresIn ? time() + $socialiteUser->expiresIn : time()),
                'last_sync_error' => null,
                'updated_at' => now(),
                'created_at' => now(),
            ],
        );

        try {
            $maintenanceService->syncUser((int) $user->id, true);
        } catch (\Throwable $e) {
            Log::error('Initial Strava sync after connect failed: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => $user->id,
            ]);

            return redirect($redirect)->with('success', 'stravaConnected');
        }

        return redirect($redirect)->with('success', 'stravaConnected');
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
            return redirect($redirectPath)->with('status', 'stravaAlreadyDisconnected');
        }

        try {
            $client->deauthorize((string) $account->access_token);
        } catch (\Throwable $e) {
            Log::warning('Strava deauthorize failed for user ' . $user->id . ': ' . $e->getMessage());
        }

        DB::table('strava_accounts')->where('user_id', $user->id)->delete();

        return redirect($redirectPath)->with('success', 'stravaDisconnected');
    }
}
