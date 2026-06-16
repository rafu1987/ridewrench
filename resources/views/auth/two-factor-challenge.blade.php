@section('title', __('auth.twoFactorTitle'))

<x-app-layout>
    <div class="card card-lift mx-auto" style="max-width:520px">
        <div class="card-body p-3 p-md-4">
            <div class="text-center mb-4">
                <div class="brand-mark mx-auto mb-3">
                    <i class="fa-light fa-sharp fa-shield-keyhole"></i>
                </div>

                <h1 class="h3 fw-bold">
                    {{ __('auth.twoFactorTitle') }}
                </h1>

                <p class="text-muted mb-0">
                    {{ __('auth.twoFactorSubtitle') }}
                </p>
            </div>

            <form method="post" action="{{ route('two-factor.login.store') }}">
                @csrf

                <label for="two-factor-code" class="form-label">
                    {{ __('auth.twoFactorCode') }}
                </label>

                <input
                    id="two-factor-code"
                    class="form-control mb-3 @error('code') is-invalid @enderror"
                    type="text"
                    name="code"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    autofocus
                >

                @error('code')
                    <div class="invalid-feedback d-block mb-3">
                        {{ $message }}
                    </div>
                @enderror

                <div class="text-center text-muted small my-3">
                    {{ __('auth.twoFactorOrRecovery') }}
                </div>

                <label for="two-factor-recovery-code" class="form-label">
                    {{ __('auth.twoFactorRecoveryCode') }}
                </label>

                <input
                    id="two-factor-recovery-code"
                    class="form-control mb-3 @error('recovery_code') is-invalid @enderror"
                    type="text"
                    name="recovery_code"
                    autocomplete="one-time-code"
                >

                @error('recovery_code')
                    <div class="invalid-feedback d-block mb-3">
                        {{ $message }}
                    </div>
                @enderror

                <button class="btn btn-dark w-100">
                    <i class="fa-light fa-sharp fa-shield-check me-1"></i>
                    {{ __('auth.twoFactorLoginButton') }}
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
