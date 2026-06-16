@php
    $redirectPath = $redirectPath ?? '/dashboard';
    $account = $account ?? null;
@endphp

@if ($account)
    <form
        method="post"
        action="{{ url('/strava/disconnect') }}"
        onsubmit="return confirm(@js(__('confirm.disconnectStrava')))"
    >
        @csrf
        <input type="hidden" name="redirect" value="{{ $redirectPath }}">

        <div class="d-grid mb-2 mb-md-0">
            <button class="btn btn-danger">
                <i class="fa-brands fa-strava me-1"></i>
                {{ __('settings.disconnectStrava') }}
            </button>
        </div>
    </form>
@else
    <a
        class="strava-connect-button"
        href="{{ url('/strava/connect?redirect=' . rawurlencode($redirectPath)) }}"
        aria-label="{{ __('settings.connectStrava') }}"
    >
        <img
            src="/images/strava/connect-with-strava.svg"
            alt="{{ __('settings.connectStrava') }}"
            width="193"
            height="48"
            loading="lazy"
        >
    </a>
@endif
