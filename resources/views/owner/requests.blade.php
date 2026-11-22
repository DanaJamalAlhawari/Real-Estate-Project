@extends('layouts.app')

@section('title', "Makan — Booking requests")
@section('roleStyle', 'owner')
@section('bodyAttributes') data-role="owner" data-page="requests"@endsection

@section('content')
<!-- Desktop: collapses the sidebar to an icon rail (CSS only) -->
<input
  type="checkbox"
  class="sidebar-toggle"
  id="sidebarToggle"
  aria-label="Collapse the menu" />

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
        aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
      <div class="side-label">Property owner</div>
      <a class="side-link" href="{{ route('owner.dashboard') }}" title="Dashboard"><i class="bi bi-grid-1x2"></i><span class="flex-grow-1">Dashboard</span></a>
      <a class="side-link" href="{{ route('owner.properties') }}" title="My properties"><i class="bi bi-house-door"></i><span class="flex-grow-1">My properties</span></a>
      <a class="side-link" href="{{ route('owner.property-form') }}" title="Add property"><i class="bi bi-plus-square"></i><span class="flex-grow-1">Add property</span></a>
      <a class="side-link active" href="{{ route('owner.requests') }}" aria-current="page" title="Booking requests"><i class="bi bi-inbox"></i><span class="flex-grow-1">Booking requests</span>
        <span class="side-badge">
          {{ $bookings->where('status', 'pending')->count() }}
        </span>
      </a>
      <a class="side-link" href="{{ route('owner.chat') }}" title="Messages"><i class="bi bi-chat-dots"></i><span class="flex-grow-1">Messages</span></a>
      <a class="side-link" href="{{ route('owner.account') }}" title="My account"><i class="bi bi-person-badge"></i><span class="flex-grow-1">My account</span></a>
    </div>
  </aside>

  <!-- ===== Content ===== -->
  <main class="app-main">
    <!-- 1. Page header -->
    <div class="page-head">
      <div>
        <h1>Booking requests</h1>
        <p>
          Reply to requests quickly — seekers prefer owners who respond
          within hours.
        </p>
      </div>
    </div>

    <!-- ===== 2. Booking requests ===== -->
    @if (session('error'))
      <p class="alert alert-danger" role="alert">{{ session('error') }}</p>
    @elseif (session('success'))
      <p class="alert alert-success" role="status">{{ session('success') }}</p>
    @endif
    <section aria-label="Booking requests">
      <!-- Filters -->
      <div
        class="d-flex flex-wrap gap-2 mb-3"
        role="radiogroup"
        aria-label="Filter requests"
        id="requestFilters">
        <input
          type="radio"
          class="btn-check"
          name="status"
          id="rfAll"
          value="all"
          checked />
        <label class="chip-select py-2" for="rfAll">All</label>
        <input
          type="radio"
          class="btn-check"
          name="status"
          id="rfPending"
          value="pending" />
        <label class="chip-select py-2" for="rfPending">
          Awaiting your reply ({{ $bookings->where('status', 'pending')->count() }})
        </label>
        <input
          type="radio"
          class="btn-check"
          name="status"
          id="rfAccepted"
          value="confirmed" />
        <label class="chip-select py-2" for="rfAccepted">Accepted</label>
        <input
          type="radio"
          class="btn-check"
          name="status"
          id="rfRejected"
          value="cancelled" />
        <label class="chip-select py-2" for="rfRejected">Cancelled</label>
      </div>

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
                {{ $booking->tenant?->display_name ?? 'Unknown Customer' }}
              </h2>
            </div>
            <div class="small text-muted">
              {{ $booking->property->title ?? 'Property unavailable' }}
            </div>
            <div class="small text-muted">
              {{ $booking->start_date->format('j M Y') }} – {{ $booking->end_date->format('j M Y') }}
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
              Accept
            </button>

            <!-- Reject Button -->
            <button
              type="button"
              class="btn btn-outline-danger"
              data-bs-toggle="modal"
              data-bs-target="#rejectModal{{ $booking->id }}">
              Reject
            </button>

            @elseif ($booking->status === 'confirmed')
            <span class="status status--success">
              Accepted
            </span>
            @elseif ($booking->status === 'cancelled')
            <span class="status status--danger">
              Cancelled
            </span>
            @elseif ($booking->status === 'completed')
            <span class="status status--success">
              Completed
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
                <h5 class="modal-title">Confirm Acceptance</h5>
                <button type="button"
                  class="btn-close"
                  data-bs-dismiss="modal">
                </button>
              </div>

              <div class="modal-body">
                Are you sure you want to accept the booking request from
                <strong>{{ $booking->tenant?->display_name ?? 'this customer' }}</strong>?
              </div>

              <div class="modal-footer">
                <button type="button"
                  class="btn btn-ghost"
                  data-bs-dismiss="modal">
                  Cancel
                </button>

                <form action="{{ route('owner.requests.accept', $booking->id) }}"
                  method="POST">
                  @csrf
                  @method('PATCH')

                  <button type="submit" class="btn btn-primary">
                    Yes, Accept
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
                <h5 class="modal-title">Confirm Rejection</h5>
                <button type="button"
                  class="btn-close"
                  data-bs-dismiss="modal">
                </button>
              </div>

              <div class="modal-body">
                Are you sure you want to reject the booking request from
                <strong>{{ $booking->tenant?->display_name ?? 'this customer' }}</strong>?
              </div>

              <div class="modal-footer">
                <button type="button"
                  class="btn btn-ghost"
                  data-bs-dismiss="modal">
                  Cancel
                </button>

                <form action="{{ route('owner.requests.reject', $booking->id) }}"
                  method="POST">
                  @csrf
                  @method('PATCH')

                  <button type="submit" class="btn btn-danger">
                    Yes, Reject
                  </button>
                </form>
              </div>

            </div>
          </div>
        </div>

        @empty
        <div class="panel text-center p-4">
          <p class="text-muted mb-0">No booking requests found.</p>
        </div>
        @endforelse
      </div>
    </section>
  </main>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {

    const filters = document.querySelectorAll(
      '#requestFilters input[name="status"]'
    );

    const bookings = document.querySelectorAll(
      '#requestsList .request-row'
    );

    filters.forEach(function(filter) {

      filter.addEventListener('change', function() {

        const selectedStatus = this.value;

        bookings.forEach(function(booking) {

          const bookingStatus = booking.dataset.status;

          if (
            selectedStatus === 'all' ||
            bookingStatus === selectedStatus
          ) {
            booking.style.display = '';
          } else {
            booking.style.display = 'none';
          }

        });

      });

    });

  });
</script>

@endsection
