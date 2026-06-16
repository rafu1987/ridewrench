<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

final class MaintenanceService
{
    public function syncUser(int $userId, bool $full = false): int
    {
        $account = DB::table('strava_accounts')->where('user_id', $userId)->first();

        if (!$account) {
            return 0;
        }

        $account = (array) $account;
        $client = new StravaClient();

        if ((int) $account['expires_at'] <= time() + 120) {
            $token = $client->refresh($account);

            $account['access_token'] = (string) $token['access_token'];
            $account['refresh_token'] = (string) $token['refresh_token'];
            $account['expires_at'] = (int) $token['expires_at'];

            DB::table('strava_accounts')
                ->where('id', $account['id'])
                ->update([
                    'access_token' => $account['access_token'],
                    'refresh_token' => $account['refresh_token'],
                    'expires_at' => $account['expires_at'],
                    'updated_at' => now(),
                ]);
        }

        $athlete = $client->athlete((string) $account['access_token']);

        $bikeNamesByGearId = $this->syncBikesFromAthlete($userId, $athlete);
        $gearNameCache = $this->syncExistingBikeNamesFromGear(
            $userId,
            $client,
            (string) $account['access_token'],
            $bikeNamesByGearId,
        );

        $lastStartedAt = DB::table('activities')->where('user_id', $userId)->max('started_at');

        $lastTimestamp = $lastStartedAt ? strtotime((string) $lastStartedAt) : 0;
        $after = $full ? 0 : max(0, $lastTimestamp - 86400);

        $count = 0;
        $maxPages = $full ? 20 : 5;

        for ($page = 1; $page <= $maxPages; $page++) {
            $activities = $client->activities((string) $account['access_token'], $after, $page);

            if (!$activities) {
                break;
            }

            foreach ($activities as $activity) {
                if (!is_array($activity) || !$this->isCyclingActivity($activity)) {
                    continue;
                }

                $gearId = $activity['gear_id'] ?? null;

                if (!$this->isBikeGearId($gearId)) {
                    continue;
                }

                $gearId = (string) $gearId;
                $bikeName = $gearNameCache[$gearId] ?? '';

                if ($bikeName === '') {
                    $bikeName = trim((string) ($activity['gear']['name'] ?? ''));
                }

                if ($bikeName === '') {
                    try {
                        $gear = $client->gear((string) $account['access_token'], $gearId);
                        $bikeName = trim((string) ($gear['name'] ?? ''));

                        if ($bikeName !== '') {
                            $gearNameCache[$gearId] = $bikeName;
                        }
                    } catch (\Throwable) {
                    }
                }

                if ($bikeName === '') {
                    $bikeName = 'Strava bike ' . $gearId;
                }

                $bikeId = $this->ensureBike($userId, $gearId, $bikeName);

                $activityId = (int) ($activity['id'] ?? 0);

                if ($activityId <= 0) {
                    continue;
                }

                $alreadyExists = $this->activityExists($userId, $activityId);

                DB::table('activities')->updateOrInsert(
                    [
                        'user_id' => $userId,
                        'strava_activity_id' => $activityId,
                    ],
                    [
                        'bike_id' => $bikeId,
                        'strava_gear_id' => $gearId,
                        'name' => (string) ($activity['name'] ?? 'Ride'),
                        'sport_type' => (string) ($activity['sport_type'] ?? ($activity['type'] ?? 'Ride')),
                        'distance_m' => (float) ($activity['distance'] ?? 0),
                        'moving_time' => (int) ($activity['moving_time'] ?? 0),
                        'started_at' => $this->parseStravaDate(
                            $activity['start_date_local'] ?? ($activity['start_date'] ?? null),
                        ),
                        'synced_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                );

                if (!$alreadyExists) {
                    $count++;
                }
            }

            if (count($activities) < 100) {
                break;
            }
        }

        $this->createDueAlerts($userId);
        $this->markSyncSuccess($userId, $full);

        return $count;
    }

    public function markSyncError(int $userId, string $message): void
    {
        DB::table('strava_accounts')
            ->where('user_id', $userId)
            ->update([
                'last_sync_error' => Str::limit($message, 2000, ''),
                'updated_at' => now(),
            ]);
    }

    public function ensureBike(int $userId, string $gearId, string $name): int
    {
        $bike = DB::table('bikes')->where('user_id', $userId)->where('strava_gear_id', $gearId)->first();

        if ($bike) {
            $currentName = (string) $bike->name;

            $currentNameIsFallback = str_starts_with($currentName, 'Strava bike ');
            $newNameIsReal = $name !== '' && !str_starts_with($name, 'Strava bike ');

            if ($currentNameIsFallback && $newNameIsReal && $name !== $currentName) {
                DB::table('bikes')
                    ->where('id', $bike->id)
                    ->where('user_id', $userId)
                    ->update([
                        'name' => $name,
                        'updated_at' => now(),
                    ]);
            }

            return (int) $bike->id;
        }

        return (int) DB::table('bikes')->insertGetId([
            'user_id' => $userId,
            'strava_gear_id' => $gearId,
            'name' => $name,
            'type' => 'other',
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * @param array<string, mixed>|object $rule
     * @return array{last_date: string|null, distance_km: float, days: int}
     */
    public function statsForRule(array|object $rule): array
    {
        $rule = (array) $rule;

        $lastDate = DB::table('maintenance_events')
            ->where('rule_id', $rule['id'])
            ->orderByDesc('performed_at')
            ->orderByDesc('id')
            ->value('performed_at');

        if ($lastDate) {
            $distanceKm = (float) DB::table('activities')
                ->where('bike_id', $rule['bike_id'])
                ->where('started_at', '>', $lastDate)
                ->selectRaw('COALESCE(SUM(distance_m), 0) / 1000 AS km')
                ->value('km');

            $days = max(0, (int) floor((time() - strtotime((string) $lastDate)) / 86400));
        } else {
            $distanceKm = (float) DB::table('activities')
                ->where('bike_id', $rule['bike_id'])
                ->selectRaw('COALESCE(SUM(distance_m), 0) / 1000 AS km')
                ->value('km');

            $firstDate = DB::table('activities')->where('bike_id', $rule['bike_id'])->min('started_at');

            $days = $firstDate ? max(0, (int) floor((time() - strtotime((string) $firstDate)) / 86400)) : 0;
        }

        return [
            'last_date' => $lastDate ? (string) $lastDate : null,
            'distance_km' => round($distanceKm, 1),
            'days' => $days,
        ];
    }

    public function createDueAlerts(?int $onlyUserId = null): int
    {
        $query = DB::table('maintenance_rules as r')
            ->join('bikes as b', 'b.id', '=', 'r.bike_id')
            ->select(['r.*', 'b.name as bike_name'])
            ->where('r.active', true)
            ->where('b.active', true);

        if ($onlyUserId) {
            $query->where('r.user_id', $onlyUserId);
        }

        $created = 0;

        foreach ($query->get() as $rule) {
            $stats = $this->statsForRule($rule);
            $reasons = [];

            if ($rule->distance_km !== null && $stats['distance_km'] >= (float) $rule->distance_km) {
                $reasons[] = $stats['distance_km'] . ' km since last service';
            }

            if ($rule->interval_days !== null && $stats['days'] >= (int) $rule->interval_days) {
                $reasons[] = $stats['days'] . ' days since last service';
            }

            if (!$reasons) {
                continue;
            }

            $exists = DB::table('maintenance_alerts')
                ->where('user_id', $rule->user_id)
                ->where('rule_id', $rule->id)
                ->where('status', 'open')
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('maintenance_alerts')->insert([
                'user_id' => $rule->user_id,
                'bike_id' => $rule->bike_id,
                'rule_id' => $rule->id,
                'status' => 'open',
                'due_reason' => implode(' and ', $reasons),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $created++;
        }

        return $created;
    }

    public function sendDueEmails(): int
    {
        $alerts = DB::table('maintenance_alerts as a')
            ->join('users as u', 'u.id', '=', 'a.user_id')
            ->join('bikes as b', 'b.id', '=', 'a.bike_id')
            ->join('maintenance_rules as r', 'r.id', '=', 'a.rule_id')
            ->select([
                'a.*',
                'u.email',
                'u.name as user_name',
                'u.language',
                'b.name as bike_name',
                'r.name as rule_name',
                'r.distance_km as rule_distance_km',
                'r.interval_days as rule_interval_days',
            ])
            ->where('a.status', 'open')
            ->whereNull('a.sent_at')
            ->where('r.email_enabled', true)
            ->where('b.active', true)
            ->whereRaw('COALESCE(u.email_reminders_enabled, 1) = 1')
            ->get();

        $sent = 0;

        foreach ($alerts as $alert) {
            $previousLocale = App::getLocale();

            try {
                App::setLocale((string) ($alert->language ?? 'en'));

                $userName = trim((string) ($alert->user_name ?? ''));

                if ($userName === '') {
                    $userName = (string) $alert->email;
                }

                $bikeName = (string) $alert->bike_name;
                $ruleName = (string) $alert->rule_name;
                $reason = $this->translatedDueReason((array) $alert);

                $subject = __('email.maintenanceDueSubject', [$bikeName, $ruleName]);

                $body = $this->renderMaintenanceDueEmail($subject, $userName, $bikeName, $ruleName, $reason);

                if ($this->sendHtmlEmail((string) $alert->email, $subject, $body)) {
                    DB::table('maintenance_alerts')
                        ->where('id', $alert->id)
                        ->update([
                            'sent_at' => now(),
                            'updated_at' => now(),
                        ]);

                    $sent++;
                } else {
                    Log::error('Maintenance due email failed for alert ' . (string) $alert->id);
                }
            } finally {
                App::setLocale($previousLocale);
            }
        }

        return $sent;
    }

    public function sendPreviewDueEmail(array $user): bool
    {
        $previousLocale = App::getLocale();

        try {
            App::setLocale((string) ($user['language'] ?? 'en'));

            $userName = trim((string) ($user['name'] ?? ''));

            if ($userName === '') {
                $userName = (string) ($user['email'] ?? '');
            }

            $bikeName = 'Test Bike';
            $ruleName = 'Chain wax';
            $reason = '- ' . __('email.dueReasonDistance', [\App\Support\RideWrench::formatNumber(325.4, 1)]);

            $subject = __('email.maintenanceDueSubject', [$bikeName, $ruleName]);

            $body = $this->renderMaintenanceDueEmail($subject, $userName, $bikeName, $ruleName, $reason);

            return $this->sendHtmlEmail((string) $user['email'], $subject, $body);
        } finally {
            App::setLocale($previousLocale);
        }
    }

    private function translatedDueReason(array $alert): string
    {
        $stats = $this->statsForRule([
            'id' => $alert['rule_id'],
            'bike_id' => $alert['bike_id'],
        ]);

        $reasons = [];

        if ($alert['rule_distance_km'] !== null && $stats['distance_km'] >= (float) $alert['rule_distance_km']) {
            $reasons[] = __('email.dueReasonDistance', [\App\Support\RideWrench::formatNumber((float) $stats['distance_km'], 1)]);
        }

        if ($alert['rule_interval_days'] !== null && $stats['days'] >= (int) $alert['rule_interval_days']) {
            $reasons[] = __('email.dueReasonDays', [(string) $stats['days']]);
        }

        if ($reasons) {
            return implode("\n", array_map(static fn(string $reason): string => '- ' . $reason, $reasons));
        }

        return (string) ($alert['due_reason'] ?? '');
    }

    private function renderMaintenanceDueEmail(
        string $subject,
        string $userName,
        string $bikeName,
        string $ruleName,
        string $reason,
    ): string {
        $dashboardUrl = url('/dashboard');

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
        $body .= '<h1 style="margin:0;font-size:22px;line-height:1.25;">' . e($subject) . '</h1>';
        $body .= '</div>';

        $body .= '<div style="padding:24px;">';

        $body .= '<p style="margin:0 0 16px;">' . e(__('email.maintenanceDueGreeting', [$userName])) . '</p>';
        $body .= '<p style="margin:0 0 20px;">' . e(__('email.maintenanceDueIntro', [$bikeName, $ruleName])) . '</p>';

        $body .=
            '<div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;padding:16px;margin-bottom:24px;color:#9a3412;">';
        $body .= '<div style="font-weight:bold;margin-bottom:8px;">' . e(__('email.maintenanceDueReasonTitle')) . '</div>';
        $body .= '<div style="white-space:pre-wrap;">' . e($reason) . '</div>';
        $body .= '</div>';

        $metaRows = [
            __('email.maintenanceDueBike') => $bikeName,
            __('email.maintenanceDueRule') => $ruleName,
        ];

        $body .=
            '<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="width:100%;border-collapse:collapse;margin-bottom:24px;">';

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

        $body .=
            '<a href="' .
            e($dashboardUrl) .
            '" style="display:inline-block;background:#0f141b;color:#ffffff;text-decoration:none;border-radius:999px;padding:10px 16px;font-weight:bold;">';
        $body .= e(__('email.maintenanceDueOpenDashboard'));
        $body .= '</a>';

        $body .= '</div>';
        $body .= '</div>';

        $body .= '<div style="padding:14px 4px;color:#6b7280;font-size:12px;text-align:center;">';
        $body .= 'RideWrench · ' . e(rtrim((string) config('app.url'), '/'));
        $body .= '</div>';

        $body .= '</div>';
        $body .= '</body>';
        $body .= '</html>';

        return $body;
    }

    private function sendHtmlEmail(string $to, string $subject, string $body): bool
    {
        try {
            Mail::html($body, function ($message) use ($to, $subject): void {
                $message->to($to);
                $message->subject($subject);
            });

            return true;
        } catch (\Throwable $e) {
            Log::error('RideWrench email failed: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return false;
        }
    }

    private function markSyncSuccess(int $userId, bool $full): void
    {
        $data = [
            'last_synced_at' => now(),
            'last_sync_error' => null,
            'updated_at' => now(),
        ];

        if ($full) {
            $data['last_full_synced_at'] = now();
        }

        DB::table('strava_accounts')->where('user_id', $userId)->update($data);
    }

    /**
     * @param array<string, mixed> $athlete
     * @return array<string, string>
     */
    private function syncBikesFromAthlete(int $userId, array $athlete): array
    {
        $bikeNamesByGearId = [];

        foreach ($athlete['bikes'] ?? [] as $bike) {
            if (!is_array($bike)) {
                continue;
            }

            $gearId = (string) ($bike['id'] ?? '');
            $name = trim((string) ($bike['name'] ?? ''));

            if (!$this->isBikeGearId($gearId) || $name === '') {
                continue;
            }

            $bikeNamesByGearId[$gearId] = $name;
            $this->ensureBike($userId, $gearId, $name);
        }

        return $bikeNamesByGearId;
    }

    /**
     * @param array<string, string> $gearNameCache
     * @return array<string, string>
     */
    private function syncExistingBikeNamesFromGear(
        int $userId,
        StravaClient $client,
        string $accessToken,
        array $gearNameCache = [],
    ): array {
        $gearIds = DB::table('bikes')
            ->where('user_id', $userId)
            ->where('strava_gear_id', 'like', 'b%')
            ->orderBy('id')
            ->pluck('strava_gear_id');

        foreach ($gearIds as $gearId) {
            $gearId = (string) $gearId;

            if (!$this->isBikeGearId($gearId)) {
                continue;
            }

            if (!empty($gearNameCache[$gearId])) {
                continue;
            }

            try {
                $gear = $client->gear($accessToken, $gearId);
                $bikeName = trim((string) ($gear['name'] ?? ''));

                if ($bikeName !== '') {
                    $gearNameCache[$gearId] = $bikeName;
                    $this->ensureBike($userId, $gearId, $bikeName);
                }
            } catch (\Throwable) {
            }
        }

        return $gearNameCache;
    }

    private function activityExists(int $userId, int $activityId): bool
    {
        return DB::table('activities')->where('user_id', $userId)->where('strava_activity_id', $activityId)->exists();
    }

    private function isBikeGearId(mixed $gearId): bool
    {
        return is_string($gearId) && str_starts_with($gearId, 'b');
    }

    /**
     * @param array<string, mixed> $activity
     */
    private function isCyclingActivity(array $activity): bool
    {
        $sportType = (string) ($activity['sport_type'] ?? ($activity['type'] ?? ''));

        return in_array(
            $sportType,
            [
                'Ride',
                'VirtualRide',
                'MountainBikeRide',
                'GravelRide',
                'EBikeRide',
                'EMountainBikeRide',
                'Velomobile',
                'Handcycle',
            ],
            true,
        );
    }

    private function parseStravaDate(mixed $value): string
    {
        if (!is_string($value) || trim($value) === '') {
            return now()->toDateTimeString();
        }

        return Carbon::parse($value)->toDateTimeString();
    }
}
