@section('title', __('settings.title'))

<x-app-layout>
    <div class="page-hero mb-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div>
                <p class="text-white-50 mb-1">
                    <i class="fa-light fa-sharp fa-gear me-1"></i>
                    {{ __('settings.accountIntegrations') }}
                </p>

                <h1 class="display-6 fw-bold mb-1">
                    {{ __('settings.title') }}
                </h1>

                <p class="mb-0 text-white-50">
                    {{ __('settings.subtitle') }}
                </p>
            </div>

            <a class="btn btn-light" href="{{ url('/dashboard') }}">
                <i class="fa-light fa-sharp fa-gauge-high me-1"></i>
                {{ __('alerts.backToDashboard') }}
            </a>
        </div>
    </div>

    @include('partials.strava-sync-status', [
        'user' => $user,
        'account' => $account,
        'redirectPath' => '/settings',
    ])

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card card-lift h-100">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="fa-light fa-sharp fa-user text-muted"></i>
                        <h2 class="h5 mb-0">{{ __('settings.profile') }}</h2>
                    </div>

                    <form method="post" action="{{ url('/settings/profile') }}" class="mb-4">
                        @csrf

                        <label for="settings-name" class="form-label">
                            {{ __('settings.name') }}
                        </label>

                        <input
                            id="settings-name"
                            class="form-control mb-3"
                            name="name"
                            value="{{ old('name', $user->name) }}"
                            required
                        >

                        <label for="settings-email" class="form-label">
                            {{ __('settings.loginEmail') }}
                        </label>

                        <input
                            id="settings-email"
                            class="form-control mb-3"
                            type="email"
                            name="email"
                            value="{{ old('email', $user->email) }}"
                            required
                        >

                        <label for="settings-profile-current-password" class="form-label">
                            {{ __('settings.currentPasswordConfirm') }}
                        </label>

                        <input
                            id="settings-profile-current-password"
                            class="form-control mb-2"
                            type="password"
                            name="current_password"
                            required
                        >

                        <p class="text-muted small mb-3">
                            {{ __('settings.profilePasswordHelp') }}
                        </p>

                        <div class="d-grid d-md-block">
                            <button class="btn btn-dark">
                                <i class="fa-light fa-sharp fa-floppy-disk me-1"></i>
                                {{ __('settings.saveProfile') }}
                            </button>
                        </div>
                    </form>

                    <hr class="my-4">

                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="fa-light fa-sharp fa-envelope-circle-check text-muted"></i>
                        <h2 class="h5 mb-0">{{ __('settings.emailReminders') }}</h2>
                    </div>

                    <form method="post" action="{{ url('/settings/email-reminders') }}" class="mb-4">
                        @csrf

                        <p class="text-muted small mb-3">
                            {{ __('settings.emailRemindersHelp') }}
                        </p>

                        <div class="form-check form-switch mb-3">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                role="switch"
                                id="settings-email-reminders"
                                name="email_reminders_enabled"
                                value="1"
                                @checked((bool) $user->email_reminders_enabled)
                            >

                            <label class="form-check-label" for="settings-email-reminders">
                                {{ __('settings.emailRemindersEnabled') }}
                            </label>
                        </div>

                        <div class="d-grid d-md-block">
                            <button class="btn btn-dark">
                                <i class="fa-light fa-sharp fa-floppy-disk me-1"></i>
                                {{ __('common.save') }}
                            </button>
                        </div>
                    </form>

                    <hr class="my-4">

                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="fa-light fa-sharp fa-language text-muted"></i>
                        <h2 class="h5 mb-0">{{ __('settings.language') }}</h2>
                    </div>

                    <form method="post" action="{{ url('/settings/language') }}">
                        @csrf

                        <label for="settings-language" class="form-label">
                            {{ __('settings.language') }}
                        </label>

                        <select id="settings-language" class="form-select mb-2" name="language">
                            @foreach ($languages as $languageKey => $languageConfig)
                                <option value="{{ $languageKey }}" @selected(app()->getLocale() === $languageKey)>
                                    {{ $languageConfig['native'] ?? strtoupper($languageKey) }}
                                </option>
                            @endforeach
                        </select>

                        <p class="text-muted small mb-3">
                            {{ __('settings.languageHelp') }}
                        </p>

                        <div class="d-grid d-md-block">
                            <button class="btn btn-dark">
                                <i class="fa-light fa-sharp fa-floppy-disk me-1"></i>
                                {{ __('settings.saveLanguage') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card card-lift h-100">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="fa-light fa-sharp fa-lock-keyhole text-muted"></i>
                        <h2 class="h5 mb-0">{{ __('settings.security') }}</h2>
                    </div>

                    <form method="post" action="{{ url('/settings/password') }}">
                        @csrf

                        <div class="row g-3">
                            <div class="col-12">
                                <label for="settings-current-password" class="form-label">
                                    {{ __('settings.currentPassword') }}
                                </label>

                                <input
                                    id="settings-current-password"
                                    class="form-control"
                                    type="password"
                                    name="current_password"
                                    required
                                >
                            </div>

                            <div class="col-md-6">
                                <label for="settings-new-password" class="form-label">
                                    {{ __('settings.newPassword') }}
                                </label>

                                <input
                                    id="settings-new-password"
                                    class="form-control"
                                    type="password"
                                    name="new_password"
                                    required
                                    minlength="8"
                                >
                            </div>

                            <div class="col-md-6">
                                <label for="settings-new-password-repeat" class="form-label">
                                    {{ __('settings.repeatNewPassword') }}
                                </label>

                                <input
                                    id="settings-new-password-repeat"
                                    class="form-control"
                                    type="password"
                                    name="new_password_repeat"
                                    required
                                    minlength="8"
                                >
                            </div>
                        </div>

                        <p class="text-muted small mt-2 mb-3">
                            {{ __('settings.passwordHelp') }}
                        </p>

                        <div class="d-grid d-md-block">
                            <button class="btn btn-dark">
                                <i class="fa-light fa-sharp fa-lock-keyhole me-1"></i>
                                {{ __('settings.changePassword') }}
                            </button>
                        </div>
                    </form>

                    <hr class="my-4">

                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="fa-light fa-sharp fa-shield-keyhole text-muted"></i>
                        <h2 class="h5 mb-0">{{ __('twoFactor.settingsTitle') }}</h2>
                    </div>

                    @if (!$twoFactorPending && !$twoFactorEnabled)
                        <p class="text-muted mb-3">
                            {{ __('twoFactor.disabledText') }}
                        </p>

                        <form method="post" action="{{ route('two-factor.enable') }}">
                            @csrf

                            <button class="btn btn-dark">
                                <i class="fa-light fa-sharp fa-shield-plus me-1"></i>
                                {{ __('twoFactor.enable') }}
                            </button>
                        </form>
                    @endif

                    @if ($twoFactorPending)
                        <div class="alert alert-warning">
                            <i class="fa-light fa-sharp fa-triangle-exclamation me-1"></i>
                            {{ __('twoFactor.confirmRequiredText') }}
                        </div>

                        @if ($twoFactorQrCode || $twoFactorSecretKey)
                            <div class="card bg-light border-0 mb-3">
                                <div class="card-body">
                                    <h3 class="h6 mb-2">
                                        {{ __('twoFactor.setupTitle') }}
                                    </h3>

                                    <p class="text-muted small mb-3">
                                        {{ __('twoFactor.setupHelp') }}
                                    </p>

                                    @if ($twoFactorQrCode)
                                        <div class="two-factor-qr mb-3 p-3 rounded bg-dark border d-inline-block">
                                            {!! $twoFactorQrCode !!}
                                        </div>
                                    @endif

                                    @if ($twoFactorSecretKey)
                                        <div class="mt-3">
                                            <div class="small fw-bold mb-2">
                                                {{ __('twoFactor.manualSetupTitle') }}
                                            </div>

                                            <div class="p-3 rounded bg-dark border">
                                                <code class="d-block text-break">{{ $twoFactorSecretKey }}</code>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <form method="post" action="{{ route('two-factor.confirm') }}" class="mb-3">
                            @csrf

                            <label for="two-factor-confirm-code" class="form-label">
                                {{ __('twoFactor.confirmCode') }}
                            </label>

                            <input
                                id="two-factor-confirm-code"
                                class="form-control mb-3 @error('code') is-invalid @enderror"
                                name="code"
                                inputmode="numeric"
                                autocomplete="one-time-code"
                                required
                            >

                            @error('code')
                                <div class="invalid-feedback d-block mb-3">
                                    {{ $message }}
                                </div>
                            @enderror

                            <div class="d-grid d-md-flex gap-2">
                                <button class="btn btn-dark">
                                    <i class="fa-light fa-sharp fa-shield-check me-1"></i>
                                    {{ __('twoFactor.confirm') }}
                                </button>

                                <button
                                    class="btn btn-outline-danger"
                                    type="submit"
                                    form="disable-two-factor-form"
                                >
                                    <i class="fa-light fa-sharp fa-xmark me-1"></i>
                                    {{ __('common.cancel') }}
                                </button>
                            </div>
                        </form>

                        <form
                            id="disable-two-factor-form"
                            method="post"
                            action="{{ route('two-factor.disable') }}"
                            class="d-none"
                        >
                            @csrf
                            @method('DELETE')
                        </form>
                    @endif

                    @if ($twoFactorEnabled)
                        <div class="alert alert-success">
                            <i class="fa-light fa-sharp fa-shield-check me-1"></i>
                            {{ __('twoFactor.enabledText') }}
                        </div>

                        @if (!empty($twoFactorRecoveryCodes))
                            <div class="card bg-light border-0 mb-3">
                                <div class="card-body">
                                    <h3 class="h6 mb-3">
                                        {{ __('twoFactor.recoveryCodesTitle') }}
                                    </h3>

                                    <p class="text-muted small">
                                        {{ __('twoFactor.recoveryCodesHelp') }}
                                    </p>

                                    <div class="recovery-codes p-3 rounded bg-dark border">
                                        @foreach ($twoFactorRecoveryCodes as $code)
                                            <code class="d-block">{{ $code }}</code>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="d-grid d-md-flex gap-2 flex-wrap">
                            <form method="post" action="{{ route('two-factor.regenerate-recovery-codes') }}">
                                @csrf

                                <button class="btn btn-outline-dark">
                                    <i class="fa-light fa-sharp fa-arrows-rotate me-1"></i>
                                    {{ __('twoFactor.regenerateRecoveryCodes') }}
                                </button>
                            </form>

                            <form
                                method="post"
                                action="{{ route('two-factor.disable') }}"
                                onsubmit="return confirm(@js(__('confirm.disableTwoFactor')))"
                            >
                                @csrf
                                @method('DELETE')

                                <button class="btn btn-outline-danger">
                                    <i class="fa-light fa-sharp fa-shield-xmark me-1"></i>
                                    {{ __('twoFactor.disable') }}
                                </button>
                            </form>
                        </div>
                    @endif

                    <hr class="my-4">

                    <div class="align-items-center d-flex gap-2 mb-3">
                        <i class="fa-light fa-sharp fa-file-export text-muted"></i>
                        <h2 class="h5 mb-0">
                            {{ __('settings.exportData') }}
                        </h2>
                    </div>

                    <p class="mb-3 small text-muted">
                        {{ __('settings.exportDataHelp') }}
                    </p>

                    <form method="post" action="{{ url('/settings/export-data') }}">
                        @csrf

                        <div class="d-grid d-md-block">
                            <button class="btn btn-outline-dark">
                                <i class="fa-light fa-sharp fa-download me-1"></i>
                                {{ __('settings.downloadExport') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-lift mt-3 border border-danger-subtle">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex align-items-start gap-3 flex-wrap">
                <div class="brand-mark brand-mark-md flex-shrink-0">
                    <i class="fa-light fa-sharp fa-triangle-exclamation"></i>
                </div>

                <div class="flex-grow-1">
                    <h2 class="h5 mb-2">{{ __('settings.dangerZone') }}</h2>

                    <p class="text-muted mb-3">
                        {{ __('settings.deleteAccountHelp') }}
                    </p>

                    <form
                        method="post"
                        action="{{ url('/settings/delete-account') }}"
                        onsubmit="return confirm(@js(__('confirm.deleteAccount')))"
                    >
                        @csrf

                        <div class="row g-3">
                            <div class="col-lg-4 col-xl-5">
                                <label for="delete-account-password" class="form-label">
                                    {{ __('settings.currentPassword') }}
                                </label>

                                <input
                                    id="delete-account-password"
                                    class="form-control"
                                    type="password"
                                    name="password"
                                    required
                                >
                            </div>

                            <div class="col-lg-4 col-xl-5">
                                <label for="delete-account-confirm" class="form-label">
                                    {{ __('settings.deleteAccountConfirmLabel') }}
                                </label>

                                <input
                                    id="delete-account-confirm"
                                    class="form-control"
                                    name="confirm"
                                    placeholder="DELETE"
                                    required
                                >

                                <div class="form-text text-muted d-block d-lg-none">
                                    {{ __('settings.deleteAccountConfirmHelp') }}
                                </div>
                            </div>

                            <div class="col-lg-4 col-xl-2 d-flex align-items-end">
                                <button class="btn btn-outline-danger w-100">
                                    <i class="fa-light fa-sharp fa-trash me-1"></i>
                                    {{ __('settings.deleteAccount') }}
                                </button>
                            </div>
                        </div>

                        <div class="row d-none d-lg-flex">
                            <div class="col-lg-4 offset-lg-4 col-xl-5 offset-xl-5">
                                <div class="form-text text-muted">
                                    {{ __('settings.deleteAccountConfirmHelp') }}
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
