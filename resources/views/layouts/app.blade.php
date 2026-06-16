@php
    $user = $user ?? auth()->user();
    $currentPath = request()->path() === '/' ? '/' : '/' . request()->path();
    $isLandingPage = $currentPath === '/';
    $isDevEnv = app()->environment(['local', 'dev']);

    $isPrivatePage =
        str_starts_with($currentPath, '/dashboard') ||
        str_starts_with($currentPath, '/bikes') ||
        str_starts_with($currentPath, '/alerts') ||
        str_starts_with($currentPath, '/settings') ||
        str_starts_with($currentPath, '/admin');

    $robotsContent = null;

    if ($isDevEnv || $isPrivatePage) {
        $robotsContent = 'noindex,nofollow';
    }

    $active = static function (string $path) use ($currentPath): string {
        if ($path === '/') {
            return $currentPath === '/' ? 'active' : '';
        }

        return str_starts_with($currentPath, $path) ? 'active' : '';
    };

    $languages = config('ridewrench.languages', [
        'en' => ['native' => 'English'],
        'de' => ['native' => 'Deutsch'],
        'fr' => ['native' => 'Français'],
        'it' => ['native' => 'Italiano'],
        'es' => ['native' => 'Español'],
    ]);

    $currentLanguage = app()->getLocale();

    $pageTitle = trim($__env->yieldContent('title', config('app.name')));
@endphp

