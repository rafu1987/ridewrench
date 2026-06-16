@section('title', __('error.404.title'))

<x-app-layout>
    <div class="page-hero mb-4">
        <div>
            <p class="text-white-50 mb-1">
                <i class="fa-light fa-sharp fa-triangle-exclamation me-1"></i>
                {{ __('error.404.kicker') }}
            </p>

            <h1 class="display-6 fw-bold mb-1">
                {{ __('error.404.title') }}
            </h1>

            <p class="mb-0 text-white-50">
                {{ __('error.404.subtitle') }}
            </p>
        </div>
    </div>

    <div class="card card-lift mx-auto" style="max-width:720px">
        <div class="card-body p-4 p-md-5 text-center">
            <div class="display-1 fw-bold text-muted mb-3">404</div>

            <div class="brand-mark mx-auto mb-4">
                <i class="icon-ridewrench-road"></i>
            </div>

            <h2 class="h4 fw-bold mb-3">
                {{ __('error.404.cardTitle') }}
            </h2>

            <p class="text-muted mb-4">
                {{ __('error.404.cardText') }}
            </p>

            <div class="d-flex justify-content-center gap-2 flex-wrap">
                @auth
                    <a class="btn btn-dark" href="{{ url('/dashboard') }}">
                        <i class="fa-light fa-sharp fa-gauge-high me-1"></i>
                        {{ __('nav.dashboard') }}
                    </a>

                    <a class="btn btn-outline-dark" href="{{ url('/bikes') }}">
                        <i class="icon-ridewrench-road me-1"></i>
                        {{ __('nav.bikes') }}
                    </a>
                @else
                    <a class="btn btn-dark" href="{{ url('/') }}">
                        <i class="fa-light fa-sharp fa-house me-1"></i>
                        {{ __('nav.home') }}
                    </a>

                    <a class="btn btn-outline-dark" href="{{ url('/login') }}">
                        <i class="fa-light fa-sharp fa-right-to-bracket me-1"></i>
                        {{ __('nav.login') }}
                    </a>
                @endauth
            </div>
        </div>
    </div>
</x-app-layout>