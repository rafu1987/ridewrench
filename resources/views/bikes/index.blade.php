@php
    use App\Support\RideWrench;
@endphp

@section('title', __('bikes.title'))

<x-app-layout>
    <div class="page-hero mb-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div>
                <p class="text-white-50 mb-1">
                    <i class="fa-light fa-sharp fa-bicycle me-1"></i>
                    {{ __('bikes.kicker') }}
                </p>

                <h1 class="display-6 fw-bold mb-1">
                    {{ __('bikes.title') }}
                </h1>

                <p class="mb-0 text-white-50">
                    {{ __('bikes.subtitle') }}
                </p>
            </div>

            <a class="btn btn-light" href="{{ url('/dashboard') }}">
                <i class="fa-light fa-sharp fa-gauge-high me-1"></i>
                {{ __('alerts.backToDashboard') }}
            </a>
        </div>
    </div>

    @if ($bikeRows->isEmpty())
        <div class="card empty-state">
            <div class="card-body text-center py-5">
                <div class="display-5 mb-3">
                    <i class="fa-light fa-sharp fa-bicycle"></i>
                </div>

                <h2 class="h4">
                    {{ __('bikes.noBikesTitle') }}
                </h2>

                <p class="text-muted mb-4">
                    {{ __('bikes.noBikesText') }}
                </p>

                <a class="btn btn-light" href="{{ url('/dashboard') }}">
                    <i class="fa-light fa-sharp fa-gauge-high me-1"></i>
                    {{ __('alerts.backToDashboard') }}
                </a>
            </div>
        </div>
    @else
        <div class="row g-3">
            @foreach ($bikeRows as $bike)
                @php
                    $bikeType = $bike->type ?: 'other';
                    $isActive = (bool) $bike->active;
                    $km = round((float) $bike->km, 1);
                @endphp

                <div class="col-lg-6">
                    <div class="card card-lift h-100 {{ $isActive ? '' : 'opacity-50' }}">
                        <div class="card-body p-3 p-md-4">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="brand-mark brand-mark-lg flex-shrink-0">
                                        <i class="{{ RideWrench::bikeTypeIconClass($bikeType) }}"></i>
                                    </div>

                                    <div>
                                        <h2 class="h5 mb-2">
                                            {{ $bike->name }}
                                        </h2>

                                        <div class="d-flex gap-2 flex-wrap align-items-center">
                                            <span class="badge badge-{{ $bikeType }}">
                                                {{ __('bikeType.' . $bikeType) }}
                                            </span>

                                            @if ($isActive)
                                                <span class="badge text-bg-success">
                                                    <i class="fa-light fa-sharp fa-check me-1"></i>
                                                    {{ __('common.active') }}
                                                </span>
                                            @else
                                                <span class="badge text-bg-secondary">
                                                    <i class="fa-light fa-sharp fa-pause me-1"></i>
                                                    {{ __('common.inactive') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="metric-box text-end">
                                    <div class="fw-bold fs-5">
                                        {{ RideWrench::formatNumber($km, 1) }}
                                    </div>

                                    <div class="text-muted small">
                                        {{ __('bikes.kmTotal') }}
                                    </div>
                                </div>
                            </div>

                            <form method="post" action="{{ url('/bikes') }}" class="row g-2 align-items-end">
                                @csrf

                                <input type="hidden" name="bike_id" value="{{ $bike->id }}">
                                <input type="hidden" name="redirect" value="{{ request()->getRequestUri() }}">

                                <div class="col-md-5">
                                    <label for="bike-name-{{ $bike->id }}" class="form-label small text-muted">
                                        {{ __('bikes.bikeName') }}
                                    </label>

                                    <input
                                        id="bike-name-{{ $bike->id }}"
                                        class="form-control"
                                        name="name"
                                        value="{{ $bike->name }}"
                                    >
                                </div>

                                <div class="col-md-3">
                                    <label for="bike-type-{{ $bike->id }}" class="form-label small text-muted">
                                        {{ __('bikes.type') }}
                                    </label>

                                    <select
                                        id="bike-type-{{ $bike->id }}"
                                        class="form-select"
                                        name="type"
                                    >
                                        <option value="road" @selected($bikeType === 'road')>
                                            {{ __('bikeType.road') }}
                                        </option>
                                        <option value="gravel" @selected($bikeType === 'gravel')>
                                            {{ __('bikeType.gravel') }}
                                        </option>
                                        <option value="mtb" @selected($bikeType === 'mtb')>
                                            {{ __('bikeType.mtb') }}
                                        </option>
                                        <option value="indoor" @selected($bikeType === 'indoor')>
                                            {{ __('bikeType.indoor') }}
                                        </option>
                                        <option value="other" @selected($bikeType === 'other')>
                                            {{ __('bikeType.other') }}
                                        </option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="bike-active-{{ $bike->id }}" class="form-label small text-muted">
                                        {{ __('bikes.status') }}
                                    </label>

                                    <div class="form-check form-check--status border rounded bg-light mb-0">
                                        <input
                                            class="form-check-input ms-0 me-2"
                                            type="checkbox"
                                            name="active"
                                            id="bike-active-{{ $bike->id }}"
                                            @checked($isActive)
                                        >

                                        <label class="form-check-label" for="bike-active-{{ $bike->id }}">
                                            {{ __('bikes.activeBike') }}
                                        </label>
                                    </div>
                                </div>

                                <div class="form-text text-muted">
                                    {{ __('bikes.activeHelp') }}
                                </div>

                                <div class="col-12">
                                    <div class="d-grid d-sm-flex gap-2 flex-wrap mt-2">
                                        <a class="btn btn-outline-dark" href="{{ url('/bikes/' . $bike->id) }}">
                                            <i class="fa-light fa-sharp fa-screwdriver-wrench me-1"></i>
                                            {{ __('bikes.manageRules') }}
                                        </a>

                                        <button class="btn btn-dark">
                                            <i class="fa-light fa-sharp fa-floppy-disk me-1"></i>
                                            {{ __('bikes.saveBike') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-app-layout>