<!doctype html>
<html lang="{{ e($currentLanguage) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">

    <title>{{ e($pageTitle) }} - {{ e(config('app.name')) }}</title>

    @if ($isLandingPage)
        <meta name="description" content="{{ e(__('meta.homeDescription')) }}">
        <link rel="canonical" href="{{ e(config('app.url')) }}/">

        <meta property="og:type" content="website">
        <meta property="og:site_name" content="{{ e(config('app.name')) }}">
        <meta property="og:title" content="{{ e(config('app.name')) }} - {{ e(__('home.heroTitle')) }}">
        <meta property="og:description" content="{{ e(__('meta.homeDescription')) }}">
        <meta property="og:url" content="{{ e(config('app.url')) }}/">
        <meta property="og:image" content="{{ e(config('app.url')) }}/images/social/ridewrench-og.jpg">

        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ e(config('app.name')) }} - {{ e(__('home.heroTitle')) }}">
        <meta name="twitter:description" content="{{ e(__('meta.homeDescription')) }}">
        <meta name="twitter:image" content="{{ e(config('app.url')) }}/images/social/ridewrench-twitter.jpg">

        @php
            $jsonLd = [
                "\u{40}context" => 'https://schema.org',
                "\u{40}type" => 'SoftwareApplication',
                'name' => config('app.name'),
                'applicationCategory' => 'SportsApplication',
                'operatingSystem' => 'Web',
                'url' => rtrim((string) config('app.url'), '/') . '/',
                'description' => __('meta.homeDescription'),
                'creator' => [
                    "\u{40}type" => 'Person',
                    'name' => 'Raphael Zschorsch',
                ],
                'offers' => [
                    "\u{40}type" => 'Offer',
                    'price' => '0',
                    'priceCurrency' => 'EUR',
                ],
            ];
        @endphp

        <script type="application/ld+json">
        {!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
        </script>
    @endif

    @if ($robotsContent)
        <meta name="robots" content="{{ e($robotsContent) }}">
    @endif

    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="{{ e(config('app.name')) }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <meta name="color-scheme" content="light dark">
    <meta name="theme-color" content="#f4f6f8" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#0f141b" media="(prefers-color-scheme: dark)">

    <link rel="apple-touch-icon-precomposed" sizes="57x57" href="/images/favicon/apple-touch-icon-57x57.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="/images/favicon/apple-touch-icon-114x114.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="/images/favicon/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="/images/favicon/apple-touch-icon-144x144.png">
    <link rel="apple-touch-icon-precomposed" sizes="60x60" href="/images/favicon/apple-touch-icon-60x60.png">
    <link rel="apple-touch-icon-precomposed" sizes="120x120" href="/images/favicon/apple-touch-icon-120x120.png">
    <link rel="apple-touch-icon-precomposed" sizes="76x76" href="/images/favicon/apple-touch-icon-76x76.png">
    <link rel="apple-touch-icon-precomposed" sizes="152x152" href="/images/favicon/apple-touch-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/images/favicon/apple-touch-icon-180x180.png">

    <link rel="icon" type="image/png" href="/images/favicon/favicon-196x196.png" sizes="196x196">
    <link rel="icon" type="image/png" href="/images/favicon/favicon-96x96.png" sizes="96x96">
    <link rel="icon" type="image/png" href="/images/favicon/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/images/favicon/favicon-16x16.png" sizes="16x16">
    <link rel="icon" type="image/png" href="/images/favicon/favicon-128.png" sizes="128x128">

    <meta name="application-name" content="{{ e(config('app.name')) }}">
    <meta name="msapplication-TileColor" content="#fc4c02">
    <meta name="msapplication-TileImage" content="/images/favicon/mstile-144x144.png">
    <meta name="msapplication-square70x70logo" content="/images/favicon/mstile-70x70.png">
    <meta name="msapplication-square150x150logo" content="/images/favicon/mstile-150x150.png">
    <meta name="msapplication-wide310x150logo" content="/images/favicon/mstile-310x150.png">
    <meta name="msapplication-square310x310logo" content="/images/favicon/mstile-310x310.png">

    <link
        rel="apple-touch-startup-image"
        href="/images/splash/apple-splash-1320-2868.png"
        media="(device-width: 440px) and (device-height: 956px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)"
    >
    <link
        rel="apple-touch-startup-image"
        href="/images/splash/apple-splash-1206-2622.png"
        media="(device-width: 402px) and (device-height: 874px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)"
    >
    <link
        rel="apple-touch-startup-image"
        href="/images/splash/apple-splash-1290-2796.png"
        media="(device-width: 430px) and (device-height: 932px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)"
    >
    <link
        rel="apple-touch-startup-image"
        href="/images/splash/apple-splash-1179-2556.png"
        media="(device-width: 393px) and (device-height: 852px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)"
    >
    <link
        rel="apple-touch-startup-image"
        href="/images/splash/apple-splash-1170-2532.png"
        media="(device-width: 390px) and (device-height: 844px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)"
    >
    <link
        rel="apple-touch-startup-image"
        href="/images/splash/apple-splash-1284-2778.png"
        media="(device-width: 428px) and (device-height: 926px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)"
    >
    <link
        rel="apple-touch-startup-image"
        href="/images/splash/apple-splash-1242-2688.png"
        media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)"
    >
    <link
        rel="apple-touch-startup-image"
        href="/images/splash/apple-splash-828-1792.png"
        media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)"
    >
    <link
        rel="apple-touch-startup-image"
        href="/images/splash/apple-splash-1125-2436.png"
        media="(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)"
    >
    <link
        rel="apple-touch-startup-image"
        href="/images/splash/apple-splash-1242-2208.png"
        media="(device-width: 414px) and (device-height: 736px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)"
    >
    <link
        rel="apple-touch-startup-image"
        href="/images/splash/apple-splash-750-1334.png"
        media="(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)"
    >
    <link
        rel="apple-touch-startup-image"
        href="/images/splash/apple-splash-960-2079.png"
        media="(device-width: 320px) and (device-height: 693px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)"
    >
    <link
        rel="apple-touch-startup-image"
        href="/images/splash/apple-splash-886-1920.png"
        media="(device-width: 393px) and (device-height: 852px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)"
    >

    @vite(['resources/js/app.js'])
</head>

