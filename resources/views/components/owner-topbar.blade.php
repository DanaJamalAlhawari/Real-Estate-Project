@php
    $agent = auth()->user();
    $unread = $agent->unreadMessageCount();
    $pending = \App\Models\Booking::where('status', 'pending')
        ->whereHas('property', fn ($query) => $query->where('agent_id', $agent->id))->count();
    $verified = $agent->properties()->where('verification_status', 'verified')
        ->whereNotNull('verified_at')->latest('verified_at')->first();
@endphp
<header class="app-topbar">
    <button class="icon-btn d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#appSidebar" aria-label="Menu">
        <i class="bi bi-list"></i>
    </button>
    <label for="sidebarToggle" class="icon-btn sidebar-burger d-none d-lg-inline-flex" title="Collapse or expand the menu">
        <i class="bi bi-list"></i>
    </label>
    <a class="brand" href="{{ route('owner.dashboard') }}"><span class="brand-mark">M</span><span>Makan</span></a>
    <div class="ms-auto d-flex align-items-center gap-1">
        <a class="icon-btn" href="{{ route('owner.chat') }}" data-inbox-messages-link aria-label="Messages, {{ $unread }} unread">
            <i class="bi bi-chat-dots"></i><span class="icon-badge" data-inbox-unread @if (!$unread) hidden @endif>{{ $unread }}</span>
        </a>
        <div class="dropdown">
            <button class="icon-btn" type="button" data-bs-toggle="dropdown" aria-label="Notifications"><i class="bi bi-bell"></i></button>
            <div class="dropdown-menu dropdown-menu-end">
                <a class="dropdown-item" href="{{ route('owner.requests') }}">{{ $pending }} pending booking {{ Str::plural('request', $pending) }}</a>
                @if ($verified)
                    <a class="dropdown-item text-wrap" href="{{ route('owner.properties') }}">{{ $verified->title }} was verified</a>
                @endif
            </div>
        </div>
        <div class="dropdown">
            <button class="app-user app-user--avatar" type="button" data-bs-toggle="dropdown" aria-label="Account menu, {{ $agent->display_name }}">
                <span class="avatar">{{ $agent->display_initial }}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li><h6 class="dropdown-header">{{ $agent->display_name }}</h6></li>
                <li><a class="dropdown-item" href="{{ route('owner.account') }}">My account</a></li>
                <li><x-logout-form /></li>
            </ul>
        </div>
    </div>
</header>
