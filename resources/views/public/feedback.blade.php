@section('title', __('feedback.title'))

<x-app-layout>
    <div class="page-hero mb-4">
        <div>
            <p class="text-white-50 mb-1">
                <i class="fa-light fa-sharp fa-bug me-1"></i>
                {{ __('feedback.kicker') }}
            </p>

            <h1 class="display-6 fw-bold mb-1">
                {{ __('feedback.title') }}
            </h1>

            <p class="mb-0 text-white-50">
                {{ __('feedback.subtitle') }}
            </p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7 order-2 order-lg-1">
            <div class="card card-lift h-100">
                <div class="card-body p-3 p-md-4">
                    <form method="post" action="{{ route('feedback.submit') }}">
                        @csrf

                        <label for="feedback-type" class="form-label">
                            {{ __('feedback.typeLabel') }}
                        </label>

                        <select id="feedback-type" class="form-select mb-3" name="type">
                            <option value="bug">{{ __('feedback.type.bug') }}</option>
                            <option value="idea">{{ __('feedback.type.idea') }}</option>
                            <option value="question">{{ __('feedback.type.question') }}</option>
                        </select>

                        <label for="feedback-name" class="form-label">
                            {{ __('settings.name') }}
                        </label>

                        <input
                            id="feedback-name"
                            class="form-control mb-3"
                            name="name"
                            value="{{ old('name', auth()->user()?->name) }}"
                            autocomplete="name"
                        >

                        <label for="feedback-email" class="form-label">
                            {{ __('settings.email') }}
                        </label>

                        <input
                            id="feedback-email"
                            class="form-control mb-3"
                            type="email"
                            name="email"
                            value="{{ old('email', auth()->user()?->email) }}"
                            autocomplete="email"
                        >

                        <label for="feedback-page-url" class="form-label">
                            {{ __('feedback.pageUrl') }}
                        </label>

                        <input
                            id="feedback-page-url"
                            class="form-control mb-3"
                            type="url"
                            name="page_url"
                            placeholder="https://www.ridewrench.de/..."
                            autocomplete="url"
                        >

                        <label for="feedback-message" class="form-label">
                            {{ __('feedback.message') }}
                        </label>

                        <textarea
                            id="feedback-message"
                            class="form-control mb-3 @error('message') is-invalid @enderror"
                            name="message"
                            rows="7"
                            required
                        >{{ old('message') }}</textarea>

                        @error('message')
                            <div class="invalid-feedback d-block mb-3">
                                {{ $message }}
                            </div>
                        @enderror

                        @if (!empty($recaptchaSiteKey))
                            <div class="mb-3 form-recaptcha">
                                <div
                                    id="feedback-recaptcha-wrap"
                                    data-sitekey="{{ $recaptchaSiteKey }}"
                                >
                                    <div id="feedback-recaptcha"></div>
                                </div>
                            </div>
                        @endif

                        <button class="btn btn-dark">
                            <i class="fa-light fa-sharp fa-paper-plane me-1"></i>
                            {{ __('feedback.send') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5 order-1 order-lg-2">
            <div class="card card-lift h-100">
                <div class="card-body p-3 p-md-4">
                    <h2 class="h5 mb-3">
                        <i class="fa-light fa-sharp fa-circle-info me-1"></i>
                        {{ __('feedback.helpTitle') }}
                    </h2>

                    <p class="text-muted">
                        {{ __('feedback.helpText') }}
                    </p>

                    <ul class="text-muted mb-0">
                        <li>{{ __('feedback.helpItem1') }}</li>
                        <li>{{ __('feedback.helpItem2') }}</li>
                        <li>{{ __('feedback.helpItem3') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
