<x-app-layout>
    <section class="page-hero">
        <div class="container">
            <p class="eyebrow">{{ __('auth.verifyEmail') }}</p>
            <h1>{{ __('auth.verifyEmail') }}</h1>
        </div>
    </section>

    <section class="section-shell">
        <div class="container">
            <div class="auth-card card-lift mx-auto">
                <p class="text-muted mb-4">
                    {{ __('auth.verifyEmailText') }}
                </p>

                @if (session('status') === 'verification-link-sent')
                    <div class="alert alert-success">
                        {{ __('auth.verificationLinkSent') }}
                    </div>
                @endif

                <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf

                        <button type="submit" class="btn btn-dark">
                            {{ __('auth.resendVerificationEmail') }}
                        </button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <button type="submit" class="btn btn-outline-dark">
                            {{ __('auth.logout') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>