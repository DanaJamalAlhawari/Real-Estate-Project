@php
    $tenantProfile = auth()->user()->tenantProfile;
    $tenantName = trim(($tenantProfile?->first_name ?? '') . ' ' . ($tenantProfile?->last_name ?? ''));
    $tenantName = $tenantName !== '' ? $tenantName : auth()->user()->email;
    $tenantInitial = mb_strtoupper(mb_substr($tenantName, 0, 1));
@endphp

<header class="site-header app-nav">
    <nav class="navbar navbar-expand-lg" aria-label="Main">
        <div class="container">
            <a class="brand" href="{{ route('seeker.search') }}">
                <span class="brand-mark">M</span><span>Makan</span>
            </a>

            <div class="d-flex align-items-center gap-1 ms-auto order-lg-last">
                <a class="icon-btn" href="{{ route('seeker.chat') }}" aria-label="Messages, 2 unread">
                    <i class="bi bi-chat-dots"></i><span class="icon-badge">2</span>
                </a>

                <div class="dropdown">
                    <button class="icon-btn" type="button" data-bs-toggle="dropdown" aria-label="Notifications">
                        <i class="bi bi-bell"></i><span class="dot"></span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow-sm p-0" style="width: 320px">
                        <div class="px-3 py-2 border-bottom fw-semibold">Notifications</div>
                        <div class="py-1">
                            <a class="dropdown-item d-flex gap-2 py-2 text-wrap" href="{{ route('seeker.property') }}">
                                <span class="kpi-icon flex-shrink-0" style="width: 34px; height: 34px; font-size: 1rem">
                                    <i class="bi bi-stars"></i>
                                </span>
                                <span class="small">
                                    <span class="d-block">A new home just listed in Al Jubeiha with a 92% match</span>
                                    <span class="text-muted" style="font-size: 0.72rem">10 minutes ago</span>
                                </span>
                            </a>
                            <a class="dropdown-item d-flex gap-2 py-2 text-wrap" href="{{ route('seeker.bookings') }}">
                                <span class="kpi-icon flex-shrink-0" style="width: 34px; height: 34px; font-size: 1rem">
                                    <i class="bi bi-check2-circle"></i>
                                </span>
                                <span class="small">
                                    <span class="d-block">Abu Ahmad accepted your booking</span>
                                    <span class="text-muted" style="font-size: 0.72rem">1 hour ago</span>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="dropdown">
                    <button
                        class="app-user app-user--avatar"
                        type="button"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                        aria-label="Account menu, {{ $tenantName }}"
                    >
                        <span class="avatar">{{ $tenantInitial }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li><h6 class="dropdown-header">{{ $tenantName }}</h6></li>
                        <li><a class="dropdown-item" href="{{ route('seeker.profile') }}">My profile</a></li>
                        <li><hr class="dropdown-divider" /></li>
                        <li><x-logout-form /></li>
                    </ul>
                </div>

                <button
                    class="navbar-toggler border-0"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#appNav"
                    aria-controls="appNav"
                    aria-expanded="false"
                    aria-label="Menu"
                >
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>

            <div class="collapse navbar-collapse" id="appNav">
                <div class="nav-group">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="{{ route('seeker.search') }}">
                                <i class="bi bi-stars"></i><span>Discover</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('seeker.bookings') }}">
                                <i class="bi bi-calendar-check"></i><span>My bookings</span>
                                <span class="side-badge">1</span>
                            </a>
                        </li>
                    </ul>
                    <span class="nav-group-divider" aria-hidden="true"></span>
                    <form class="nav-search" action="{{ route('seeker.search') }}" method="get" role="search">
                        <i class="bi bi-search" aria-hidden="true"></i>
                        <input
                            type="search"
                            class="form-control"
                            name="q"
                            placeholder="Search by area..."
                            aria-label="Search homes by neighborhood"
                        />
                    </form>
                </div>
            </div>
        </div>
    </nav>
</header>
