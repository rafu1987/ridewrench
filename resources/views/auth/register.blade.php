@section('title', __('auth.registerTitle'))

<x-app-layout>
    <div class="card card-lift mx-auto" style="max-width:520px">
        <div class="card-body p-3 p-md-4">
            <div class="text-center mb-4">
                <div class="brand-mark mx-auto mb-3">
                    <i class="icon-ridewrench-road"></i>
                </div>

                <h1 class="h3 fw-bold">
                    {{ __('auth.registerTitle') }}
                </h1>

                <p class="text-muted mb-0">
                    {{ __('auth.registerSubtitle') }}
                </p>
            </div>

            <form method="post" action="{{ route('register') }}">
                @csrf

                <label for="register-name" class="form-label">
                    {{ __('auth.nameLabel') }}
                </label>

                <input
                    id="register-name"
                    class="form-control mb-3 @error('name') is-invalid @enderror"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    autofocus
                    autocomplete="name"
                >

                @error('name')
                    <div class="invalid-feedback d-block mb-3">
                        {{ $message }}
                    </div>
                @enderror

                <label for="register-email" class="form-label">
                    {{ __('auth.emailLabel') }}
                </label>

                <input
                    id="register-email"
                    class="form-control mb-3 @error('email') is-invalid @enderror"
                    type="email"
                    autocomplete="username"
                    name="email"
                    value="{{ old('email') }}"
                    required
                >

                @error('email')
                    <div class="invalid-feedback d-block mb-3">
                        {{ $message }}
                    </div>
                @enderror

                <label for="register-password" class="form-label">
                    {{ __('auth.passwordLabel') }}
                </label>

                <input
                    id="register-password"
                    class="form-control mb-3 @error('password') is-invalid @enderror"
                    type="password"
                    name="password"
                    required
                    minlength="8"
                    autocomplete="new-password"
                >

                @error('password')
                    <div class="invalid-feedback d-block mb-3">
                        {{ $message }}
                    </div>
                @enderror

                <label for="register-password-repeat" class="form-label">
                    {{ __('auth.repeatPassword') }}
                </label>

                <input
                    id="register-password-repeat"
                    class="form-control mb-3"
                    type="password"
                    name="password_confirmation"
                    required
                    minlength="8"
                    autocomplete="new-password"
                >

                <button class="btn btn-dark w-100">
                    {{ __('auth.createAccount') }}
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
