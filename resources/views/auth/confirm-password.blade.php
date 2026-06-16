<x-app-layout>
    <section class="page-hero">
        <div class="container">
            <p class="eyebrow">{{ __('auth.confirmPassword') }}</p>
            <h1>{{ __('auth.confirmPassword') }}</h1>
        </div>
    </section>

    <section class="section-shell">
        <div class="container">
            <div class="auth-card card-lift mx-auto">
                <p class="text-muted mb-4">
                    {{ __('auth.confirmPasswordText') }}
                </p>

                <form method="POST" action="{{ route('password.confirm') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="password" class="form-label">
                            {{ __('auth.passwordLabel') }}
                        </label>

                        <input
                            id="password"
                            class="form-control @error('password') is-invalid @enderror"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            autofocus
                        >

                        @error('password')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-dark">
                        {{ __('auth.confirm') }}
                    </button>
                </form>
            </div>
        </div>
    </section>
</x-app-layout>