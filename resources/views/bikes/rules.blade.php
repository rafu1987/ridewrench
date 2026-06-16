@php
    use App\Support\RideWrench;
@endphp

@section('title', __('rules.pageTitle'))

<x-app-layout>
    <div class="page-hero mb-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div>
                <p class="text-white-50 mb-1">
                    <i class="fa-light fa-sharp fa-screwdriver-wrench me-1"></i>
                    {{ __('rules.kicker') }}
                </p>

                <h1 class="display-6 fw-bold mb-1">
                    {{ __('rules.titleForBike', [$bike->name]) }}
                </h1>

                <p class="mb-0 text-white-50">
                    {{ __('rules.subtitle') }}
                </p>
            </div>

            <a class="btn btn-light" href="{{ url('/bikes') }}">
                <i class="fa-light fa-sharp fa-arrow-left me-1"></i>
                {{ __('rules.backToBikes') }}
            </a>
        </div>
    </div>

    <div class="card card-lift mb-4">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                <div>
                    <h2 class="h4 mb-2">
                        <i class="fa-light fa-sharp fa-circle-plus me-1"></i>
                        {{ __('rules.addRule') }}
                    </h2>

                    <p class="text-muted mb-0">
                        {{ __('rules.startDateHelp') }}
                    </p>
                </div>

                <span class="badge badge-{{ $bike->type ?: 'other' }}">
                    {{ __('bikeType.' . ($bike->type ?: 'other')) }}
                </span>
            </div>

            <form method="post" action="{{ url('/bikes/' . $bike->id . '/rules') }}">
                @csrf

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label for="rule-template-{{ $bikeId }}" class="form-label small text-muted">
                            {{ __('rules.template') }}
                        </label>

                        <select
                            id="rule-template-{{ $bikeId }}"
                            class="form-select"
                            name="template"
                            data-rule-template-select
                        >
                            <option value="">{{ __('rules.templateCustom') }}</option>

                            @foreach ($ruleTemplates ?? [] as $templateKey => $template)
                                <option
                                    value="{{ $templateKey }}"
                                    data-name="{{ __($template['name_key']) }}"
                                    data-rule-kind="{{ $template['rule_kind'] }}"
                                    data-distance-km="{{ $template['distance_km'] !== null ? $template['distance_km'] : '' }}"
                                    data-interval-days="{{ $template['interval_days'] !== null ? $template['interval_days'] : '' }}"
                                    data-email-enabled="{{ !empty($template['email_enabled']) ? '1' : '0' }}"
                                >
                                    {{ __($template['name_key']) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <div class="p-3 rounded bg-light h-100">
                            <div class="fw-semibold mb-1">
                                <i class="fa-sharp fa-light fa-lightbulb me-1"></i>
                                {{ __('rules.template') }}
                            </div>

                            <div class="text-muted small">
                                {{ __('rules.templateHelp') }}
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="rule-name-{{ $bikeId }}" class="form-label small text-muted">
                            {{ __('rules.ruleName') }}
                        </label>

                        <input
                            id="rule-name-{{ $bikeId }}"
                            class="form-control"
                            name="name"
                            placeholder="{{ __('rules.placeholderName') }}"
                            data-rule-name
                            required
                        >
                    </div>

                    <div class="col-md-2">
                        <label for="rule-kind-{{ $bikeId }}" class="form-label small text-muted">
                            {{ __('rules.type') }}
                        </label>

                        <select
                            id="rule-kind-{{ $bikeId }}"
                            class="form-select"
                            name="rule_kind"
                            data-rule-kind
                        >
                            <option value="distance">{{ __('ruleKind.distance') }}</option>
                            <option value="time">{{ __('ruleKind.time') }}</option>
                            <option value="combined">{{ __('ruleKind.combined') }}</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="rule-distance-{{ $bikeId }}" class="form-label small text-muted">
                            {{ __('metric.distance') }}
                        </label>

                        <div class="input-group">
                            <input
                                id="rule-distance-{{ $bikeId }}"
                                class="form-control"
                                name="distance_km"
                                type="number"
                                step="0.1"
                                placeholder="300"
                                data-rule-distance
                            >

                            <span class="input-group-text">{{ __('unit.km') }}</span>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label for="rule-interval-{{ $bikeId }}" class="form-label small text-muted">
                            {{ __('rules.interval') }}
                        </label>

                        <div class="input-group">
                            <input
                                id="rule-interval-{{ $bikeId }}"
                                class="form-control"
                                name="interval_days"
                                type="number"
                                placeholder="90"
                                data-rule-days
                            >

                            <span class="input-group-text">{{ __('unit.days') }}</span>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label for="rule-start-date-{{ $bikeId }}" class="form-label small text-muted">
                            {{ __('maintenance.lastDoneStart') }}
                        </label>

                        <input
                            id="rule-start-date-{{ $bikeId }}"
                            class="form-control"
                            name="start_date"
                            type="date"
                            title="{{ __('maintenance.lastDoneStart') }}"
                        >
                    </div>
                </div>

                <div class="row g-3 mt-1 align-items-end">
                    <div class="col-md-8">
                        <div class="p-3 rounded bg-light">
                            <div class="fw-semibold mb-1">
                                <i class="fa-sharp fa-light fa-lightbulb me-1"></i>
                                {{ __('rules.examplesTitle') }}
                            </div>

                            <div class="text-muted small">
                                {{ __('rules.examplesText') }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label for="rule-email-enabled-{{ $bikeId }}" class="form-label small text-muted">
                            {{ __('settings.email') }}
                        </label>

                        <div class="form-check form-check--status border rounded bg-light mb-0">
                            <input
                                class="form-check-input ms-0 me-2"
                                type="checkbox"
                                name="email_enabled"
                                id="rule-email-enabled-{{ $bikeId }}"
                                data-rule-email
                                checked
                            >

                            <label class="form-check-label" for="rule-email-enabled-{{ $bikeId }}">
                                {{ __('common.enabled') }}
                            </label>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <button class="btn btn-dark w-100">
                            <i class="fa-light fa-sharp fa-plus me-1"></i>
                            {{ __('rules.addRuleButton') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if ($ruleRows->isEmpty())
        <div class="card empty-state">
            <div class="card-body text-center py-5">
                <div class="display-5 mb-3">
                    <i class="fa-light fa-sharp fa-screwdriver-wrench"></i>
                </div>

                <h2 class="h4">
                    {{ __('rules.noRulesTitle') }}
                </h2>

                <p class="text-muted mb-0">
                    {{ __('rules.noRulesText') }}
                </p>
            </div>
        </div>
    @else
        <div class="row g-3">
            @foreach ($ruleRows as $rule)
                @php
                    $stats = $service->statsForRule($rule);

                    $distanceSinceDone = (float) ($stats['distance_km'] ?? 0);
                    $daysSinceDone = (int) ($stats['days'] ?? 0);

                    $distanceLimit = isset($rule->distance_km) ? (float) $rule->distance_km : 0;
                    $dayLimit = isset($rule->interval_days) ? (int) $rule->interval_days : 0;

                    $distanceRemaining = $distanceLimit > 0 ? max(0, $distanceLimit - $distanceSinceDone) : null;
                    $daysRemaining = $dayLimit > 0 ? max(0, $dayLimit - $daysSinceDone) : null;

                    $distanceDue = $distanceLimit > 0 && $distanceSinceDone >= $distanceLimit;
                    $daysDue = $dayLimit > 0 && $daysSinceDone >= $dayLimit;
                    $isDue = $distanceDue || $daysDue;

                    $ruleKind = $rule->rule_kind ?: 'combined';

                    $kindIcon = match ($ruleKind) {
                        'distance' => 'fa-route',
                        'time' => 'fa-calendar-days',
                        default => 'fa-layer-group',
                    };

                    $editModalId = 'edit-rule-modal-' . (int) $rule->id;
                @endphp

                <div class="col-lg-6">
                    <div class="card card-lift h-100 {{ $isDue ? 'border border-danger' : '' }}">
                        <div class="card-body p-3 p-md-4 d-flex flex-column">
                            <div class="d-md-flex justify-content-md-between align-items-md-start gap-3 mb-3">
                                <div>
                                    <h2 class="h5 mb-3">
                                        <i class="fa-light fa-sharp {{ $kindIcon }} me-1"></i>
                                        {{ $rule->name }}
                                    </h2>

                                    <div class="d-flex gap-2 flex-wrap">
                                        <span class="badge text-bg-dark">
                                            {{ __('ruleKind.' . $ruleKind) }}
                                        </span>

                                        @if ((int) $rule->email_enabled === 1)
                                            <span class="badge text-bg-success">
                                                <i class="fa-light fa-sharp fa-envelope me-1"></i>
                                                {{ __('settings.email') }}
                                            </span>
                                        @else
                                            <span class="badge text-bg-secondary">
                                                <i class="fa-light fa-sharp fa-envelope-open me-1"></i>
                                                {{ __('rules.noEmail') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="text-md-end mt-3 mt-md-0">
                                    <div class="text-muted small">
                                        {{ __('maintenance.lastDoneStart') }}
                                    </div>

                                    <div class="fw-semibold">
                                        {{ RideWrench::formatDate($stats['last_date'] ?? null) }}
                                    </div>
                                </div>
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
                                            @if ($distanceLimit > 0)
                                                {{ __('metric.ofDistance', [RideWrench::formatNumber($distanceLimit, 1)]) }}
                                            @else
                                                {{ __('metric.noLimit') }}
                                            @endif
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
                                            @if ($dayLimit > 0)
                                                {{ __('metric.ofDays', [(string) $dayLimit]) }}
                                            @else
                                                {{ __('metric.noLimit') }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

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

                            <div class="mt-auto">
                                <div class="border-top pt-3">
                                    <div class="d-grid d-md-flex gap-2 flex-wrap">
                                        <button
                                            class="btn btn-sm btn-outline-dark"
                                            type="button"
                                            data-bs-toggle="modal"
                                            data-bs-target="#{{ $editModalId }}"
                                        >
                                            <i class="fa-light fa-sharp fa-pen-to-square me-1"></i>
                                            {{ __('common.edit') }}
                                        </button>

                                        <form class="d-grid d-md-block" method="post" action="{{ url('/rules/' . $rule->id . '/done') }}">
                                            @csrf

                                            <button class="btn btn-sm btn-success">
                                                <i class="fa-light fa-sharp fa-check me-1"></i>
                                                {{ __('maintenance.doneToday') }}
                                            </button>
                                        </form>

                                        <form class="d-grid d-md-block" method="post" action="{{ url('/rules/' . $rule->id . '/reset') }}" onsubmit="return confirm(@js(__('confirm.resetRule')))">
                                            @csrf

                                            <button class="btn btn-sm btn-outline-warning">
                                                <i class="fa-light fa-sharp fa-rotate-left me-1"></i>
                                                {{ __('maintenance.reset') }}
                                            </button>
                                        </form>

                                        <form class="d-grid d-md-block" method="post" action="{{ url('/rules/' . $rule->id . '/delete') }}" onsubmit="return confirm(@js(__('confirm.deleteRule')))">
                                            @csrf

                                            <button class="btn btn-sm btn-outline-danger">
                                                <i class="fa-light fa-sharp fa-trash me-1"></i>
                                                {{ __('maintenance.delete') }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="{{ $editModalId }}" tabindex="-1" aria-labelledby="{{ $editModalId }}-label" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <form method="post" action="{{ url('/rules/' . $rule->id . '/edit') }}">
                                    @csrf

                                    <div class="modal-header">
                                        <h2 class="modal-title h5 mb-0" id="{{ $editModalId }}-label">
                                            <i class="fa-light fa-sharp fa-pen-to-square me-1"></i>
                                            {{ __('rules.editRule') }}
                                        </h2>

                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('common.close') }}"></button>
                                    </div>

                                    <div class="modal-body">
                                        <div class="row g-3 mb-3">
                                            <div class="col-md-5">
                                                <label for="rule-template-edit-{{ $rule->id }}" class="form-label small text-muted">
                                                    {{ __('rules.template') }}
                                                </label>

                                                <select
                                                    id="rule-template-edit-{{ $rule->id }}"
                                                    class="form-select"
                                                    name="template"
                                                    data-rule-template-select
                                                >
                                                    <option value="">{{ __('rules.templateCustom') }}</option>

                                                    @foreach ($ruleTemplates ?? [] as $templateKey => $template)
                                                        <option
                                                            value="{{ $templateKey }}"
                                                            data-name="{{ __($template['name_key']) }}"
                                                            data-rule-kind="{{ $template['rule_kind'] }}"
                                                            data-distance-km="{{ $template['distance_km'] !== null ? $template['distance_km'] : '' }}"
                                                            data-interval-days="{{ $template['interval_days'] !== null ? $template['interval_days'] : '' }}"
                                                            data-email-enabled="{{ !empty($template['email_enabled']) ? '1' : '0' }}"
                                                        >
                                                            {{ __($template['name_key']) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="p-3 rounded bg-light h-100">
                                                    <div class="fw-semibold mb-1">
                                                        <i class="fa-sharp fa-light fa-lightbulb me-1"></i>
                                                        {{ __('rules.template') }}
                                                    </div>

                                                    <div class="text-muted small">
                                                        {{ __('rules.templateHelp') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="rule-name-edit-{{ $rule->id }}" class="form-label small text-muted">
                                                    {{ __('rules.ruleName') }}
                                                </label>

                                                <input
                                                    id="rule-name-edit-{{ $rule->id }}"
                                                    class="form-control"
                                                    name="name"
                                                    value="{{ $rule->name }}"
                                                    data-rule-name
                                                    required
                                                >
                                            </div>

                                            <div class="col-md-6">
                                                <label for="rule-kind-edit-{{ $rule->id }}" class="form-label small text-muted">
                                                    {{ __('rules.type') }}
                                                </label>

                                                <select
                                                    id="rule-kind-edit-{{ $rule->id }}"
                                                    class="form-select"
                                                    name="rule_kind"
                                                    data-rule-kind
                                                >
                                                    <option value="distance" @selected($ruleKind === 'distance')>
                                                        {{ __('ruleKind.distance') }}
                                                    </option>
                                                    <option value="time" @selected($ruleKind === 'time')>
                                                        {{ __('ruleKind.time') }}
                                                    </option>
                                                    <option value="combined" @selected($ruleKind === 'combined')>
                                                        {{ __('ruleKind.combined') }}
                                                    </option>
                                                </select>
                                            </div>

                                            <div class="col-md-6">
                                                <label for="rule-distance-edit-{{ $rule->id }}" class="form-label small text-muted">
                                                    {{ __('metric.distance') }}
                                                </label>

                                                <div class="input-group">
                                                    <input
                                                        id="rule-distance-edit-{{ $rule->id }}"
                                                        class="form-control"
                                                        name="distance_km"
                                                        type="number"
                                                        step="0.1"
                                                        value="{{ $distanceLimit > 0 ? $distanceLimit : '' }}"
                                                        data-rule-distance
                                                    >

                                                    <span class="input-group-text">{{ __('unit.km') }}</span>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <label for="rule-interval-edit-{{ $rule->id }}" class="form-label small text-muted">
                                                    {{ __('rules.interval') }}
                                                </label>

                                                <div class="input-group">
                                                    <input
                                                        id="rule-interval-edit-{{ $rule->id }}"
                                                        class="form-control"
                                                        name="interval_days"
                                                        type="number"
                                                        value="{{ $dayLimit > 0 ? $dayLimit : '' }}"
                                                        data-rule-days
                                                    >

                                                    <span class="input-group-text">{{ __('unit.days') }}</span>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <label for="rule-start-date-edit-{{ $rule->id }}" class="form-label small text-muted">
                                                    {{ __('maintenance.replaceLastDoneStart') }}
                                                </label>

                                                <input
                                                    id="rule-start-date-edit-{{ $rule->id }}"
                                                    class="form-control"
                                                    name="start_date"
                                                    type="date"
                                                    value="{{ !empty($stats['last_date']) ? date('Y-m-d', strtotime($stats['last_date'])) : '' }}"
                                                >

                                                <div class="form-text">
                                                    {{ __('rules.startDateEditHelp') }}
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-check form-check--status border rounded bg-light mb-0">
                                                    <input
                                                        class="form-check-input ms-0 me-2"
                                                        type="checkbox"
                                                        name="email_enabled"
                                                        id="rule-email-edit-{{ $rule->id }}"
                                                        data-rule-email
                                                        @checked((int) $rule->email_enabled === 1)
                                                    >

                                                    <label class="form-check-label" for="rule-email-edit-{{ $rule->id }}">
                                                        {{ __('settings.email') }} — {{ __('settings.emailRemindersEnabled') }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                            {{ __('common.cancel') }}
                                        </button>

                                        <button class="btn btn-dark">
                                            <i class="fa-light fa-sharp fa-floppy-disk me-1"></i>
                                            {{ __('common.save') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-app-layout>