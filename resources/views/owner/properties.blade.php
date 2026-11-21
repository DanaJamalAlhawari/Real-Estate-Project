@extends('layouts.app')

@section('title', "Makan — My properties")
@section('roleStyle', 'owner')
@section('bodyAttributes') data-role="owner" data-page="properties"@endsection

@section('content')
    <!-- Desktop: collapses the sidebar to an icon rail (CSS only) -->
    <input
      type="checkbox"
      class="sidebar-toggle"
      id="sidebarToggle"
      aria-label="Collapse the menu"
    />

    <!-- ===== Topbar ===== -->
    <x-owner-topbar />

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
          <div class="side-label">Property owner</div>
          <a class="side-link" href="{{ route('owner.dashboard') }}" title="Dashboard"
            ><i class="bi bi-grid-1x2"></i
            ><span class="flex-grow-1">Dashboard</span></a
          >
          <a class="side-link active" href="{{ route('owner.properties') }}" aria-current="page" title="My properties"
            ><i class="bi bi-house-door"></i
            ><span class="flex-grow-1">My properties</span></a
          >
          <a class="side-link" href="{{ route('owner.property-form') }}" title="Add property"
            ><i class="bi bi-plus-square"></i
            ><span class="flex-grow-1">Add property</span></a
          >
          <a class="side-link" href="{{ route('owner.requests') }}" title="Booking requests"
            ><i class="bi bi-inbox"></i
            ><span class="flex-grow-1">Booking requests</span></a
          >
          <a class="side-link" href="{{ route('owner.chat') }}" title="Messages"
            ><i class="bi bi-chat-dots"></i
            ><span class="flex-grow-1">Messages</span></a
          >
          <a class="side-link" href="{{ route('owner.account') }}" title="My account"
            ><i class="bi bi-person-badge"></i
            ><span class="flex-grow-1">My account</span></a
          >
        </div>
      </aside>

      <!-- ===== Content ===== -->
      <main class="app-main">
        <!-- 1. Page header -->
        <div class="page-head">
          <div>
            <h1>My properties</h1>
            @if(isset($ownedProperties) || isset($properties))
              @php $listForCount = $ownedProperties ?? $properties; @endphp
              <p>{{ $listForCount->count() }} properties</p>
            @endif
          </div>
          <a href="{{ route('owner.property-form') }}" class="btn btn-accent">
            <i class="bi bi-plus-lg"></i>
            <span>Add property</span>
          </a>
        </div>

        <!-- 2. Property list -->
        <div class="d-flex flex-column gap-3" id="ownerProperties">
          @if(isset($ownedProperties) || isset($properties))
            @php
              $displayProperties = $ownedProperties ?? $properties;
            @endphp
          @forelse($displayProperties as $property)
          <article class="panel prop-row" data-property="{{ $property->id }}">
            @if($property->images->first())
            <img src="{{ ($property->images->firstWhere('is_primary', true) ?? $property->images->first())->public_url }}" class="listing-thumb" style="object-fit: cover;" alt="{{ $property->title }}">
            @else
            <div class="listing-thumb ph ph-1" aria-hidden="true"></div>
            @endif
            <div class="flex-main">
              <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                <h2 class="prop-row__title">{{ $property->title }}</h2>
                @if ($property->verification_status !== 'verified')
                <span class="status status--pending">{{ ucfirst($property->verification_status) }} verification</span>
                @elseif($property->status == 'available')
                <span class="status status--success">Active</span>
                @elseif($property->status == 'hidden')
                <span class="status status--pending">Hidden</span>
                @else
                <span class="status status--pending">
                  {{ ucfirst($property->status) }}
                </span>
                @endif
                @if($property->is_featured ?? false)
                <span class="badge-featured mt-0"><i class="bi bi-star-fill"></i>Featured</span>
                @endif
              </div>
              <div class="small text-muted">
                {{ number_format($property->price, 2) }} JOD
              </div>
              <p class="small text-muted mb-0">Verification: {{ ucfirst($property->verification_status) }}</p>
              <div class="fresh-note">
                <i class="bi bi-arrow-repeat"></i>
                Last updated:
                {{ $property->updated_at?->diffForHumans() ?? 'Not available' }}
              </div>
            </div>
            <div class="stat-tiles prop-row__stats">
              <!-- Stats are intentionally left empty until real data is available. -->
            </div>
            <div class="d-flex gap-2">
              <a href="{{ route('owner.properties.edit', $property) }}"
                class="btn btn-ghost btn-square text-primary-bt"
                aria-label="Edit {{ $property->title }}">
                <i class="bi bi-pencil"></i>
              </a>
              <form action="{{ route('properties.visibility', $property->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <button
                  type="submit"
                  class="btn btn-ghost btn-square"
                  aria-label="{{ $property->status == 'hidden' ? 'Show' : 'Hide' }} {{ $property->title }}">
                  <i class="bi {{ $property->status == 'hidden' ? 'bi-eye' : 'bi-eye-slash' }}"></i>
                </button>
              </form>
              <button
                type="button"
                class="btn btn-ghost btn-square"
                data-bs-toggle="modal"
                data-bs-target="#deleteModal{{ $property->id }}"
                aria-label="Delete {{ $property->title }}">
                <i class="bi bi-trash"></i>
              </button>
              <div class="modal fade"
                id="deleteModal{{ $property->id }}"
                tabindex="-1"
                aria-labelledby="deleteModalTitle{{ $property->id }}"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                  <div class="modal-content rounded-4">
                    <div class="modal-header border-0">
                      <h5 class="modal-title fw-bold"
                        id="deleteModalTitle{{ $property->id }}">
                        Confirm Deletion
                      </h5>
                      <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                      <i class="bi bi-exclamation-triangle text-danger"
                        style="font-size: 3rem;"></i>
                      <h5 class="mt-3">Delete this property?</h5>
                      <p class="text-muted">
                        Are you sure you want to delete
                        <strong>{{ $property->title }}</strong>?
                        This action cannot be undone.
                      </p>
                    </div>
                    <div class="modal-footer border-0 justify-content-center">
                      <button type="button"
                        class="btn btn-outline-secondary"
                        data-bs-dismiss="modal">
                        Cancel
                      </button>
                      <form action="{{ route('properties.destroy', $property->id) }}"
                        method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                          <i class="bi bi-trash"></i>
                          Yes, Delete
                        </button>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </article>
          @empty
            <p class="text-muted">You have no properties yet.</p>
          @endforelse
          @endif
        </div>
      </main>
    </div>
@endsection
