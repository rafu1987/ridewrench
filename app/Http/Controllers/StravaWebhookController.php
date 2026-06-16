<?php

namespace App\Http\Controllers;

use App\Services\MaintenanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

final class StravaWebhookController extends Controller
{
    public function verify(Request $request): JsonResponse|Response
    {
        $mode = (string) ($request->query('hub_mode') ?? ($request->query('hub.mode') ?? ''));
        $verifyToken = (string) ($request->query('hub_verify_token') ?? ($request->query('hub.verify_token') ?? ''));
        $challenge = (string) ($request->query('hub_challenge') ?? ($request->query('hub.challenge') ?? ''));

        if ($mode === 'subscribe' && hash_equals((string) config('services.strava.webhook_verify_token'), $verifyToken)) {
            return response()->json([
                'hub.challenge' => $challenge,
            ]);
        }

        return response("Forbidden\n", 403)->header('Content-Type', 'text/plain; charset=utf-8');
    }

    public function handle(Request $request, MaintenanceService $service): Response
    {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return response("Invalid JSON\n", 400)->header('Content-Type', 'text/plain; charset=utf-8');
        }

        $objectType = (string) ($payload['object_type'] ?? '');
        $aspectType = (string) ($payload['aspect_type'] ?? '');
        $ownerId = (int) ($payload['owner_id'] ?? 0);
        $objectId = (string) ($payload['object_id'] ?? '');

        $account = DB::table('strava_accounts')->where('athlete_id', $ownerId)->first();

        $eventId = $this->createStravaWebhookEvent($payload, $account ? (array) $account : null);

        if (!$account) {
            $this->updateStravaWebhookEvent($eventId, 'ignored', 'No local Strava account found for athlete_id ' . $ownerId);

            return response("OK\n", 200)->header('Content-Type', 'text/plain; charset=utf-8');
        }

        $userId = (int) $account->user_id;

        try {
            if ($objectType === 'athlete' && $aspectType === 'update') {
                DB::table('strava_accounts')->where('user_id', $userId)->delete();

                $this->updateStravaWebhookEvent($eventId, 'processed');
            } elseif ($objectType === 'activity' && $aspectType === 'delete') {
                DB::table('activities')->where('user_id', $userId)->where('strava_activity_id', $objectId)->delete();

                $this->updateStravaWebhookEvent($eventId, 'processed');
            } elseif ($objectType === 'activity' && in_array($aspectType, ['create', 'update'], true)) {
                $service->syncUser($userId);

                $this->updateStravaWebhookEvent($eventId, 'processed');
            } else {
                $this->updateStravaWebhookEvent(
                    $eventId,
                    'ignored',
                    'Unsupported webhook event: ' . $objectType . '/' . $aspectType,
                );
            }
        } catch (\Throwable $e) {
            $service->markSyncError($userId, $e->getMessage());

            $this->updateStravaWebhookEvent($eventId, 'failed', $e->getMessage());
        }

        return response("OK\n", 200)->header('Content-Type', 'text/plain; charset=utf-8');
    }

    private function createStravaWebhookEvent(array $payload, ?array $account): int
    {
        return (int) DB::table('strava_webhook_events')->insertGetId([
            'user_id' => $account['user_id'] ?? null,
            'athlete_id' => (int) ($payload['owner_id'] ?? 0),
            'object_type' => (string) ($payload['object_type'] ?? ''),
            'aspect_type' => (string) ($payload['aspect_type'] ?? ''),
            'object_id' => (string) ($payload['object_id'] ?? ''),
            'event_time' => (int) ($payload['event_time'] ?? 0),
            'payload' => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'status' => 'received',
            'error' => null,
            'received_at' => now(),
            'processed_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function updateStravaWebhookEvent(int $eventId, string $status, ?string $error = null): void
    {
        DB::table('strava_webhook_events')
            ->where('id', $eventId)
            ->update([
                'status' => $status,
                'error' => $error,
                'processed_at' => now(),
                'updated_at' => now(),
            ]);
    }
}
