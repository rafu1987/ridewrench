@php
    use App\Support\RideWrench;
@endphp

@section('title', __('alerts.title'))

<x-app-layout>
    <div class="page-hero mb-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div>
                <p class="text-white-50 mb-1">
                    <i class="fa-light fa-sharp fa-bell me-1"></i>
                    {{ __('alerts.kicker') }}
                </p>

                <h1 class="display-6 fw-bold mb-1">
                    {{ __('alerts.title') }}
                </h1>

                <p class="mb-0 text-white-50">
                    {{ __('alerts.subtitle') }}
                </p>
            </div>

            <a class="btn btn-light" href="{{ url('/dashboard') }}">
                <i class="fa-light fa-sharp fa-gauge-high me-1"></i>
                {{ __('alerts.backToDashboard') }}
            </a>
        </div>
    </div>

    @if ($alertRows->isEmpty())
        <div class="card empty-state">
            <div class="card-body text-center py-5">
                <div class="display-5 text-success mb-3">
                    <i class="fa-light fa-sharp fa-circle-check"></i>
                </div>

                <h2 class="h4">
                    {{ __('alerts.emptyTitle') }}
                </h2>

                <p class="text-muted mb-0">
                    {{ __('alerts.emptyText') }}
                </p>
            </div>
        </div>
    @else
        <div class="row g-3">
            @foreach ($alertRows as $alert)
                <div class="col-md-6 col-xl-4">
                    <div class="card card-lift h-100 {{ $alert->status === 'open' ? 'border border-danger' : '' }}">
                        <div class="card-body">
                            <h2 class="h5 mb-1">
                                {{ $alert->rule_name }}
                            </h2>

                            <p class="mb-1">
                                <strong>{{ $alert->bike_name }}</strong>
                            </p>

                            <p class="mb-1">
                                {{ $alert->due_reason }}
                            </p>

                            <span class="badge {{ $alert->status === 'open' ? 'bg-danger' : 'bg-secondary' }}">
                                {{ __('alertStatus.' . $alert->status) }}
                            </span>

                            @if (!empty($alert->created_at))
                                <p class="text-muted small mb-0 mt-2">
                                    {{ __('common.created') }}:
                                    {{ RideWrench::formatDateTime($alert->created_at) }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-app-layout>