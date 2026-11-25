@extends('layouts.app')

@section('title', __('Makan — Admin dashboard'))
@section('roleStyle', 'admin')
@section('bodyAttributes') data-role="admin" data-page="dashboard"@endsection

@section('content')
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
      <a class="brand" href="{{ route('admin.dashboard') }}"
        ><img class="brand-mark" src="{{ asset('assets/images/makan-mark.svg') }}" alt="" width="36" height="36"><span>Makan</span></a
      >
      <div class="ms-auto d-flex align-items-center gap-1">
        <!-- Messages: icon with unread count -->
        <a class="icon-btn" href="{{ route('admin.chat') }}" aria-label="{{ __('Messages') }}"><i class="bi bi-chat-dots"></i></a>
        <div class="dropdown">
          <button
            class="icon-btn"
            type="button"
            data-bs-toggle="dropdown"
            aria-label="{{ __('Notifications') }}"
          >
            <i class="bi bi-bell"></i><span class="dot"></span>
          </button>
          <div
            class="dropdown-menu dropdown-menu-end shadow-sm p-0"
            style="width: 320px"
          >
            <div class="px-3 py-2 border-bottom fw-semibold">{{ __('Notifications') }}</div>
            <div class="py-1">
              <a
                class="dropdown-item d-flex gap-2 py-2 text-wrap"
                href="{{ route('admin.verifications') }}"
              >
                <span
                  class="kpi-icon flex-shrink-0"
                  style="width: 34px; height: 34px; font-size: 1rem"
                  ><i class="bi bi-patch-check"></i
                ></span>
                <span class="small">
                  <span class="d-block"
                    >{{ $pendingVerificationCount }} {{ __('verification requests waiting for review') }}</span
                  >
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
            aria-label="Account menu, {{ auth()->user()->display_name }}"
          >
            <span class="avatar">{{ auth()->user()->display_initial }}</span>
          </button>
          <ul class="dropdown-menu dropdown-menu-end shadow-sm">
            <li><h6 class="dropdown-header">{{ auth()->user()->display_name }}</h6></li>
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
      @include('partials.admin-sidebar')

      <!-- ===== Content ===== -->
      <main class="app-main">
        <!-- 1. Page header -->
        <div class="page-head">
          <div>
            <h1>{{ __('Dashboard') }}</h1>
            <p>{{ __('Platform overview ·') }} {{ now()->translatedFormat('F Y') }}</p>
          </div>
        </div>

        <!-- 2. Key numbers -->
        <div class="row g-3 mb-4" id="adminKpis">
          <div class="col-sm-6 col-xl-3">
            <div class="kpi">
              <span class="kpi-icon"><i class="bi bi-people"></i></span>
              <div>
                <b>{{ $totalUsers }}</b>
                <span>{{ __('Users') }}</span>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-xl-3">
            <div class="kpi">
              <span class="kpi-icon"><i class="bi bi-house-door"></i></span>
              <div>
                <b>{{ $totalProperties }}</b>
                <span>{{ __('Properties') }}</span>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-xl-3">
            <div class="kpi">
              <span class="kpi-icon"><i class="bi bi-calendar-check"></i></span>
              <div>
                <b>{{ $bookingsThisMonth }}</b>
                <span>{{ __('Bookings this month') }}</span>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-xl-3">
            <div class="kpi">
              <span class="kpi-icon"><i class="bi bi-cash-stack"></i></span>
              <div>
                <b>{{ number_format($totalCommissionRevenue, 2) }} {{ __('JOD') }}</b>
                <span>{{ __('Total Commission Revenue') }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- 3. Commission revenue + awaiting verification -->
        <div class="row g-4 mb-4 align-items-start">
          <div class="col-lg-8">
            <section class="panel" aria-labelledby="revTitle">
              <div class="panel-head">
                <h2 id="revTitle">{{ __('Commission Revenue · Last 6 Months') }}</h2>
                <span class="small text-muted">{{ __('In JOD') }}</span>
              </div>
              <x-dashboard-chart :series="$monthlyCommissionRevenue" :label="__('Commission Revenue · Last 6 Months')" :decimals="2" />
            </section>
          </div>

          <div class="col-lg-4">
            <section class="panel" aria-labelledby="pendingTitle">
              <div class="panel-head">
                <h2 id="pendingTitle">{{ __('Awaiting verification') }}</h2>
                <a href="{{ route('admin.verifications') }}">{{ __('View all') }}</a>
              </div>
              <ul class="list-clean" id="pendingList">
                @foreach ($pendingVerificationPreview as $property)
                <li class="list-row">
                  <span class="avatar">{{ Str::substr($property->localized('title'), 0, 1) }}</span>
                  <div class="grow">
                    <b class="d-block small">{{ $property->localized('title') }}</b>
                  </div>
                  <a
                    href="{{ route('admin.verifications') }}"
                    class="small fw-semibold text-decoration-none"
                    >{{ __('Review') }}</a
                  >
                </li>
                @endforeach
                </ul>
                <x-pagination :items="$pendingVerificationPreview" />
            </section>
          </div>
        </div>

        <!-- 4. Recent paid commissions -->
        <div class="row g-4">
          <div class="col-lg-8">
        <section class="panel" aria-labelledby="subsTitle">
          <div class="panel-head">
            <h2 id="subsTitle">{{ __('Recent Paid Commissions') }}</h2>
          </div>
          <ul class="list-clean" id="subsList">
            @forelse ($recentPaidCommissions as $commission)
              @php
                $profile = $commission->agent?->agentProfile;
                $agent = trim(($profile?->first_name ?? '').' '.($profile?->last_name ?? '')) ?: 'Agent #'.$commission->agent_id;
              @endphp
              <li class="list-row">
                <span class="avatar">{{ Str::upper(Str::substr($agent, 0, 1)) }}</span>
                <div class="grow">
                  <b class="d-block small">{{ $agent }}</b>
                  <small>{{ __('Booking #') }}{{ $commission->booking_id }}@if ($commission->paid_at) · {{ $commission->paid_at->translatedFormat('M j, Y') }}@endif</small>
                </div>
                <span class="small fw-semibold">{{ number_format($commission->commission_amount, 2) }} {{ __('JOD') }}</span>
              </li>
            @empty
              <li class="list-row text-muted">{{ __('No paid commissions yet.') }}</li>
            @endforelse
        </ul>
        <x-pagination :items="$recentPaidCommissions" />
        </section>
          </div>
        </div>
      </main>
    </div>
@endsection
