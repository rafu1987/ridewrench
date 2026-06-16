<div class="dropdown language-switcher">
    <button
        class="btn btn-sm btn-theme dropdown-toggle"
        type="button"
        data-bs-toggle="dropdown"
        aria-expanded="false"
        aria-label="{{ e(__('language.toggle')) }}"
    >
        <i class="fa-light fa-sharp fa-language me-1"></i>
        {{ e(strtoupper($currentLanguage)) }}
    </button>

    <ul class="dropdown-menu dropdown-menu-lg-end theme-menu">
        @foreach ($languages as $languageKey => $languageConfig)
            <li>
                <form method="post" action="{{ url('/language') }}">
                    @csrf

                    <input type="hidden" name="language" value="{{ e($languageKey) }}">
                    <input type="hidden" name="redirect" value="{{ e(request()->getRequestUri()) }}">

                    <button
                        class="dropdown-item d-flex align-items-center gap-2 {{ $currentLanguage === $languageKey ? 'active' : '' }}"
                        type="submit"
                    >
                        <span>{{ e(strtoupper($languageKey)) }}</span>
                        <span>{{ e($languageConfig['native'] ?? strtoupper($languageKey)) }}</span>

                        @if ($currentLanguage === $languageKey)
                            <i class="fa-light fa-sharp fa-check ms-auto"></i>
                        @endif
                    </button>
                </form>
            </li>
        @endforeach
    </ul>
</div>
