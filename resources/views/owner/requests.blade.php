@extends('layouts.app')

@section('title', __('Makan — Booking requests'))
@section('roleStyle', 'owner')
@section('bodyAttributes') data-role="owner" data-page="requests"@endsection

@section('content')
<!-- Desktop: collapses the sidebar to an icon rail (CSS only) -->
<input
  type="checkbox"
  class="sidebar-toggle"
  id="sidebarToggle"
  aria-label="{{ __('Collapse the menu') }}" />

<!-- ===== Topbar ===== -->
<x-owner-topbar />

<div class="app-shell">
  <!-- ===== Sidebar ===== -->
  <aside
    class="app-sidebar offcanvas-lg offcanvas-start"
    tabindex="-1"
    id="appSidebar">
    <div class="offcanvas-header border-bottom">
      <a class="brand" href="{{ route('home') }}"><img class="brand-mark" src="{{ asset('assets/images/makan-mark.svg') }}" alt="" width="36" height="36"><span>Makan</span></a>
      <button
        type="button"
        class="btn-close"
        data-bs-dismiss="offcanvas"
        data-bs-target="#appSidebar"
        aria-label="{{ __('Close') }}"></button>
    </div>
    <div class="offcanvas-body">
      <div class="side-label">{{ __('Property owner') }}</div>
      <a class="side-link" href="{{ route('owner.dashboard') }}" title="{{ __('Dashboard') }}"><i class="bi bi-grid-1x2"></i><span class="flex-grow-1">{{ __('Dashboard') }}</span></a>
      <a class="side-link" href="{{ route('owner.properties') }}" title="{{ __('My properties') }}"><i class="bi bi-house-door"></i><span class="flex-grow-1">{{ __('My properties') }}</span></a>
      <a class="side-link" href="{{ route('owner.property-form') }}" title="{{ __('Add property') }}"><i class="bi bi-plus-square"></i><span class="flex-grow-1">{{ __('Add property') }}</span></a>
      <a class="side-link active" href="{{ route('owner.requests') }}" aria-current="page" title="{{ __('Booking requests') }}"><i class="bi bi-inbox"></i><span class="flex-grow-1">{{ __('Booking requests') }}</span>
        <span class="side-badge">
          {{ $bookings->where('status', 'pending')->count() }}
        </span>
      </a>
      <a class="side-link" href="{{ route('owner.chat') }}" title="{{ __('Messages') }}"><i class="bi bi-chat-dots"></i><span class="flex-grow-1">{{ __('Messages') }}</span></a>
      <a class="side-link" href="{{ route('owner.account') }}" title="{{ __('My account') }}"><i class="bi bi-person-badge"></i><span class="flex-grow-1">{{ __('My account') }}</span></a>
    </div>
  </aside>

  <!-- ===== Content ===== -->
  <main class="app-main">
    <!-- 1. Page header -->
    <div class="page-head">
      <div>
        <h1>{{ __('Booking requests') }}</h1>
        <p>
          {{ __('Reply to requests quickly — seekers prefer owners who respond within hours.') }}
        </p>
      </div>
    </div>

    <!-- ===== 2. Booking requests ===== -->
    @if (session('error'))
      <p class="alert alert-danger" role="alert">{{ __(session('error')) }}</p>
    @elseif (session('success'))
      <p class="alert alert-success" role="status">{{ __(session('success')) }}</p>
    @endif
    <section aria-label="{{ __('Booking requests') }}">
      <!-- Filters -->
      <nav class="d-flex flex-wrap gap-2 mb-3" aria-label="{{ __('Filter requests') }}">
        @foreach (['' => 'All', 'pending' => 'Awaiting your reply', 'confirmed' => 'Accepted', 'cancelled' => 'Cancelled'] as $status => $label)
          <a @class(['btn btn-outline-primary', 'active' => request('status', '') === $status])
             href="{{ route('owner.requests', $status === '' ? [] : ['status' => $status]) }}">
            {{ __($label) }} @if ($status === 'pending') ({{ $pendingCount }}) @endif
          </a>
        @endforeach
      </nav>

      <!-- Request list -->
      <div class="d-flex flex-column gap-3" id="requestsList">
        @forelse ($bookings as $booking)
        <!-- Request  -->
        <article
          class="panel request-row"
          data-request="{{ $booking->id }}"
          data-status="{{ $booking->status }}">
          <span class="avatar avatar-lg">
            {{ strtoupper(substr($booking->tenant?->display_name ?? 'U', 0, 1)) }}
          </span>
          <div class="flex-main">
            <div class="d-flex flex-wrap align-items-center gap-2">
              <h2 class="request-row__name">
                {{ $booking->tenant?->display_name ?? __('Unknown Customer') }}
              </h2>
            </div>
            <div class="small text-muted">
              {{ $booking->property?->localized('title') ?? __('Property unavailable') }}
            </div>
            <div class="small text-muted">
              {{ $booking->start_date->translatedFormat('j M Y') }} – {{ $booking->end_date->translatedFormat('j M Y') }}
            </div>
          </div>
          <div class="d-flex flex-wrap align-items-center gap-2">

            @if ($booking->status === 'pending')

            <!-- Accept Button -->
            <button
              type="button"
              class="btn btn-primary"
              data-bs-toggle="modal"
              data-bs-target="#acceptModal{{ $booking->id }}">
              {{ __('Accept') }}
            </button>

            <!-- Reject Button -->
            <button
              type="button"
              class="btn btn-outline-danger"
              data-bs-toggle="modal"
              data-bs-target="#rejectModal{{ $booking->id }}">
              {{ __('Reject') }}
            </button>

            @elseif ($booking->status === 'confirmed')
            <span class="status status--success">
              {{ __('Accepted') }}
            </span>
            @elseif ($booking->status === 'cancelled')
            <span class="status status--danger">
              {{ __('Cancelled') }}
            </span>
            @elseif ($booking->status === 'completed')
            <span class="status status--success">
              {{ __('Completed') }}
            </span>
            @endif

            <a
              href="{{ route('owner.chat') }}"
              class="btn btn-ghost btn-square text-primary-bt"
              aria-label="Message {{ $booking->tenant?->display_name ?? 'customer' }}"
              title="Message {{ $booking->tenant?->display_name ?? 'customer' }}">
              <i class="bi bi-chat-dots"></i>
            </a>

          </div>
        </article>

        <!-- confirem Accept -->
        <div class="modal fade"
          id="acceptModal{{ $booking->id }}"
          tabindex="-1"
          aria-hidden="true">

          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

              <div class="modal-header">
                <h5 class="modal-title">{{ __('Confirm Acceptance') }}</h5>
                <button type="button"
                  class="btn-close"
                  data-bs-dismiss="modal">
                </button>
              </div>

              <div class="modal-body">
                {{ __('Are you sure you want to accept the booking request from') }}
                <strong>{{ $booking->tenant?->display_name ?? 'this customer' }}</strong>?
              </div>

              <div class="modal-footer">
                <button type="button"
                  class="btn btn-ghost"
                  data-bs-dismiss="modal">
                  {{ __('Cancel') }}
                </button>

                <form action="{{ route('owner.requests.accept', $booking->id) }}"
                  method="POST">
                  @csrf
                  @method('PATCH')

                  <button type="submit" class="btn btn-primary">
                    {{ __('Yes, Accept') }}
                  </button>
                </form>
              </div>

            </div>
          </div>
        </div>

        <!-- confirem Reject -->
        <div class="modal fade"
          id="rejectModal{{ $booking->id }}"
          tabindex="-1"
          aria-hidden="true">

          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

              <div class="modal-header">
                <h5 class="modal-title">{{ __('Confirm Rejection') }}</h5>
                <button type="button"
                  class="btn-close"
                  data-bs-dismiss="modal">
                </button>
              </div>

              <div class="modal-body">
                {{ __('Are you sure you want to reject the booking request from') }}
                <strong>{{ $booking->tenant?->display_name ?? 'this customer' }}</strong>?
              </div>

              <div class="modal-footer">
                <button type="button"
                  class="btn btn-ghost"
                  data-bs-dismiss="modal">
                  {{ __('Cancel') }}
                </button>

                <form action="{{ route('owner.requests.reject', $booking->id) }}"
                  method="POST">
                  @csrf
                  @method('PATCH')

                  <button type="submit" class="btn btn-danger">
                    {{ __('Yes, Reject') }}
                  </button>
                </form>
              </div>

            </div>
          </div>
        </div>

        @empty
        <div class="panel text-center p-4">
          <p class="text-muted mb-0">{{ __('No booking requests found.') }}</p>
        </div>
        @endforelse
      </div>
        <x-pagination :items="$bookings" />
    </section>
  </main>
</div>

@endsection
