@section('title', __('admin.title'))

<x-app-layout>
    <div class="page-hero mb-4">
        <div>
            <p class="text-white-50 mb-1">
                <i class="fa-light fa-sharp fa-screwdriver-wrench me-1"></i>
                {{ __('admin.kicker') }}
            </p>

            <h1 class="display-6 fw-bold mb-1">
                {{ __('admin.title') }}
            </h1>

            <p class="mb-0 text-white-50">
                {{ __('admin.subtitle') }}
            </p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card card-lift h-100">
                <div class="card-body p-3">
                    <div class="text-muted small">{{ __('admin.users') }}</div>
                    <div class="h3 mb-0">{{ $stats['users'] }}</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card card-lift h-100">
                <div class="card-body p-3">
                    <div class="text-muted small">{{ __('admin.stravaAccounts') }}</div>
                    <div class="h3 mb-0">{{ $stats['strava_accounts'] }}</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card card-lift h-100">
                <div class="card-body p-3">
                    <div class="text-muted small">{{ __('admin.bikes') }}</div>
                    <div class="h3 mb-0">
                        {{ $stats['active_bikes'] }}
                        <span class="text-muted fs-6">/ {{ $stats['bikes'] }}</span>
                    </div>
                    <div class="text-muted small">{{ __('admin.activeTotal') }}</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card card-lift h-100">
                <div class="card-body p-3">
                    <div class="text-muted small">{{ __('admin.activities') }}</div>
                    <div class="h3 mb-0">{{ $stats['activities'] }}</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card card-lift h-100">
                <div class="card-body p-3">
                    <div class="text-muted small">{{ __('admin.rules') }}</div>
                    <div class="h3 mb-0">{{ $stats['rules'] }}</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card card-lift h-100">
                <div class="card-body p-3">
                    <div class="text-muted small">{{ __('admin.openAlerts') }}</div>
                    <div class="h3 mb-0">{{ $stats['open_alerts'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-lift mb-4">
        <div class="card-body p-3 p-md-4">
            <h2 class="h5 mb-3">
                <i class="fa-light fa-sharp fa-clock-rotate-left me-1"></i>
                {{ __('admin.cronHealth') }}
            </h2>

            @if (!$lastCronRun)
                <p class="text-muted mb-0">
                    {{ __('admin.noCronRuns') }}
                </p>
            @else
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <div class="metric-box h-100">
                            <div class="text-muted small">{{ __('admin.lastRun') }}</div>
                            <div class="fw-bold">
                                {{ \App\Support\RideWrench::formatDateTime($lastCronRun['started_at']) }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="metric-box h-100">
                            <div class="text-muted small">{{ __('bikes.status') }}</div>
                            <div class="fw-bold">
                                {{ __('cronStatus.' . $lastCronRun['status']) }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="metric-box h-100">
                            <div class="text-muted small">{{ __('admin.usersChecked') }}</div>
                            <div class="fw-bold">
                                {{ $lastCronRun['users_checked'] }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="metric-box h-100">
                            <div class="text-muted small">{{ __('admin.syncedActivities') }}</div>
                            <div class="fw-bold">
                                {{ $lastCronRun['synced_activities'] }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="metric-box h-100">
                            <div class="text-muted small">{{ __('admin.emailsSent') }}</div>
                            <div class="fw-bold">
                                {{ $lastCronRun['emails_sent'] }}
                            </div>
                        </div>
                    </div>
                </div>

                @if (!empty($lastCronRun['error']))
                    <div class="alert alert-danger small mb-3">
                        <strong>{{ __('admin.error') }}:</strong><br>
                        {!! nl2br(e($lastCronRun['error'])) !!}
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('common.created') }}</th>
                                <th>{{ __('bikes.status') }}</th>
                                <th>{{ __('admin.usersChecked') }}</th>
                                <th>{{ __('admin.failedTasks') }}</th>
                                <th>{{ __('admin.syncedActivities') }}</th>
                                <th>{{ __('admin.emailsSent') }}</th>
                                <th>{{ __('admin.duration') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cronRuns as $cronRun)
                                <tr>
                                    <td>
                                        {{ \App\Support\RideWrench::formatDateTime($cronRun['started_at']) }}
                                    </td>
                                    <td>
                                        @php
                                            $statusClass = match ($cronRun['status']) {
                                                'completed' => 'text-success',
                                                'completed_with_errors' => 'text-warning',
                                                'failed' => 'text-danger',
                                                default => 'text-muted',
                                            };
                                        @endphp

                                        <span class="{{ $statusClass }}">
                                            {{ __('cronStatus.' . $cronRun['status']) }}
                                        </span>
                                    </td>
                                    <td>{{ $cronRun['users_checked'] }}</td>
                                    <td>{{ $cronRun['failed_tasks'] }}</td>
                                    <td>{{ $cronRun['synced_activities'] }}</td>
                                    <td>{{ $cronRun['emails_sent'] }}</td>
                                    <td>
                                        @if (!empty($cronRun['duration_ms']))
                                            {{ \App\Support\RideWrench::formatNumber(((int) $cronRun['duration_ms']) / 1000, 2) }} s
                                        @else
                                            {{ __('common.notSet') }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="card card-lift mb-4">
        <div class="card-body p-3 p-md-4">
            <h2 class="h5 mb-3">
                <i class="fa-brands fa-strava me-1"></i>
                {{ __('admin.stravaSyncStatus') }}
            </h2>

            @if (!$stravaAccounts)
                <p class="text-muted mb-0">{{ __('admin.noStravaAccounts') }}</p>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('admin.user') }}</th>
                                <th>{{ __('settings.strava') }}</th>
                                <th>{{ __('strava.lastSync') }}</th>
                                <th>{{ __('strava.lastFullSync') }}</th>
                                <th>{{ __('strava.tokenExpires') }}</th>
                                <th>{{ __('strava.lastSyncError') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($stravaAccounts as $account)
                                @php
                                    $tokenExpiresAt = !empty($account['expires_at'])
                                        ? date('c', (int) $account['expires_at'])
                                        : null;

                                    $tokenExpired = $tokenExpiresAt && (int) $account['expires_at'] <= time();
                                @endphp

                                <tr>
                                    <td>
                                        <strong>{{ $account['user_name'] ?: $account['email'] }}</strong>
                                        <div class="text-muted small">{{ $account['email'] }}</div>
                                    </td>

                                    <td>
                                        {{ $account['athlete_name'] ?: '-' }}
                                        <div class="text-muted small">
                                            ID: {{ $account['athlete_id'] }}
                                        </div>
                                    </td>

                                    <td>
                                        @if (!empty($account['last_synced_at']))
                                            {{ \App\Support\RideWrench::formatDateTime($account['last_synced_at']) }}
                                        @else
                                            {{ __('common.notSet') }}
                                        @endif
                                    </td>

                                    <td>
                                        @if (!empty($account['last_full_synced_at']))
                                            {{ \App\Support\RideWrench::formatDateTime($account['last_full_synced_at']) }}
                                        @else
                                            {{ __('common.notSet') }}
                                        @endif
                                    </td>

                                    <td>
                                        @if ($tokenExpiresAt)
                                            <span class="{{ $tokenExpired ? 'text-danger fw-bold' : '' }}">
                                                {{ \App\Support\RideWrench::formatDateTime($tokenExpiresAt) }}
                                            </span>
                                        @else
                                            {{ __('common.notSet') }}
                                        @endif
                                    </td>

                                    <td>
                                        @if (!empty($account['last_sync_error']))
                                            <span class="text-danger small">
                                                {{ $account['last_sync_error'] }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="card card-lift mb-4">
        <div class="card-body p-3 p-md-4">
            <h2 class="h5 mb-3">
                <i class="fa-light fa-sharp fa-webhook me-1"></i>
                {{ __('admin.webhookEvents') }}
            </h2>

            @if (!$webhookEvents)
                <p class="text-muted mb-0">{{ __('admin.noWebhookEvents') }}</p>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('common.created') }}</th>
                                <th>{{ __('admin.user') }}</th>
                                <th>{{ __('admin.event') }}</th>
                                <th>{{ __('bikes.status') }}</th>
                                <th>{{ __('admin.error') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($webhookEvents as $event)
                                <tr>
                                    <td>
                                        @if (!empty($event['received_at']))
                                            {{ \App\Support\RideWrench::formatDateTime($event['received_at']) }}
                                        @else
                                            {{ __('common.notSet') }}
                                        @endif
                                    </td>

                                    <td>
                                        @if (!empty($event['email']))
                                            <strong>{{ $event['user_name'] ?: $event['email'] }}</strong>
                                            <div class="text-muted small">{{ $event['email'] }}</div>
                                        @else
                                            <span class="text-muted">
                                                athlete_id {{ $event['athlete_id'] ?? '-' }}
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        <strong>
                                            {{ $event['object_type'] }}/{{ $event['aspect_type'] }}
                                        </strong>
                                        @if (!empty($event['object_id']))
                                            <div class="text-muted small">
                                                object_id {{ $event['object_id'] }}
                                            </div>
                                        @endif
                                    </td>

                                    <td>
                                        @php
                                            $statusClass = match ($event['status']) {
                                                'processed' => 'text-success',
                                                'failed' => 'text-danger',
                                                'ignored' => 'text-muted',
                                                default => 'text-warning',
                                            };
                                        @endphp

                                        <span class="{{ $statusClass }}">
                                            {{ $event['status'] }}
                                        </span>
                                    </td>

                                    <td>
                                        @if (!empty($event['error']))
                                            <span class="text-danger small">
                                                {{ $event['error'] }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card card-lift h-100">
                <div class="card-body p-3 p-md-4">
                    <h2 class="h5 mb-3">
                        <i class="fa-light fa-sharp fa-users me-1"></i>
                        {{ __('admin.recentUsers') }}
                    </h2>

                    @if (!$recentUsers)
                        <p class="text-muted mb-0">{{ __('admin.noUsers') }}</p>
                    @else
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ __('admin.user') }}</th>
                                        <th>{{ __('settings.language') }}</th>
                                        <th>2FA</th>
                                        <th>{{ __('common.created') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentUsers as $recentUser)
                                        <tr>
                                            <td>
                                                <strong>{{ $recentUser['name'] ?: '-' }}</strong>
                                                <div class="text-muted small">{{ $recentUser['email'] }}</div>
                                            </td>
                                            <td>{{ strtoupper((string) ($recentUser['language'] ?? '-')) }}</td>
                                            <td>
                                                @if ((int) ($recentUser['two_factor_enabled'] ?? 0) === 1)
                                                    {{ __('common.enabled') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if (!empty($recentUser['created_at']))
                                                    {{ \App\Support\RideWrench::formatDateTime($recentUser['created_at']) }}
                                                @else
                                                    {{ __('common.notSet') }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card card-lift h-100">
                <div class="card-body p-3 p-md-4">
                    <h2 class="h5 mb-3">
                        <i class="fa-light fa-sharp fa-bell me-1"></i>
                        {{ __('admin.recentAlerts') }}
                    </h2>

                    @if (!$recentAlerts)
                        <p class="text-muted mb-0">{{ __('admin.noAlerts') }}</p>
                    @else
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ __('admin.alert') }}</th>
                                        <th>{{ __('admin.user') }}</th>
                                        <th>{{ __('bikes.bikeName') }}</th>
                                        <th>{{ __('bikes.status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentAlerts as $alert)
                                        <tr>
                                            <td>
                                                <strong>{{ $alert['rule_name'] }}</strong>
                                                <div class="text-muted small">{{ $alert['due_reason'] }}</div>
                                            </td>
                                            <td>{{ $alert['email'] }}</td>
                                            <td>{{ $alert['bike_name'] }}</td>
                                            <td>{{ __('alertStatus.' . $alert['status']) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
