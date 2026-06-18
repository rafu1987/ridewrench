<?php

namespace App\Http\Controllers;

use App\Services\MaintenanceService;
use App\Services\StravaClient;
use App\Support\RideWrench;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $account = DB::table('strava_accounts')->where('user_id', $user->id)->first();

        $twoFactorPending = !empty($user->two_factor_secret) && empty($user->two_factor_confirmed_at);
        $twoFactorEnabled = !empty($user->two_factor_secret) && !empty($user->two_factor_confirmed_at);

        $twoFactorSecretKey = null;
        $twoFactorQrCode = null;
        $twoFactorRecoveryCodes = [];

        if (!empty($user->two_factor_secret)) {
            try {
                $twoFactorSecretKey = decrypt($user->two_factor_secret);
            } catch (\Throwable) {
                $twoFactorSecretKey = null;
            }

            try {
                $twoFactorQrCode = $user->twoFactorQrCodeSvg();
            } catch (\Throwable) {
                $twoFactorQrCode = null;
            }
        }

        if ($twoFactorEnabled) {
            try {
                $twoFactorRecoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true) ?: [];
            } catch (\Throwable) {
                $twoFactorRecoveryCodes = [];
            }
        }

        return view('settings.index', [
            'user' => $user,
            'account' => $account,
            'languages' => config('ridewrench.languages', []),
            'twoFactorPending' => $twoFactorPending,
            'twoFactorEnabled' => $twoFactorEnabled,
            'twoFactorSecretKey' => $twoFactorSecretKey,
            'twoFactorQrCode' => $twoFactorQrCode,
            'twoFactorRecoveryCodes' => $twoFactorRecoveryCodes,
        ]);
    }

    public function profile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user->id)],
            'current_password' => ['required', 'string'],
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return redirect('/settings')->with('error', 'currentPasswordInvalid');
        }

        DB::table('users')
            ->where('id', $user->id)
            ->update([
                'name' => trim($validated['name']),
                'email' => strtolower(trim($validated['email'])),
                'updated_at' => now(),
            ]);

        return redirect('/settings')->with('success', 'profileUpdated');
    }

    public function password(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8'],
            'new_password_repeat' => ['required', 'string'],
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return redirect('/settings')->with('error', 'currentPasswordInvalid');
        }

        if ($validated['new_password'] !== $validated['new_password_repeat']) {
            return redirect('/settings')->with('error', 'passwordsDoNotMatch');
        }

        DB::table('users')
            ->where('id', $user->id)
            ->update([
                'password' => Hash::make($validated['new_password']),
                'updated_at' => now(),
            ]);

        return redirect('/settings')->with('success', 'passwordChanged');
    }

    public function emailReminders(Request $request): RedirectResponse
    {
        $user = $request->user();

        DB::table('users')
            ->where('id', $user->id)
            ->update([
                'email_reminders_enabled' => $request->boolean('email_reminders_enabled'),
                'updated_at' => now(),
            ]);

        return redirect('/settings')->with('success', 'emailRemindersSaved');
    }

    public function language(Request $request): RedirectResponse
    {
        $availableLocales = array_keys(config('ridewrench.languages', []));

        $validated = $request->validate([
            'language' => ['required', 'string', 'in:' . implode(',', $availableLocales)],
        ]);

        $locale = $validated['language'];

        $request->session()->put('locale', $locale);

        DB::table('users')
            ->where('id', $request->user()->id)
            ->update([
                'language' => $locale,
                'updated_at' => now(),
            ]);

        return redirect('/settings')->with('success', 'settings.languageSaved');
    }

    public function exportData(Request $request): StreamedResponse
    {
        $user = $request->user();

        $filename = 'ridewrench-export-' . now()->format('Y-m-d-His') . '.json';

        return response()->streamDownload(
            function () use ($user): void {
                $data = [
                    'user' => DB::table('users')
                        ->select(['id', 'name', 'email', 'language', 'email_reminders_enabled', 'created_at', 'updated_at'])
                        ->where('id', $user->id)
                        ->first(),

                    'strava_account' => DB::table('strava_accounts')
                        ->select([
                            'athlete_id',
                            'athlete_name',
                            'last_synced_at',
                            'last_full_synced_at',
                            'created_at',
                            'updated_at',
                        ])
                        ->where('user_id', $user->id)
                        ->first(),

                    'bikes' => DB::table('bikes')->where('user_id', $user->id)->get(),

                    'activities' => DB::table('activities')->where('user_id', $user->id)->get(),

                    'maintenance_rules' => DB::table('maintenance_rules')->where('user_id', $user->id)->get(),

                    'maintenance_events' => DB::table('maintenance_events')->where('user_id', $user->id)->get(),

                    'maintenance_alerts' => DB::table('maintenance_alerts')->where('user_id', $user->id)->get(),
                ];

                echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            },
            $filename,
            [
                'Content-Type' => 'application/json',
            ],
        );
    }

    public function deleteAccount(Request $request, StravaClient $stravaClient): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'password' => ['required', 'string'],
            'confirm' => ['required', 'string'],
        ]);

        if (!Hash::check($validated['password'], $user->password)) {
            return redirect('/settings')->with('error', 'currentPasswordInvalid');
        }

        if ($validated['confirm'] !== 'DELETE') {
            return redirect('/settings')->with('error', 'deleteAccountConfirmInvalid');
        }

        $account = DB::table('strava_accounts')->where('user_id', $user->id)->first();

        if ($account) {
            try {
                $stravaClient->deauthorize((string) $account->access_token);
            } catch (\Throwable) {
                // Account deletion must still continue.
            }
        }

        Auth::guard('web')->logout();

        DB::table('users')->where('id', $user->id)->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'accountDeleted');
    }

    public function testEmail(Request $request): RedirectResponse
    {
        $user = $request->user();

        $sentAt = RideWrench::formatDateTime(now()->toIso8601String());
        $fromEmail = (string) config('mail.from.address');
        $toEmail = (string) ($user->email ?? '');
        $userName = (string) ($user->name ?? '');

        $subject = __('email.testSubject', [$sentAt]);

        $metaRows = [
            __('email.testMetaSentAt') => $sentAt,
            __('email.testMetaFrom') => $fromEmail,
            __('email.testMetaTo') => $toEmail,
        ];

        $body = '<!doctype html>';
        $body .= '<html lang="' . e(App::getLocale()) . '">';
        $body .= '<head>';
        $body .= '<meta charset="utf-8">';
        $body .= '<title>' . e($subject) . '</title>';
        $body .= '</head>';
        $body .=
            '<body style="margin:0;padding:0;background:#f4f6f8;color:#111827;font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:1.5;">';
        $body .= '<div style="max-width:720px;margin:0 auto;padding:24px;">';

        $body .= '<div style="background:#ffffff;border:1px solid #e5e7eb;border-radius:14px;overflow:hidden;">';

        $body .= '<div style="padding:20px 24px;background:#0f141b;color:#ffffff;">';
        $body .= '<div style="font-size:13px;opacity:.75;margin-bottom:4px;">RideWrench</div>';
        $body .= '<h1 style="margin:0;font-size:22px;line-height:1.25;">' . e(__('email.testHeadline')) . '</h1>';
        $body .= '</div>';

        $body .= '<div style="padding:24px;">';

        $body .= '<p style="margin:0 0 16px;">' . e(__('email.testGreeting', [$userName])) . '</p>';
        $body .= '<p style="margin:0 0 20px;">' . e(__('email.testIntro')) . '</p>';

        $body .=
            '<div style="background:#ecfdf5;border:1px solid #bbf7d0;border-radius:10px;padding:16px;margin-bottom:24px;color:#166534;">';
        $body .= '<strong>' . e(__('email.testSuccess')) . '</strong>';
        $body .= '</div>';

        $body .= '<h2 style="margin:0 0 12px;font-size:16px;">' . e(__('email.testDetails')) . '</h2>';
        $body .=
            '<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="width:100%;border-collapse:collapse;">';

        foreach ($metaRows as $label => $value) {
            $body .= '<tr>';
            $body .=
                '<td style="width:140px;padding:8px 10px;border-top:1px solid #e5e7eb;color:#6b7280;font-weight:bold;vertical-align:top;">' .
                e($label) .
                '</td>';
            $body .= '<td style="padding:8px 10px;border-top:1px solid #e5e7eb;vertical-align:top;">' . e($value) . '</td>';
            $body .= '</tr>';
        }

        $body .= '</table>';

        $body .= '<div style="margin-top:24px;">';
        $body .=
            '<a href="' .
            e(url('/settings')) .
            '" style="display:inline-block;background:#0f141b;color:#ffffff;text-decoration:none;border-radius:999px;padding:10px 16px;font-weight:bold;">';
        $body .= e(__('email.testOpenSettings'));
        $body .= '</a>';
        $body .= '</div>';

        $body .= '</div>';
        $body .= '</div>';

        $body .= '<div style="padding:14px 4px;color:#6b7280;font-size:12px;text-align:center;">';
        $body .= 'RideWrench · ' . e(rtrim((string) config('app.url'), '/'));
        $body .= '</div>';

        $body .= '</div>';
        $body .= '</body>';
        $body .= '</html>';

        $debug = null;

        if ($this->sendSettingsHtmlEmail($toEmail, $subject, $body, $debug)) {
            return redirect('/settings')->with('success', 'testEmailAccepted', [$debug]);
        }

        return redirect('/settings')->with('error', 'testEmailFailed', [$debug]);
    }

    public function testDueEmail(Request $request, MaintenanceService $maintenanceService): RedirectResponse
    {
        $user = $request->user();

        if (!$user || !$user->is_admin) {
            abort(403);
        }

        if (
            $maintenanceService->sendPreviewDueEmail([
                'email' => $user->email,
                'name' => $user->name,
                'language' => $user->language,
            ])
        ) {
            return redirect('/settings')->with('success', 'testEmailAccepted', ['Due email preview sent.']);
        }

        return redirect('/settings')->with('error', 'testEmailFailed', ['See app debug log.']);
    }

    private function sendSettingsHtmlEmail(string $to, string $subject, string $body, ?string &$debug = null): bool
    {
        try {
            Mail::html($body, function ($message) use ($to, $subject): void {
                $message->to($to);
                $message->subject($subject);
            });

            $debug = 'SMTP accepted.';

            return true;
        } catch (\Throwable $e) {
            $debug = $e->getMessage();

            Log::error('Settings test email failed: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return false;
        }
    }
}