<body class="d-flex flex-column min-vh-100">
<nav class="navbar navbar-expand-xxl app-navbar sticky-top mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ url('/') }}">
            {!! view('partials.logo') !!}
        </a>

        <button
            class="navbar-toggler hamburger hamburger--squeeze"
            type="button"
            data-bs-toggle="offcanvas"
            data-bs-target="#appNav"
            aria-controls="appNav"
            aria-expanded="false"
            aria-label="{{ e(__('nav.toggle')) }}"
        >
            <span class="hamburger-box">
                <span class="hamburger-inner"></span>
            </span>
        </button>

        <div class="offcanvas offcanvas-end" id="appNav">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title fw-bold" id="appNavLabel">{{ e(__('app.name')) }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="{{ e(__('common.close')) }}"></button>
            </div>

            <div class="offcanvas-body px-0">
                @auth
                    <ul class="navbar-nav gap-2 app-nav ms-xxl-3 me-auto">
                        <li class="nav-item px-3 px-xxl-0">
                            <a class="nav-link {{ $active('/dashboard') }}" href="{{ url('/dashboard') }}">
                                <i class="fa-light fa-sharp fa-gauge-high me-1"></i>
                                {{ e(__('nav.dashboard')) }}
                            </a>
                        </li>

                        <li class="nav-item px-3 px-xxl-0">
                            <a class="nav-link {{ $active('/bikes') }}" href="{{ url('/bikes') }}">
                                <i class="fa-light fa-sharp fa-bicycle me-1"></i>
                                {{ e(__('nav.bikes')) }}
                            </a>
                        </li>

                        <li class="nav-item px-3 px-xxl-0">
                            <a class="nav-link {{ $active('/alerts') }}" href="{{ url('/alerts') }}">
                                <i class="fa-light fa-sharp fa-bell me-1"></i>
                                {{ e(__('nav.alerts')) }}
                            </a>
                        </li>

                        <li class="nav-item px-3 px-xxl-0 dropdown">
                            <a
                                href="#"
                                class="nav-link dropdown-toggle {{ $active('/history') || $active('/settings') || $active('/admin') ? 'active' : '' }}"
                                role="button"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                            >
                                <i class="fa-light fa-sharp fa-circle-ellipsis me-1"></i>
                                {{ e(__('nav.more')) }}
                            </a>

                            <ul class="dropdown-menu mt-2">
                                <li>
                                    <a class="dropdown-item {{ $active('/history') }}" href="{{ url('/history') }}">
                                        <i class="fa-light fa-sharp fa-clock-rotate-left me-1"></i>
                                        {{ e(__('nav.history')) }}
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item {{ $active('/settings') }}" href="{{ url('/settings') }}">
                                        <i class="fa-light fa-sharp fa-gear me-1"></i>
                                        {{ e(__('nav.settings')) }}
                                    </a>
                                </li>

                                @if ($user?->is_admin)
                                    <li><hr class="dropdown-divider"></li>

                                    <li>
                                        <a class="dropdown-item {{ $active('/admin') }}" href="{{ url('/admin') }}">
                                            <i class="fa-light fa-sharp fa-user-crown me-1"></i>
                                            {{ e(__('nav.admin')) }}
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </li>

                        <li class="nav-item d-xxl-none">
                            <hr class="my-1">
                        </li>

                        <li class="nav-item px-3 px-xxl-0">
                            <form method="post" class="nav-link d-xxl-none" action="{{ route('logout') }}">
                                @csrf

                                <button class="text-reset p-0 m-0 border-0 bg-transparent text-start w-100">
                                    <i class="fa-light fa-sharp fa-right-from-bracket me-1"></i>
                                    {{ e(__('nav.logout')) }}
                                </button>
                            </form>
                        </li>
                    </ul>

                    <div class="d-flex align-items-center gap-2 mt-3 mt-xxl-0 flex-wrap px-3 px-xxl-0">
                        <span class="app-user small d-none d-xxl-inline">
                            {{ e($user->name ?: $user->email) }}
                        </span>

                        @include('partials.language-switcher', [
                            'languages' => $languages,
                            'currentLanguage' => $currentLanguage,
                        ])

                        @include('partials.theme-switcher')

                        <form method="post" class="d-none d-xxl-block" action="{{ route('logout') }}">
                            @csrf

                            <button class="btn btn-sm btn-outline-secondary">
                                <i class="fa-light fa-sharp fa-right-from-bracket me-1"></i>
                                {{ e(__('nav.logout')) }}
                            </button>
                        </form>
                    </div>
                @else
                    <div class="navbar-nav gap-2 app-nav ms-xxl-4 me-auto">
                        <a class="nav-link {{ $active('/login') }}" href="{{ route('login') }}">
                            <i class="fa-light fa-sharp fa-right-to-bracket me-1"></i>
                            {{ e(__('nav.login')) }}
                        </a>

                        <a class="nav-link {{ $active('/register') }}" href="{{ route('register') }}">
                            <i class="fa-light fa-sharp fa-user-plus me-1"></i>
                            {{ e(__('nav.register')) }}
                        </a>
                    </div>

                    <div class="d-flex align-items-center gap-2 mt-3 mt-xxl-0 flex-wrap">
                        @include('partials.language-switcher', [
                            'languages' => $languages,
                            'currentLanguage' => $currentLanguage,
                        ])

                        @include('partials.theme-switcher')
                    </div>
                @endauth
            </div>
        </div>
    </div>
