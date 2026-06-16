@section('title', __('auth.loginTitle'))

<x-app-layout>
    <div class="card card-lift mx-auto" style="max-width:520px">
        <div class="card-body p-3 p-md-4">
            <div class="text-center mb-4">
                <div class="brand-mark mx-auto mb-3">
                    <i class="icon-ridewrench-road"></i>
                </div>

                <h1 class="h3 fw-bold">
                    {{ __('auth.loginTitle') }}
                </h1>

                <p class="text-muted mb-0">
                    {{ __('auth.loginSubtitle') }}
                </p>
            </div>

            <form method="post" action="{{ route('login') }}">
                @csrf

                <label for="login-email" class="form-label">
                    {{ __('auth.emailLabel') }}
                </label>

                <input
                    id="login-email"
                    autocomplete="username"
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

                <label for="login-password" class="form-label">
                    {{ __('auth.passwordLabel') }}
                </label>

                <input
                    id="login-password"
                    autocomplete="current-password"
                    class="form-control mb-3 @error('password') is-invalid @enderror"
                    type="password"
                    name="password"
                    required
                >

                @error('password')
                    <div class="invalid-feedback d-block mb-3">
                        {{ $message }}
                    </div>
                @enderror

                <div class="d-flex justify-content-between align-items-center mb-3 gap-3">
                    <div class="form-check mb-0">
                        <input
                            id="login-remember"
                            class="form-check-input"
                            type="checkbox"
                            name="remember"
                            value="1"
                        >

                        <label class="form-check-label" for="login-remember">
                            {{ __('auth.stayLoggedIn') }}
                        </label>
                    </div>

                    <a class="small" href="{{ route('password.request') }}">
                        {{ __('auth.forgotPassword') }}
                    </a>
                </div>

                <button class="btn btn-dark w-100">
                    {{ __('auth.loginButton') }}
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
