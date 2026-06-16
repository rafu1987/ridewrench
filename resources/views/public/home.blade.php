@section('title', __('landing.metaTitle'))

<x-app-layout>
    <section class="landing-hero mb-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <div class="mb-3">
                    <span class="badge text-bg-light">
                        <i class="fa-brands fa-strava me-1"></i>
                        {{ __('landing.badge') }}
                    </span>
                </div>

                <h1 class="landing-title mb-3">
                    {{ __('landing.title') }}
                </h1>

                <p class="landing-lead mb-4">
                    {{ __('landing.subtitle') }}
                </p>

                <div class="d-flex gap-2 flex-wrap align-items-center">
                    @auth
                        <a class="btn btn-primary btn-lg" href="{{ url('/dashboard') }}">
                            <i class="fa-light fa-sharp fa-gauge-high me-1"></i>
                            {{ __('landing.openApp') }}
                        </a>
                    @else
                        <a class="btn btn-primary btn-lg" href="{{ route('register') }}">
                            <i class="fa-light fa-sharp fa-user-plus me-1"></i>
                            {{ __('landing.getStarted') }}
                        </a>

                        <a class="btn btn-outline-light btn-lg" href="{{ route('login') }}">
                            <i class="fa-light fa-sharp fa-right-to-bracket me-1"></i>
                            {{ __('landing.login') }}
                        </a>
                    @endauth
                </div>
            </div>

            <div class="col-lg-5">
                <div class="landing-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="text-muted small">
                                {{ __('landing.previewBike') }}
                            </div>

                            <h2 class="h4 mb-0">
                                Colnago V5Rs
                            </h2>
                        </div>

                        <span class="badge badge-road">
                            {{ __('bikeType.road') }}
                        </span>
                    </div>

                    <div class="metric-box mb-3">
                        <div class="text-muted small">
                            {{ __('landing.previewWax') }}
                        </div>

                        <div class="fw-bold fs-4">
                            101,3 {{ __('maintenance.kmRemaining') }}
                        </div>
                    </div>

                    <div class="status-box status-box-ok">
                        <strong>
                            <i class="fa-light fa-sharp fa-circle-check me-1"></i>
                            {{ __('maintenance.nextAlert') }}
                        </strong>
                        <br>
                        {{ __('landing.previewText') }}
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card card-lift h-100">
                <div class="card-body p-4">
                    <div class="brand-mark brand-mark-md mb-3">
                        <i class="fa-brands fa-strava"></i>
                    </div>

                    <h2 class="h5">
                        {{ __('landing.featureSyncTitle') }}
                    </h2>

                    <p class="text-muted mb-0">
                        {{ __('landing.featureSyncText') }}
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-lift h-100">
                <div class="card-body p-4">
                    <div class="brand-mark brand-mark-md mb-3">
                        <i class="fa-light fa-sharp fa-screwdriver-wrench"></i>
                    </div>

                    <h2 class="h5">
                        {{ __('landing.featureRulesTitle') }}
                    </h2>

                    <p class="text-muted mb-0">
                        {{ __('landing.featureRulesText') }}
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-lift h-100">
                <div class="card-body p-4">
                    <div class="brand-mark brand-mark-md mb-3">
                        <i class="fa-light fa-sharp fa-bell"></i>
                    </div>

                    <h2 class="h5">
                        {{ __('landing.featureAlertsTitle') }}
                    </h2>

                    <p class="text-muted mb-0">
                        {{ __('landing.featureAlertsText') }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    @include('partials.bmc')
</x-app-layout>
