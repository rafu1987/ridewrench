@section('title', __('feedback.thankYouTitle'))

<x-app-layout>
    <div class="page-hero mb-4">
        <div>
            <p class="text-white-50 mb-1">
                <i class="fa-light fa-sharp fa-circle-check me-1"></i>
                {{ __('feedback.thankYouKicker') }}
            </p>

            <h1 class="display-6 fw-bold mb-1">
                {{ __('feedback.thankYouTitle') }}
            </h1>

            <p class="mb-0 text-white-50">
                {{ __('feedback.thankYouSubtitle') }}
            </p>
        </div>
    </div>

    <div class="card card-lift">
        <div class="card-body p-3 p-md-4 text-center">
            <div class="display-5 mb-3 text-success">
                <i class="fa-light fa-sharp fa-paper-plane"></i>
            </div>

            <h2 class="h4">
                {{ __('feedback.thankYouHeadline') }}
            </h2>

            <p class="text-muted mb-4">
                {{ __('feedback.thankYouText') }}
            </p>

            <a class="btn btn-dark" href="{{ url('/') }}">
                <i class="fa-light fa-sharp fa-arrow-left me-1"></i>
                {{ __('feedback.backHome') }}
            </a>
        </div>
    </div>
</x-app-layout>
