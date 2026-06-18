<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

final class StravaClient
{
    private string $baseUrl = 'https://www.strava.com/api/v3';

    /**
     * @param array<string, mixed> $account
     * @return array<string, mixed>
     */
    public function refresh(array $account): array
    {
        return $this->postToken([
            'client_id' => (string) config('services.strava.client_id'),
            'client_secret' => (string) config('services.strava.client_secret'),
            'refresh_token' => (string) $account['refresh_token'],
            'grant_type' => 'refresh_token',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function deauthorize(string $accessToken): array
    {
        $response = Http::acceptJson()->withToken($accessToken)->timeout(30)->post('https://www.strava.com/oauth/deauthorize');

        if (!$response->successful()) {
            throw new RuntimeException('Strava deauthorize error HTTP ' . $response->status() . ': ' . $response->body());
        }

        return $response->json() ?: [];
    }

    /**
     * @return array<string, mixed>
     */
    public function athlete(string $accessToken): array
    {
        return $this->get('/athlete', $accessToken);
    }

    /**
     * @return array<string, mixed>
     */
    public function gear(string $accessToken, string $gearId): array
    {
        return $this->get('/gear/' . rawurlencode($gearId), $accessToken);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function activities(string $accessToken, int $after = 0, int $page = 1): array
    {
        $query = [
            'page' => $page,
            'per_page' => 100,
        ];

        if ($after > 0) {
            $query['after'] = $after;
        }

        return $this->get('/athlete/activities?' . http_build_query($query), $accessToken);
    }

    /**
     * @return array<string, mixed>|array<int, array<string, mixed>>
     */
    private function get(string $path, string $accessToken): array
    {
        $response = Http::acceptJson()
            ->withToken($accessToken)
            ->timeout(30)
            ->get($this->baseUrl . $path);

        if (!$response->successful()) {
            throw new RuntimeException('Strava API error HTTP ' . $response->status() . ': ' . $response->body());
        }

        return $response->json() ?: [];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function postToken(array $payload): array
    {
        $response = Http::asForm()->acceptJson()->timeout(30)->post('https://www.strava.com/oauth/token', $payload);

        if (!$response->successful()) {
            throw new RuntimeException('Strava token error HTTP ' . $response->status() . ': ' . $response->body());
        }

        return $response->json() ?: [];
    }
}
