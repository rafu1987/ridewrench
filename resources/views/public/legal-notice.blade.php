@section('title', __('legal.title'))

<x-app-layout>
    <div class="page-hero mb-4">
        <div>
            <p class="text-white-50 mb-1">
                <i class="fa-light fa-sharp fa-scale-balanced me-1"></i>
                {{ __('legal.kicker') }}
            </p>

            <h1 class="display-6 fw-bold mb-1">
                {{ __('legal.title') }}
            </h1>

            <p class="mb-0 text-white-50">
                {{ __('legal.subtitle') }}
            </p>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-4 p-md-5">
            {!! __('legal.text') !!}
        </div>
    </div>
</x-app-layout>
