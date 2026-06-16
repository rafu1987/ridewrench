@php
    $account = $account ?? null;
    $redirectPath = $redirectPath ?? '/settings';
    $currentUser = auth()->user();
@endphp

<div class="card card-lift mb-3">
    <div class="card-body p-3 p-md-4">
        <div class="row g-3 align-items-start">
            <div class="col-lg-6">
                <h2 class="h5 mb-3">
                    <i class="fa-brands fa-strava me-1"></i>
                    {{ __('settings.accountIntegrations') }}
                </h2>

                <p class="mb-2">
                    <strong>{{ __('settings.email') }}:</strong>
                    {{ $user->email }}
                </p>

                <p class="mb-3">
                    <strong>{{ __('settings.strava') }}:</strong>
                    @if ($account)
                        {{ __('settings.connectedAs', [$account->athlete_name ?? 'Strava']) }}
                    @else
                        {{ __('settings.notConnected') }}
                    @endif
                </p>

                @if (!$account)
                    <p class="text-muted small mb-lg-0">
                        {{ __('strava.statusNotConnected') }}
                    </p>
                @else
                    <dl class="row small mb-0">
                        <dt class="col-sm-5 text-muted">{{ __('strava.lastSync') }}</dt>
                        <dd class="col-sm-7 mb-2">
                            @if (!empty($account->last_synced_at))
                                {{ \App\Support\RideWrench::formatDateTime($account->last_synced_at) }}
                            @else
                                {{ __('common.notSet') }}
                            @endif
                        </dd>

                        <dt class="col-sm-5 text-muted">{{ __('strava.lastFullSync') }}</dt>
                        <dd class="col-sm-7 mb-2">
                            @if (!empty($account->last_full_synced_at))
                                {{ \App\Support\RideWrench::formatDateTime($account->last_full_synced_at) }}
                            @else
                                {{ __('common.notSet') }}
                            @endif
                        </dd>

                        @if ($currentUser?->is_admin)
                            <dt class="col-sm-5 text-muted">{{ __('strava.tokenExpires') }}</dt>
                            <dd class="col-sm-7 mb-0">
                                @if (!empty($account->expires_at))
                                    {{ \App\Support\RideWrench::formatDateTime(date('c', (int) $account->expires_at)) }}
                                @else
                                    {{ __('common.notSet') }}
                                @endif
                            </dd>
                        @endif
                    </dl>
                @endif
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-lg-12">
                <div class="d-md-flex gap-2 flex-wrap">
                    @include('partials.strava-connection-button', [
                        'account' => $account,
                        'redirectPath' => $redirectPath,
                    ])

                    @if ($account)
                        <form method="post" action="{{ url('/sync/full') }}">
                            @csrf
                            <input type="hidden" name="redirect" value="{{ $redirectPath }}">

                            <div class="d-grid mb-2 mb-md-0">
                                <button
                                    class="btn btn-outline-dark"
                                    onclick="return confirm(@js(__('confirm.fullResync')))"
                                >
                                    <i class="fa-light fa-sharp fa-arrows-rotate me-1"></i>
                                    {{ __('dashboard.fullResync') }}
                                </button>
                            </div>
                        </form>
                    @endif

                    @if ($currentUser?->is_admin)
                        <form method="post" action="{{ url('/settings/test-email') }}">
                            @csrf

                            <div class="d-grid mb-2 mb-md-0">
                                <button class="btn btn-outline-dark">
                                    <i class="fa-light fa-sharp fa-envelope me-1"></i>
                                    {{ __('settings.testEmail') }}
                                </button>
                            </div>
                        </form>
                    @endif

                    @if ($currentUser?->is_admin)
                        <form method="post" action="{{ url('/settings/test-due-email') }}">
                            @csrf

                            <div class="d-grid mb-2 mb-md-0">
                                <button class="btn btn-outline-dark">
                                    <i class="fa-light fa-sharp fa-screwdriver-wrench me-1"></i>
                                    {{ __('settings.sendTestDueEmail') }}
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-10">
                <p class="text-muted small mt-3 mb-lg-0">
                    <i class="fa-light fa-sharp fa-circle-info me-1"></i>
                    {{ __('strava.disconnectHelp') }}
                </p>

                <p class="text-muted small mt-2 mb-0">
                    <i class="fa-light fa-sharp fa-circle-info me-1"></i>
                    {{ __('strava.fullResyncHelp') }}
                </p>
            </div>
        </div>

        @if ($account && !empty($account->last_sync_error))
            <div class="alert alert-danger small mt-3 mb-0">
                <strong>{{ __('strava.lastSyncError') }}:</strong><br>
                {{ $account->last_sync_error }}
            </div>
        @endif
    </div>
</div>
