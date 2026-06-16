@php
    use App\Support\RideWrench;
@endphp

@section('title', __('dashboard.title'))

<x-app-layout>
    <div class="page-hero mb-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div>
                <p class="text-white-50 mb-1">
                    <i class="fa-light fa-sharp fa-screwdriver-wrench me-1"></i>
                    {{ __('dashboard.kicker') }}
                </p>

                <h1 class="display-6 fw-bold mb-1">
                    {{ __('dashboard.title') }}
                </h1>

                <p class="mb-0 text-white-50">
                    {{ __('dashboard.subtitle') }}
                </p>
            </div>

            <div class="d-md-flex gap-2 flex-wrap">
                @if ($account)
                    <form method="post" action="{{ url('/sync') }}">
                        @csrf

                        <div class="d-grid d-md-block">
                            <button class="btn btn-primary">
                                <i class="fa-brands fa-strava me-1"></i>
                                {{ __('dashboard.syncStrava') }}
                            </button>
                        </div>
                    </form>
                @else
                    @include('partials.strava-connection-button', [
                        'redirectPath' => '/dashboard',
                        'account' => $account,
                    ])
                @endif
            </div>
        </div>
    </div>

    @if (!$account)
        <div class="alert alert-warning mb-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <strong>{{ __('dashboard.stravaNotConnected') }}</strong>

                    <div class="small">
                        {{ __('strava.statusNotConnected') }}
                    </div>
                </div>

                <div>
                    @include('partials.strava-connection-button', [
                        'redirectPath' => '/dashboard',
                        'account' => $account,
                    ])
                </div>
            </div>
        </div>
    @else
        <div class="card card-lift mb-4">
            <div class="card-body p-3">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                    <div>
                        <div class="small text-muted mb-1">
                            <i class="fa-brands fa-strava me-1"></i>
                            {{ __('strava.statusTitle') }}
                        </div>

                        <div class="fw-bold">
                            {{ __('settings.connectedAs', [$account->athlete_name ?? 'Strava']) }}
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-md-row gap-3 small">
                        <div>
                            <span class="text-muted">{{ __('strava.lastSync') }}:</span>
                            <strong>
                                {{ $account->last_synced_at ? RideWrench::formatDateTime($account->last_synced_at) : __('common.notSet') }}
                            </strong>
                        </div>

                        <div>
                            <span class="text-muted">{{ __('strava.lastFullSync') }}:</span>
                            <strong>
                                {{ $account->last_full_synced_at ? RideWrench::formatDateTime($account->last_full_synced_at) : __('common.notSet') }}
                            </strong>
                        </div>
                    </div>
                </div>

                @if (!empty($account->last_sync_error))
                    <div class="alert alert-danger small mt-3 mb-0">
                        <strong>{{ __('strava.lastSyncError') }}:</strong>
                        {{ $account->last_sync_error }}
                    </div>
                @endif
            </div>
        </div>
    @endif

    <div class="row g-3">
        @foreach ($ruleRows as $rule)
            @php
                $stats = $maintenanceService->statsForRule($rule);

                $distanceSinceDone = (float) ($stats['distance_km'] ?? 0);
                $daysSinceDone = (int) ($stats['days'] ?? 0);

                $distanceLimit = isset($rule->distance_km) ? (float) $rule->distance_km : 0;
                $dayLimit = isset($rule->interval_days) ? (int) $rule->interval_days : 0;

                $distanceRemaining = $distanceLimit > 0 ? max(0, $distanceLimit - $distanceSinceDone) : null;
                $daysRemaining = $dayLimit > 0 ? max(0, $dayLimit - $daysSinceDone) : null;

                $distanceDue = $distanceLimit > 0 && $distanceSinceDone >= $distanceLimit;
                $daysDue = $dayLimit > 0 && $daysSinceDone >= $dayLimit;
                $isDue = $distanceDue || $daysDue;
            @endphp

            <div class="col-md-6 col-xl-4">
                <div class="card card-lift h-100 {{ $isDue ? 'border border-danger' : '' }}">
                    <div class="card-body d-flex flex-column p-3 p-md-4">
                        <div class="d-flex justify-content-between gap-3">
                            <h5 class="mb-0">
                                {{ $rule->rule_name ?? $rule->name }}
                            </h5>

                            <span class="badge badge-{{ $rule->bike_type ?: 'other' }}">
                                {{ __('bikeType.' . ($rule->bike_type ?: 'other')) }}
                            </span>
                        </div>

                        <div class="mb-3 mt-3 text-muted d-flex align-items-center gap-2">
                            <div class="brand-mark flex-shrink-0">
                                <i class="{{ RideWrench::bikeTypeIconClass($rule->bike_type ?? null) }}"></i>
                            </div>

                            <strong class="text-dark">
                                {{ $rule->bike_name }}
                            </strong>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="metric-box">
                                    <div class="text-muted small">
                                        {{ __('metric.distance') }}
                                    </div>

                                    <div class="fw-bold">
                                        {{ RideWrench::formatNumber($distanceSinceDone, 1) }}
                                        {{ __('unit.km') }}
                                    </div>

                                    <div class="text-muted small">
                                        {{ __('metric.sinceLastDone') }}
                                    </div>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="metric-box">
                                    <div class="text-muted small">
                                        {{ __('metric.time') }}
                                    </div>

                                    <div class="fw-bold">
                                        {{ $daysSinceDone }}
                                        {{ __('unit.days') }}
                                    </div>

                                    <div class="text-muted small">
                                        {{ __('metric.sinceLastDone') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <p class="mb-3 text-muted small">
                            <i class="fa-sharp fa-light fa-calendar-days me-1"></i>
                            {{ __('maintenance.lastDoneStart') }}:
                            {{ RideWrench::formatDate($stats['last_date'] ?? null) }}
                        </p>

                        <div class="mt-auto">
                            <div class="status-box mb-3 {{ $isDue ? 'status-box-due' : 'status-box-ok' }}">
                                @if ($isDue)
                                    <strong>
                                        <i class="fa-light fa-sharp fa-triangle-exclamation me-1"></i>
                                        {{ __('maintenance.alertDueNow') }}
                                    </strong>

                                    @if ($distanceDue)
                                        <br>{{ __('maintenance.distanceLimitReached') }}
                                    @endif

                                    @if ($daysDue)
                                        <br>{{ __('maintenance.timeLimitReached') }}
                                    @endif
                                @else
                                    <strong>
                                        <i class="fa-light fa-sharp fa-circle-check me-1"></i>
                                        {{ __('maintenance.nextAlert') }}
                                    </strong>

                                    @if ($distanceRemaining !== null)
                                        <br>
                                        {{ RideWrench::formatNumber($distanceRemaining, 1) }}
                                        {{ __('maintenance.kmRemaining') }}
                                    @endif

                                    @if ($daysRemaining !== null)
                                        <br>
                                        {{ $daysRemaining }}
                                        {{ __('maintenance.daysRemaining') }}
                                    @endif
                                @endif
                            </div>

                            <form method="post" action="{{ url('/rules/' . $rule->id . '/done') }}">
                                @csrf

                                <button class="btn btn-sm btn-success w-100">
                                    <i class="fa-light fa-sharp fa-check me-1"></i>
                                    {{ __('maintenance.markDoneToday') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if ($ruleRows->isEmpty())
        <div class="card empty-state mt-3">
            <div class="card-body p-4 p-md-5">
                <div class="row g-4 align-items-center">
                    <div class="col-lg-5 text-center text-lg-start">
                        <div class="brand-mark mb-3 mx-auto mx-lg-0">
                            <i class="fa-light fa-sharp fa-bicycle"></i>
                        </div>

                        <h2 class="h4 fw-bold mb-3">
                            {{ __('dashboard.onboardingTitle') }}
                        </h2>

                        <p class="text-muted mb-4">
                            {{ __('dashboard.onboardingText') }}
                        </p>

                        <div class="d-flex gap-2 flex-wrap justify-content-center justify-content-lg-start">
                            @if (!$account)
                                @include('partials.strava-connection-button', [
                                    'redirectPath' => '/dashboard',
                                    'account' => $account,
                                ])
                            @else
                                <form method="post" action="{{ url('/sync/full') }}">
                                    @csrf
                                    <input type="hidden" name="redirect" value="{{ request()->getRequestUri() }}">

                                    <button class="btn btn-dark">
                                        <i class="fa-light fa-sharp fa-arrows-rotate me-1"></i>
                                        {{ __('dashboard.fullResync') }}
                                    </button>
                                </form>

                                <a class="btn btn-outline-dark" href="{{ url('/bikes') }}">
                                    <i class="fa-light fa-sharp fa-bicycle me-1"></i>
                                    {{ __('dashboard.goToBikes') }}
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="col-lg-7">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="metric-box h-100">
                                    <div class="d-flex gap-3">
                                        <div class="feature-icon flex-shrink-0">
                                            <i class="fa-brands fa-strava"></i>
                                        </div>

                                        <div>
                                            <h3 class="h6 mb-1">
                                                {{ __('dashboard.onboardingStepConnectTitle') }}
                                            </h3>
                                            <p class="text-muted small mb-0">
                                                {{ __('dashboard.onboardingStepConnectText') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="metric-box h-100">
                                    <div class="d-flex gap-3">
                                        <div class="feature-icon flex-shrink-0">
                                            <i class="fa-light fa-sharp fa-arrows-rotate"></i>
                                        </div>

                                        <div>
                                            <h3 class="h6 mb-1">
                                                {{ __('dashboard.onboardingStepSyncTitle') }}
                                            </h3>
                                            <p class="text-muted small mb-0">
                                                {{ __('dashboard.onboardingStepSyncText') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="metric-box h-100">
                                    <div class="d-flex gap-3">
                                        <div class="feature-icon flex-shrink-0">
                                            <i class="fa-light fa-sharp fa-bicycle"></i>
                                        </div>

                                        <div>
                                            <h3 class="h6 mb-1">
                                                {{ __('dashboard.onboardingStepBikesTitle') }}
                                            </h3>
                                            <p class="text-muted small mb-0">
                                                {{ __('dashboard.onboardingStepBikesText') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="metric-box h-100">
                                    <div class="d-flex gap-3">
                                        <div class="feature-icon flex-shrink-0">
                                            <i class="fa-light fa-sharp fa-list-check"></i>
                                        </div>

                                        <div>
                                            <h3 class="h6 mb-1">
                                                {{ __('dashboard.onboardingStepRulesTitle') }}
                                            </h3>
                                            <p class="text-muted small mb-0">
                                                {{ __('dashboard.onboardingStepRulesText') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if ($account)
                            <p class="text-muted small mt-3 mb-0">
                                <i class="fa-light fa-sharp fa-circle-info me-1"></i>
                                {{ __('dashboard.onboardingConnectedHint') }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-app-layout>
