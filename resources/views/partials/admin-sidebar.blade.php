<aside class="app-sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="appSidebar" aria-label="Admin menu">
    <div class="offcanvas-header border-bottom">
        <a class="brand" href="{{ route('admin.dashboard') }}"><img class="brand-mark" src="{{ asset('assets/images/makan-mark.svg') }}" alt="" width="36" height="36"><span>Makan</span></a>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#appSidebar" aria-label="Close"></button>
    </div>
    <nav class="offcanvas-body">
        <div class="side-label">Admin</div>
        @foreach ([
            'admin.dashboard' => ['Dashboard', 'bi-grid-1x2'],
            'admin.verifications' => ['Verifications', 'bi-patch-check'],
            'admin.users' => ['Users', 'bi-people'],
            'admin.commissions' => ['Commissions', 'bi-cash-stack'],
            'admin.chat' => ['Messages', 'bi-chat-dots'],
            'admin.account' => ['My account', 'bi-person-badge'],
        ] as $routeName => [$label, $icon])
            <a @class(['side-link', 'active' => request()->routeIs($routeName, $routeName.'.*')]) href="{{ route($routeName) }}">
                <i class="bi {{ $icon }}"></i><span class="flex-grow-1">{{ $label }}</span>
                @if ($routeName === 'admin.verifications' && isset($pendingVerificationCount))
                    <span class="side-badge">{{ $pendingVerificationCount }}</span>
                @endif
            </a>
        @endforeach
        <x-logout-form buttonClass="side-link text-danger border-0 bg-transparent w-100" />
    </nav>
</aside>
