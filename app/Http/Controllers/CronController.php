<?php

namespace App\Http\Controllers;

use App\Services\MaintenanceService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class CronController extends Controller
{
    public function __invoke(Request $request, MaintenanceService $service): Response
    {
        $startedAt = microtime(true);
        $cronRunId = null;

        try {
            $configuredToken = (string) config('ridewrench.cron_token');
            $providedToken = (string) $request->query('token', '');

            if ($configuredToken === '') {
                throw new \RuntimeException("config('ridewrench.cron_token') is empty.");
            }

            if (!hash_equals($configuredToken, $providedToken)) {
                return response("Forbidden\n", 403)->header('Content-Type', 'text/plain; charset=utf-8');
            }

            Artisan::call('migrate', [
                '--force' => true,
            ]);

            $cronRunId = $this->createCronRun();

            $users = DB::table('users')
                ->select(['id'])
                ->orderBy('id')
                ->get();

            $synced = 0;
            $failed = 0;
            $errors = [];

            foreach ($users as $cronUser) {
                $userId = (int) $cronUser->id;

                try {
                    $synced += $service->syncUser($userId);
                } catch (\Throwable $e) {
                    $failed++;

                    $message = 'User ' . $userId . ': ' . $e->getMessage();
                    $errors[] = $message;

                    $service->markSyncError($userId, $e->getMessage());

                    Log::error('Cron sync error for user ' . $userId . ': ' . $e->getMessage(), [
                        'exception' => $e,
                    ]);
                }
            }

            try {
                $emailsSent = $service->sendDueEmails();
            } catch (\Throwable $e) {
                $emailsSent = 0;
                $failed++;
                $errors[] = 'Email sending: ' . $e->getMessage();

                Log::error('Cron email error: ' . $e->getMessage(), [
                    'exception' => $e,
                ]);
            }

            $this->finishCronRun(
                $cronRunId,
                $failed > 0 ? 'completed_with_errors' : 'completed',
                $users->count(),
                $failed,
                $synced,
                $emailsSent,
                $errors ? implode("\n", $errors) : null,
                $startedAt,
            );

            $durationMs = (int) round((microtime(true) - $startedAt) * 1000);

            $output = '';
            $output .= "RideWrench cron OK\n";
            $output .= 'Users checked: ' . $users->count() . "\n";
            $output .= 'Failed users/tasks: ' . $failed . "\n";
            $output .= 'Synced new activities: ' . $synced . "\n";
            $output .= 'Emails sent: ' . $emailsSent . "\n";
            $output .= 'Duration ms: ' . $durationMs . "\n";
            $output .= 'Finished: ' . now()->toIso8601String() . "\n";

            if ($errors) {
                $output .= "\nErrors:\n";

                foreach ($errors as $error) {
                    $output .= '- ' . $error . "\n";
                }
            }

            return response($output, 200)->header('Content-Type', 'text/plain; charset=utf-8');
        } catch (\Throwable $e) {
            if ($cronRunId !== null) {
                $this->finishCronRun($cronRunId, 'failed', 0, 1, 0, 0, $e->getMessage(), $startedAt);
            }

            $isDev = app()->environment(['local', 'dev', 'testing']);

            $output = '';
            $output .= "RideWrench cron error\n";
            $output .= 'Environment: ' . app()->environment() . "\n";
            $output .= 'Message: ' . $e->getMessage() . "\n";

            if ($isDev) {
                $output .= 'File: ' . $e->getFile() . "\n";
                $output .= 'Line: ' . $e->getLine() . "\n\n";
                $output .= $e->getTraceAsString() . "\n";
            }

            return response($output, $isDev ? 200 : 500)->header('Content-Type', 'text/plain; charset=utf-8');
        }
    }

    private function createCronRun(): int
    {
        return (int) DB::table('cron_runs')->insertGetId([
            'status' => 'running',
            'users_checked' => 0,
            'failed_tasks' => 0,
            'synced_activities' => 0,
            'emails_sent' => 0,
            'error' => null,
            'started_at' => now(),
            'finished_at' => null,
            'duration_ms' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function finishCronRun(
        int $cronRunId,
        string $status,
        int $usersChecked,
        int $failedTasks,
        int $syncedActivities,
        int $emailsSent,
        ?string $error,
        float $startedAt,
    ): void {
        DB::table('cron_runs')
            ->where('id', $cronRunId)
            ->update([
                'status' => $status,
                'users_checked' => $usersChecked,
                'failed_tasks' => $failedTasks,
                'synced_activities' => $syncedActivities,
                'emails_sent' => $emailsSent,
                'error' => $error,
                'finished_at' => now(),
                'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
                'updated_at' => now(),
            ]);
    }
}
