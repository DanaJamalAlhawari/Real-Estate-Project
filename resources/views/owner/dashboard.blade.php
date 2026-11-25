@extends('layouts.app')

@section('title', __('Makan — Dashboard'))
@section('roleStyle', 'owner')
@section('bodyAttributes') data-role="owner" data-page="dashboard"@endsection

@section('content')
    @php
      $account = auth()->user();
      $displayName = $account?->display_name ?? $agentName;
      $displayInitial = $account?->display_initial ?? Str::upper(Str::substr($displayName ?? 'Agent', 0, 1));
    @endphp
    <!-- Desktop: collapses the sidebar to an icon rail (CSS only) -->
    <input
      type="checkbox"
      class="sidebar-toggle"
      id="sidebarToggle"
      aria-label="{{ __('Collapse the menu') }}"
    />

    <!-- ===== Topbar ===== -->
    <header class="app-topbar">
      <button
        class="icon-btn d-lg-none"
        type="button"
        data-bs-toggle="offcanvas"
        data-bs-target="#appSidebar"
        aria-label="{{ __('Menu') }}"
      >
        <i class="bi bi-list"></i>
      </button>
      <label
        for="sidebarToggle"
        class="icon-btn sidebar-burger d-none d-lg-inline-flex"
        title="{{ __('Collapse or expand the menu') }}"
        aria-hidden="true"
      >
        <i class="bi bi-list"></i>
      </label>
      <a class="brand" href="{{ route('owner.dashboard') }}"
        ><img class="brand-mark" src="{{ asset('assets/images/makan-mark.svg') }}" alt="" width="36" height="36"><span>Makan</span></a
      >
      <div class="ms-auto d-flex align-items-center gap-1">
        <!-- Messages: icon with unread count -->
        <a
          class="icon-btn"
          href="{{ route('owner.chat') }}"
          data-inbox-messages-link
          aria-label="{{ __('Messages, :count unread', ['count' => $unreadMessageCount]) }}"
        >
          <i class="bi bi-chat-dots"></i><span class="icon-badge" data-inbox-unread @if (!$unreadMessageCount) hidden @endif>{{ $unreadMessageCount }}</span>
        </a>
        <div class="dropdown">
          <button
            class="icon-btn"
            type="button"
            data-bs-toggle="dropdown"
            aria-label="{{ __('Notifications') }}"
          >
            <i class="bi bi-bell"></i>
          </button>
          <div
            class="dropdown-menu dropdown-menu-end shadow-sm p-0"
            style="width: 320px"
          >
            <div class="px-3 py-2 border-bottom fw-semibold">{{ __('Notifications') }}</div>
            <div class="py-1">
              <a
                class="dropdown-item d-flex gap-2 py-2 text-wrap"
                href="{{ route('owner.requests') }}"
              >
                <span
                  class="kpi-icon flex-shrink-0"
                  style="width: 34px; height: 34px; font-size: 1rem"
                  ><i class="bi bi-inbox"></i
                ></span>
                <span class="small">
                  <span class="d-block">{{ $pendingBookingCount }} {{ __('pending booking') }} {{ Str::plural('request', $pendingBookingCount) }}</span>
                </span>
              </a>
              <a
                class="dropdown-item d-flex gap-2 py-2 text-wrap"
                href="{{ route('owner.properties') }}"
              >
                <span
                  class="kpi-icon flex-shrink-0"
                  style="width: 34px; height: 34px; font-size: 1rem"
                  ><i class="bi bi-shield-check"></i
                ></span>
                <span class="small">
                  <span class="d-block"
                    >{{ $latestVerifiedProperty ? __(':property has been verified', ['property' => $latestVerifiedProperty->localized('title')]) : __('No property verification updates') }}</span
                  >
                  @if ($latestVerifiedProperty)
                    <span class="text-muted small">{{ $latestVerifiedProperty->verified_at->diffForHumans() }}</span>
                  @endif
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
            aria-label="{{ __('Account menu, :name', ['name' => $displayName ?? __('Agent')]) }}"
          >
            <span class="avatar">{{ $displayInitial }}</span>
          </button>
          <ul class="dropdown-menu dropdown-menu-end shadow-sm">
            <li><h6 class="dropdown-header">{{ $displayName ?? __('Agent') }}</h6></li>
            <li><a class="dropdown-item" href="{{ route('owner.account') }}">{{ __('My account') }}</a></li>
            <li><hr class="dropdown-divider" /></li>
            <li>
              <x-logout-form />
            </li>
          </ul>
        </div>
      </div>
      <x-language-switch />
    </header>

    <div class="app-shell">
      <!-- ===== Sidebar ===== -->
      <aside
        class="app-sidebar offcanvas-lg offcanvas-start"
        tabindex="-1"
        id="appSidebar"
      >
        <div class="offcanvas-header border-bottom">
          <a class="brand" href="{{ route('home') }}"
            ><img class="brand-mark" src="{{ asset('assets/images/makan-mark.svg') }}" alt="" width="36" height="36"><span>Makan</span></a
          >
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="offcanvas"
            data-bs-target="#appSidebar"
            aria-label="{{ __('Close') }}"
          ></button>
        </div>
        <div class="offcanvas-body">
          <div class="side-label">{{ __('Property owner') }}</div>
          <a class="side-link active" href="{{ route('owner.dashboard') }}" aria-current="page" title="{{ __('Dashboard') }}"
            ><i class="bi bi-grid-1x2"></i
            ><span class="flex-grow-1">{{ __('Dashboard') }}</span></a
          >
          <a class="side-link" href="{{ route('owner.properties') }}" title="{{ __('My properties') }}"
            ><i class="bi bi-house-door"></i
            ><span class="flex-grow-1">{{ __('My properties') }}</span></a
          >
          <a class="side-link" href="{{ route('owner.property-form') }}" title="{{ __('Add property') }}"
            ><i class="bi bi-plus-square"></i
            ><span class="flex-grow-1">{{ __('Add property') }}</span></a
          >
          <a class="side-link" href="{{ route('owner.requests') }}" title="{{ __('Booking requests') }}"
            ><i class="bi bi-inbox"></i
            ><span class="flex-grow-1">{{ __('Booking requests') }}</span
            ><span class="side-badge">{{ $pendingBookingCount }}</span></a
          >
          <a class="side-link" href="{{ route('owner.chat') }}" title="{{ __('Messages') }}"
            ><i class="bi bi-chat-dots"></i
            ><span class="flex-grow-1">{{ __('Messages') }}</span
            ><span class="side-badge">{{ $unreadMessageCount }}</span></a
          >
          <a class="side-link" href="{{ route('owner.account') }}" title="{{ __('My account') }}"
            ><i class="bi bi-person-badge"></i
            ><span class="flex-grow-1">{{ __('My account') }}</span></a
          >
        </div>
      </aside>

      <!-- ===== Content ===== -->
      <main class="app-main">
        <!-- 1. Page header -->
        <div class="page-head">
          <div>
            <h1>{{ __('Welcome back') }}{{ $displayName ? ', '.$displayName : '' }}</h1>
            <p>{{ __('Overview of your properties and recent activity.') }}</p>
          </div>
          <a href="{{ route('owner.property-form') }}" class="btn btn-accent">
            <i class="bi bi-plus-lg"></i>
            <span>{{ __('Add property') }}</span>
          </a>
        </div>

        <!-- 2. Availability confirmation alert -->
        <div class="alert-soft flex-wrap mb-4" id="staleAlert">
          <i class="bi bi-arrow-repeat"></i>
          <span class="flex-grow-1"
            >{{ __('Availability reminders are unavailable.') }}</span
          >
          <button
            type="button"
            class="btn btn-alert"
            disabled
          >
            {{ __('Still available') }}
          </button>
        </div>

        <!-- 3. Key numbers -->
        <div class="row g-3 mb-4" id="ownerKpis">
          <div class="col-sm-6 col-xl-3">
            <div class="kpi">
              <span class="kpi-icon"><i class="bi bi-eye"></i></span>
              <div>
                <b>—</b><span>{{ __('Views') }}</span>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-xl-3">
            <div class="kpi">
              <span class="kpi-icon"><i class="bi bi-inbox"></i></span>
              <div>
                <b>{{ $recentBookingCount }}</b><span>{{ __('Booking requests · last 30 days') }}</span>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-xl-3">
            <div class="kpi">
              <span class="kpi-icon"><i class="bi bi-stars"></i></span>
              <div><b>{{ array_sum(array_column($dailyPropertyViews, 'amount')) }}</b><span>{{ __('Property views · last 14 days') }}</span></div>
            </div>
          </div>
          <div class="col-sm-6 col-xl-3">
            <div class="kpi">
              <span class="kpi-icon"><i class="bi bi-house-door"></i></span>
              <div><b>{{ $activeProperties }} / {{ $totalProperties }}</b><span>{{ __('Active properties') }}</span></div>
            </div>
          </div>
        </div>

        <div class="row g-4">
          <!-- Main column -->
          <div class="col-lg-8 d-flex flex-column gap-4">
            <!-- 4. Views -->
            <section class="panel" aria-labelledby="viewsTitle">
              <div class="panel-head">
                <h2 id="viewsTitle">{{ __('Property views · last 14 days') }}</h2>
              </div>
              <x-dashboard-chart :series="$dailyPropertyViews" :label="__('Property views · last 14 days')" />
            </section>

            <!-- 5. New booking requests -->
            <section class="panel" aria-labelledby="newRequestsTitle">
              <div class="panel-head">
                <h2 id="newRequestsTitle">{{ __('New booking requests') }}</h2>
                <a href="{{ route('owner.requests') }}">{{ __('View all') }}</a>
              </div>
              <ul class="list-clean" id="newRequests">
                @forelse ($pendingBookings as $booking)
                  @php
                    $tenant = $booking->tenant?->tenantProfile;
                    $tenantName = trim(($tenant?->first_name ?? '').' '.($tenant?->last_name ?? '')) ?: 'Tenant #'.$booking->tenant_id;
                  @endphp
                  <li class="list-row flex-wrap">
                    <span class="avatar avatar-lg">{{ Str::upper(Str::substr($tenantName, 0, 1)) }}</span>
                    <div class="grow flex-main">
                      <b>{{ $tenantName }}</b>
                      <small>
                        {{ $booking->property?->localized('title') }} {{ __('· from') }} {{ $booking->start_date->translatedFormat('j M Y') }}
                        @if ($booking->duration_unit)
                          · {{ $booking->duration }} {{ Str::plural($booking->duration_unit === 'daily' ? 'day' : Str::beforeLast($booking->duration_unit, 'ly'), $booking->duration) }}
                        @endif
                      </small>
                    </div>
                    <div class="d-flex gap-2">
                      <form method="post" action="{{ route('owner.requests.accept', $booking) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-primary btn-sm">{{ __('Accept') }}</button>
                      </form>
                      <form method="post" action="{{ route('owner.requests.reject', $booking) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-ghost btn-sm">{{ __('Decline') }}</button>
                      </form>
                    </div>
                  </li>
                @empty
                  <li class="list-row text-muted">{{ __('No pending booking requests.') }}</li>
                @endforelse
        <x-pagination :items="$pendingBookings" />
              </ul>
            </section>
          </div>

          <!-- Side column -->
          <div class="col-lg-4 d-flex flex-column gap-4">
            <!-- 6. Featured listing -->
            <section class="panel panel-featured">
              <div class="featured-label">
                <i class="bi bi-star-fill"></i>{{ __('Featured listing') }}
              </div>
              <p>{{ __('Featured listing data is unavailable.') }}</p>
              <a href="{{ route('owner.account') }}" class="btn btn-light text-primary-bt">{{ __('My account') }}</a>
            </section>

            <!-- 7. Per-property performance -->
            <section class="panel" aria-labelledby="perfTitle">
              <div class="panel-head">
                <h2 id="perfTitle">{{ __('Performance by property') }}</h2>
              </div>
              <div class="d-flex flex-column gap-3" id="propertyPerformance">
                @forelse ($propertyPreview as $property)
                  <div class="d-flex justify-content-between small mb-1">
                    <span class="fw-medium">{{ $property->localized('title') }}</span>
                    <span class="text-muted">{{ __((int) $property->period_views === 1 ? ':count view' : ':count views', ['count' => (int) $property->period_views]) }}</span>
                  </div>
                @empty
                  <p class="text-muted small mb-0">{{ __('No properties to show.') }}</p>
                @endforelse
              </div>
        <x-pagination :items="$propertyPreview" />
            </section>

            <!-- 8. Verification status -->
            <section class="panel d-flex align-items-center gap-3">
              <span @class(['kpi-icon', 'is-success' => $verificationStatus === 'verified'])
                ><i class="bi bi-patch-check-fill"></i
              ></span>
              <div>
                <b class="d-block">{{ __('Account verification') }}</b>
                <span class="small text-muted">{{ $verificationStatus ? __(\Illuminate\Support\Str::headline($verificationStatus)) : __('Status unavailable') }}</span>
              </div>
            </section>
          </div>
        </div>
      </main>
    </div>
@endsection
