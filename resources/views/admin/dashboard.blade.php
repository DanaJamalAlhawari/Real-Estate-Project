@extends('layouts.app')

@section('title', "Makan — Admin dashboard")
@section('roleStyle', 'admin')
@section('bodyAttributes') data-role="admin" data-page="dashboard"@endsection

@section('content')
    <!-- Desktop: collapses the sidebar to an icon rail (CSS only) -->
    <input
      type="checkbox"
      class="sidebar-toggle"
      id="sidebarToggle"
      aria-label="Collapse the menu"
    />

    <!-- ===== Topbar ===== -->
    <header class="app-topbar">
      <button
        class="icon-btn d-lg-none"
        type="button"
        data-bs-toggle="offcanvas"
        data-bs-target="#appSidebar"
        aria-label="Menu"
      >
        <i class="bi bi-list"></i>
      </button>
      <label
        for="sidebarToggle"
        class="icon-btn sidebar-burger d-none d-lg-inline-flex"
        title="Collapse or expand the menu"
        aria-hidden="true"
      >
        <i class="bi bi-list"></i>
      </label>
      <a class="brand" href="{{ route('admin.dashboard') }}"
        ><span class="brand-mark">M</span><span>Makan</span></a
      >
      <div class="ms-auto d-flex align-items-center gap-1">
        <!-- Messages: icon with unread count -->
        <a
          class="icon-btn"
          href="{{ route('admin.chat') }}"
          aria-label="Messages, 2 unread"
        >
          <i class="bi bi-chat-dots"></i><span class="icon-badge">2</span>
        </a>
        <div class="dropdown">
          <button
            class="icon-btn"
            type="button"
            data-bs-toggle="dropdown"
            aria-label="Notifications"
          >
            <i class="bi bi-bell"></i><span class="dot"></span>
          </button>
          <div
            class="dropdown-menu dropdown-menu-end shadow-sm p-0"
            style="width: 320px"
          >
            <div class="px-3 py-2 border-bottom fw-semibold">Notifications</div>
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
                    >{{ $pendingVerificationCount }} verification requests waiting for review</span
                  >
                  <span class="text-muted" style="font-size: 0.72rem"
                    >Today</span
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
            aria-label="Account menu, System admin"
          >
            <span class="avatar">A</span>
          </button>
          <ul class="dropdown-menu dropdown-menu-end shadow-sm">
            <li><h6 class="dropdown-header">System admin</h6></li>
            <li>
              <a class="dropdown-item text-danger" href="{{ route('home') }}"
                >Log out</a
              >
            </li>
          </ul>
        </div>
      </div>
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
            ><span class="brand-mark">M</span><span>Makan</span></a
          >
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="offcanvas"
            data-bs-target="#appSidebar"
            aria-label="Close"
          ></button>
        </div>
        <div class="offcanvas-body">
          <div class="side-label">Admin</div>
          <a class="side-link active" href="{{ route('admin.dashboard') }}" aria-current="page" title="Dashboard"
            ><i class="bi bi-grid-1x2"></i
            ><span class="flex-grow-1">Dashboard</span></a
          >
          <a class="side-link" href="{{ route('admin.verifications') }}" title="Verifications"
            ><i class="bi bi-patch-check"></i
            ><span class="flex-grow-1">Verifications</span
            ><span class="side-badge">{{ $pendingVerificationCount }}</span></a
          >
          <a class="side-link" href="{{ route('admin.users') }}" title="Users"
            ><i class="bi bi-people"></i
            ><span class="flex-grow-1">Users</span></a
          >
          <a class="side-link" href="{{ route('admin.places') }}" title="Areas"
            ><i class="bi bi-geo-alt"></i
            ><span class="flex-grow-1">Areas</span></a
          >
          <a class="side-link" href="{{ route('admin.chat') }}" title="Messages"
            ><i class="bi bi-chat-dots"></i
            ><span class="flex-grow-1">Messages</span
            ><span class="side-badge">2</span></a
          >
        </div>
      </aside>

      <!-- ===== Content ===== -->
      <main class="app-main">
        <!-- 1. Page header -->
        <div class="page-head">
          <div>
            <h1>Dashboard</h1>
            <p>Platform overview · October 2026</p>
          </div>
        </div>

        <!-- 2. Key numbers -->
        <div class="row g-3 mb-4" id="adminKpis">
          <div class="col-sm-6 col-xl-3">
            <div class="kpi">
              <span class="kpi-icon"><i class="bi bi-people"></i></span>
              <div>
                <b>{{ $totalUsers }}</b>
                <span>Users</span> <span class="trend">+8%</span>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-xl-3">
            <div class="kpi">
              <span class="kpi-icon"><i class="bi bi-house-door"></i></span>
              <div>
                <b>{{ $totalProperties }}</b>
                <span>Properties</span> <span class="trend">+1</span>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-xl-3">
            <div class="kpi">
              <span class="kpi-icon"><i class="bi bi-calendar-check"></i></span>
              <div>
                <b>{{ $bookingsThisMonth }}</b>
                <span>Bookings this month</span>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-xl-3">
            <div class="kpi">
              <span class="kpi-icon"><i class="bi bi-cash-stack"></i></span>
              <div>
                <b>{{ number_format($totalCommissionRevenue, 2) }} JOD</b>
                <span>Total Commission Revenue</span>
              </div>
            </div>
          </div>
        </div>

        <!-- 3. Commission revenue + awaiting verification -->
        <div class="row g-4 mb-4 align-items-start">
          <div class="col-lg-8">
            <section class="panel" aria-labelledby="revTitle">
              <div class="panel-head">
                <h2 id="revTitle">Commission Revenue · Last 6 Months</h2>
                <span class="small text-muted">In JOD</span>
              </div>
              @php
                $maxCommissionRevenue = max(array_column($monthlyCommissionRevenue, 'amount'));
              @endphp
              <div
                class="bar-chart rev-chart"
                id="revenueChart"
                role="img"
                aria-label="Commission revenue in JOD: {{ collect($monthlyCommissionRevenue)->map(fn ($month) => $month['label'].' '.number_format($month['amount'], 2))->implode('; ') }}"
              >
                @foreach ($monthlyCommissionRevenue as $month)
                  @php
                    $height = $maxCommissionRevenue > 0 ? round(160 * $month['amount'] / $maxCommissionRevenue) : 0;
                  @endphp
                  <div class="bar-col">
                    <span>{{ number_format($month['amount'], 2) }}</span>
                    <div @class(['bar', 'is-hi' => $loop->last]) style="height: {{ $height }}px"></div>
                    <span>{{ $month['label'] }}</span>
                  </div>
                @endforeach
              </div>
            </section>
          </div>

          <div class="col-lg-4">
            <section class="panel" aria-labelledby="pendingTitle">
              <div class="panel-head">
                <h2 id="pendingTitle">Awaiting verification</h2>
                <a href="{{ route('admin.verifications') }}">View all</a>
              </div>
              <ul class="list-clean" id="pendingList">
                @foreach ($pendingVerificationPreview as $property)
                <li class="list-row">
                  <span class="avatar">{{ Str::substr($property->title, 0, 1) }}</span>
                  <div class="grow">
                    <b class="d-block small">{{ $property->title }}</b>
                  </div>
                  <a
                    href="{{ route('admin.verifications') }}"
                    class="small fw-semibold text-decoration-none"
                    >Review</a
                  >
                </li>
                @endforeach
              </ul>
            </section>
          </div>
        </div>

        <!-- 4. Recent paid commissions -->
        <div class="row g-4">
          <div class="col-lg-8">
        <section class="panel" aria-labelledby="subsTitle">
          <div class="panel-head">
            <h2 id="subsTitle">Recent Paid Commissions</h2>
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
                  <small>Booking #{{ $commission->booking_id }}@if ($commission->paid_at) · {{ $commission->paid_at->format('M j, Y') }}@endif</small>
                </div>
                <span class="small fw-semibold">{{ number_format($commission->commission_amount, 2) }} JOD</span>
              </li>
            @empty
              <li class="list-row text-muted">No paid commissions yet.</li>
            @endforelse
          </ul>
        </section>
          </div>
        </div>
      </main>
    </div>
@endsection
