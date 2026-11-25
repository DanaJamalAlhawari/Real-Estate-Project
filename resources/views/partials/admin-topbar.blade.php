<header class="app-topbar">
    <button class="icon-btn d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#appSidebar" aria-label="{{ __('Menu') }}">
        <i class="bi bi-list"></i>
    </button>
    <a class="brand" href="{{ route('admin.dashboard') }}"><img class="brand-mark" src="{{ asset('assets/images/makan-mark.svg') }}" alt="" width="36" height="36"><span>Makan</span></a>
    <x-language-switch />
</header>
