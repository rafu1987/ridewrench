<div class="dropdown theme-switcher">
    <button
        class="btn btn-sm btn-theme dropdown-toggle"
        type="button"
        data-bs-toggle="dropdown"
        aria-expanded="false"
        aria-label="{{ e(__('theme.toggle')) }}"
    >
        <i class="fa-solid fa-sharp fa-circle-half-stroke me-1" data-theme-icon></i>
        <span data-theme-label>{{ e(__('theme.auto')) }}</span>
    </button>

    <ul class="dropdown-menu dropdown-menu-lg-end theme-menu">
        <li>
            <button class="dropdown-item d-flex align-items-center gap-2" type="button" data-theme-value="light" aria-pressed="false">
                <i class="fa-light fa-sharp fa-sun-bright"></i>
                <span>{{ e(__('theme.light')) }}</span>
                <i class="fa-light fa-sharp fa-check ms-auto theme-check opacity-0"></i>
            </button>
        </li>
        <li>
            <button class="dropdown-item d-flex align-items-center gap-2" type="button" data-theme-value="dark" aria-pressed="false">
                <i class="fa-light fa-sharp fa-moon-stars"></i>
                <span>{{ e(__('theme.dark')) }}</span>
                <i class="fa-light fa-sharp fa-check ms-auto theme-check opacity-0"></i>
            </button>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
            <button class="dropdown-item d-flex align-items-center gap-2" type="button" data-theme-value="auto" aria-pressed="false">
                <i class="fa-solid fa-sharp fa-circle-half-stroke"></i>
                <span>{{ e(__('theme.auto')) }}</span>
                <i class="fa-light fa-sharp fa-check ms-auto theme-check opacity-0"></i>
            </button>
        </li>
    </ul>
</div>