</nav>

<main class="container mb-5 flex-grow-1">
    @if (session('status'))
        @php
            $status = (string) session('status');
            $statusTranslationKey = 'flash.' . $status;
            $statusMessage = __($statusTranslationKey);
        @endphp

        <div class="alert alert-success mb-4">
            {{ e($statusMessage !== $statusTranslationKey ? $statusMessage : $status) }}
        </div>
    @endif

    @if (session('success'))
        @php
            $success = (string) session('success');
            $successTranslationKey = 'flash.' . $success;
            $successMessage = __($successTranslationKey);
        @endphp

        <div class="alert alert-success mb-4">
            {{ e($successMessage !== $successTranslationKey ? $successMessage : $success) }}
        </div>
    @endif

    @if (session('error'))
        @php
            $error = (string) session('error');
            $errorTranslationKey = 'flash.' . $error;
            $errorMessage = __($errorTranslationKey);
        @endphp

        <div class="alert alert-danger mb-4">
            {{ e($errorMessage !== $errorTranslationKey ? $errorMessage : $error) }}
        </div>
    @endif

    {{ $slot }}
</main>

<div class="container">
    <footer class="py-3 my-2">
        <ul class="nav justify-content-center border-bottom pb-3 mb-3">
            @auth
                <li class="nav-item">
                    <a href="{{ url('/dashboard') }}" class="nav-link px-2 text-body-secondary">
                        {{ e(__('nav.dashboard')) }}
                    </a>
                </li>
            @endauth

            <li class="nav-item">
                <a href="{{ url('/faq') }}" class="nav-link px-2 text-body-secondary">
                    {{ e(__('footer.faq')) }}
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ url('/feedback') }}" class="nav-link px-2 text-body-secondary">
                    {{ e(__('footer.feedback')) }}
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ url('/legal-notice') }}" class="nav-link px-2 text-body-secondary">
                    {{ e(__('footer.legalNotice')) }}
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ url('/privacy') }}" class="nav-link px-2 text-body-secondary">
                    {{ e(__('footer.privacy')) }}
                </a>
            </li>

            <li class="nav-item">
                <a href="https://github.com/rafu1987/ridewrench" target="_blank" class="nav-link px-2 text-body-secondary">
                    <i class="fa-brands fa-github"></i>
                </a>
            </li>
        </ul>

        <p class="text-center text-body-secondary small mb-2">
            {{ e(__('footer.stravaDisclaimer')) }}
        </p>

        <p class="text-center text-body-secondary mb-0">
            &copy; {{ date('Y') }} {{ e(config('app.name')) }}
        </p>
    </footer>
</div>

<script>
    window.rideWrenchTranslations = {
        theme: {
            light: @json(__('theme.light')),
            dark: @json(__('theme.dark')),
            auto: @json(__('theme.auto'))
        }
    };
</script>

@if ($currentPath === '/feedback' && (string) config('services.recaptcha.site_key') !== '')
    <script src="https://www.google.com/recaptcha/api.js?onload=rideWrenchRecaptchaReady&render=explicit" async defer></script>
@endif
</body>
</html>