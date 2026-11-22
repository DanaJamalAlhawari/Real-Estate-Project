@php
    $tenantProfile = auth()->user()->tenantProfile;
    $tenantName = trim(($tenantProfile?->first_name ?? '') . ' ' . ($tenantProfile?->last_name ?? ''));
    $tenantName = $tenantName !== '' ? $tenantName : auth()->user()->email;
    $tenantInitial = mb_strtoupper(mb_substr($tenantName, 0, 1));
    $unread = auth()->user()->unreadMessageCount();
    $pendingBookings = auth()->user()->bookings()->where('status', 'pending')->count();
    $bookingUpdates = auth()->user()->bookings()->whereIn('status', ['confirmed', 'cancelled'])
        ->with('property')->withExists(['payments as has_successful_payment' => fn ($query) => $query->where('status', 'successful')])
        ->latest('updated_at')->limit(3)->get();
@endphp

<header class="site-header app-nav">
    <nav class="navbar navbar-expand-lg" aria-label="Main">
        <div class="container">
            <a class="brand" href="{{ route('seeker.search') }}">
                <img class="brand-mark" src="{{ asset('assets/images/makan-mark.svg') }}" alt="" width="36" height="36"><span>Makan</span>
            </a>

            <div class="d-flex align-items-center gap-1 ms-auto order-lg-last">
                <a class="icon-btn {{ request()->routeIs('payments.mock.cart') ? 'is-current' : '' }}" href="{{ route('payments.mock.cart') }}" aria-label="Payment cart" @if(request()->routeIs('payments.mock.cart')) aria-current="page" @endif>
                    <i class="bi bi-bag" aria-hidden="true"></i>
                </a>
                <a class="icon-btn" href="{{ route('seeker.chat') }}" data-inbox-messages-link aria-label="Messages, {{ $unread }} unread">
                    <i class="bi bi-chat-dots"></i><span class="icon-badge" data-inbox-unread @if (!$unread) hidden @endif>{{ $unread }}</span>
                </a>

                <div class="dropdown">
                    <button class="icon-btn" type="button" data-bs-toggle="dropdown" aria-label="Notifications">
                        <i class="bi bi-bell"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow-sm p-0" style="width: 320px">
                        <div class="px-3 py-2 border-bottom fw-semibold">Notifications</div>
                        <div class="py-1">
                            @forelse ($bookingUpdates as $booking)
                                @php($canPay = $booking->status === 'confirmed' && ! $booking->has_successful_payment)
                                <a class="dropdown-item text-wrap" href="{{ $canPay ? route('payments.mock.show', $booking) : route('seeker.bookings') }}">
                                    {{ $booking->property?->title ?? 'Property' }} — {{ $canPay ? 'Accepted. You can now pay.' : ($booking->has_successful_payment ? 'Paid' : ucfirst($booking->status)) }}
                                </a>
                            @empty
                                <div class="px-3 py-2 text-muted">No booking updates yet.</div>
                            @endforelse
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
                            <a class="nav-link {{ request()->routeIs('seeker.search') ? 'active' : '' }}" @if(request()->routeIs('seeker.search')) aria-current="page" @endif href="{{ route('seeker.search') }}">
                                <i class="bi bi-stars"></i><span>Discover</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('seeker.favorites') ? 'active' : '' }}" @if(request()->routeIs('seeker.favorites')) aria-current="page" @endif href="{{ route('seeker.favorites') }}">
                                <i class="bi bi-heart" aria-hidden="true"></i><span>Favorites</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('seeker.bookings') ? 'active' : '' }}" @if(request()->routeIs('seeker.bookings')) aria-current="page" @endif href="{{ route('seeker.bookings') }}">
                                <i class="bi bi-calendar-check"></i><span>My bookings</span>
                                @if ($pendingBookings)<span class="side-badge">{{ $pendingBookings }}</span>@endif
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
