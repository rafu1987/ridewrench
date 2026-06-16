@section('title', __('privacy.title'))

<x-app-layout>
    <div class="page-hero mb-4">
        <div>
            <p class="text-white-50 mb-1">
                <i class="fa-light fa-sharp fa-shield-halved me-1"></i>
                {{ __('privacy.kicker') }}
            </p>

            <h1 class="display-6 fw-bold mb-1">
                {{ __('privacy.title') }}
            </h1>

            <p class="mb-0 text-white-50">
                {{ __('privacy.subtitle') }}
            </p>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-4 p-md-5">
            {!! __('privacy.text') !!}
        </div>
    </div>
</x-app-layout>
