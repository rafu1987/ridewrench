@section('title', __('auth.forgotPasswordTitle'))

<x-app-layout>
    <div class="card card-lift mx-auto" style="max-width:520px">
        <div class="card-body p-3 p-md-4">
            <div class="text-center mb-4">
                <div class="brand-mark mx-auto mb-3">
                    <i class="fa-light fa-sharp fa-key"></i>
                </div>

                <h1 class="h3 fw-bold">
                    {{ __('auth.forgotPasswordTitle') }}
                </h1>

                <p class="text-muted mb-0">
                    {{ __('auth.forgotPasswordSubtitle') }}
                </p>
            </div>

            <form method="post" action="{{ route('password.email') }}">
                @csrf

                <label for="forgot-password-email" class="form-label">
                    {{ __('auth.emailLabel') }}
                </label>

                <input
                    id="forgot-password-email"
                    class="form-control mb-3 @error('email') is-invalid @enderror"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                >

                @error('email')
                    <div class="invalid-feedback d-block mb-3">
                        {{ $message }}
                    </div>
                @enderror

                <button class="btn btn-dark w-100">
                    {{ __('auth.sendResetLink') }}
                </button>
            </form>

            <p class="text-center mt-3 mb-0">
                <a href="{{ route('login') }}">
                    {{ __('auth.backToLogin') }}
                </a>
            </p>
        </div>
    </div>
</x-app-layout>
