@extends('layouts.app')

@section('title', "Makan — Verifications")
@section('roleStyle', 'admin')
@section('bodyAttributes') data-role="admin" data-page="verifications"@endsection

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
        <a class="icon-btn" href="{{ route('admin.chat') }}" aria-label="Messages"><i class="bi bi-chat-dots"></i></a>
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
                    >{{ $queues->get('pending', collect())->count() }} ownership documents waiting for review</span
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
    </header>

    <div class="app-shell">
      <!-- ===== Sidebar ===== -->
      @include('partials.admin-sidebar')

      <!-- ===== Content ===== -->
      <main class="app-main">
        <!-- 1. Page header -->
        <div class="page-head">
          <div>
            <h1>Verifications</h1>
            <p>
              Review property ownership documents before granting the "Verified" badge.
            </p>
          </div>
        </div>

        <div class="row g-4 align-items-start">
          <!-- 2. Request queue -->
          <div class="col-lg-5 col-xl-4">
            <section
              class="panel p-0 overflow-hidden"
              aria-label="Request queue"
            >
              @php
                $activeStatus = $selectedProperty?->verification_status ?? 'pending';
                $tabs = ['pending' => 'Pending', 'verified' => 'Approved', 'rejected' => 'Rejected'];
              @endphp
              <ul class="nav pill-tabs" role="tablist">
                @foreach ($tabs as $status => $label)
                  <li class="nav-item" role="presentation">
                    <button
                      @class(['nav-link', 'active' => $status === $activeStatus])
                      data-bs-toggle="tab"
                      data-bs-target="#queue-{{ $status }}"
                      type="button"
                      role="tab"
                      aria-controls="queue-{{ $status }}"
                      aria-selected="{{ $status === $activeStatus ? 'true' : 'false' }}"
                    >{{ $label }}@if ($status === 'pending') ({{ $queues->get($status, collect())->count() }})@endif</button>
                  </li>
                @endforeach
              </ul>
              <div class="tab-content">
                @foreach ($tabs as $status => $label)
                  <div @class(['tab-pane fade', 'show active' => $status === $activeStatus])
                       id="queue-{{ $status }}" role="tabpanel" tabindex="0">
                    @forelse ($queues->get($status, collect()) as $property)
                      <a href="{{ route('admin.verifications', ['property' => $property->id]) }}#reviewPanel"
                         @class(['queue-item', 'is-active' => $selectedProperty?->id === $property->id])>
                        <span class="avatar">{{ Str::substr($property->title, 0, 1) }}</span>
                        <span class="flex-grow-1 min-w-0">
                          <b class="d-block">{{ $property->title }}</b>
                          <small>{{ $property->address }}</small>
                        </span>
                      </a>
                    @empty
                      <p class="text-muted small text-center p-4 mb-0">No {{ strtolower($label) }} ownership verifications.</p>
                    @endforelse
                  </div>
                @endforeach
              </div>
            </section>
          </div>

          <!-- 3. Request review -->
          <div class="col-lg-7 col-xl-8">
            <section
              class="panel p-4"
              id="reviewPanel"
              aria-labelledby="reviewName"
            >
              @if (session('success'))
                <p class="text-success" role="status">{{ session('success') }}</p>
              @endif
              @if ($errors->any())
                <p class="text-danger" role="alert">{{ $errors->first() }}</p>
              @endif

              @if ($selectedProperty)
                <div class="review-head d-flex flex-wrap align-items-center gap-3">
                  <span class="avatar avatar-lg">{{ Str::substr($selectedProperty->title, 0, 1) }}</span>
                  <div class="flex-grow-1">
                    <b class="d-block fs-5" id="reviewName">{{ $selectedProperty->title }}</b>
                    <span class="small text-muted">{{ $selectedProperty->address }}</span>
                  </div>
                  <span @class([
                    'status',
                    'status--pending' => $activeStatus === 'pending',
                    'status--success' => $activeStatus === 'verified',
                    'status--danger' => $activeStatus === 'rejected',
                  ])>{{ $tabs[$activeStatus] }}</span>
                </div>

                <h2 class="h6 mt-4 mb-2">Uploaded document</h2>
                <div class="row g-3" id="reviewDocs">
                  <div class="col-sm-6">
                    <div class="doc-card">
                      <div class="doc-preview"><i class="bi bi-file-earmark-text"></i>Ownership document</div>
                      <div class="d-flex justify-content-between align-items-center px-3 py-1">
                        <b class="small">Title deed</b>
                        <a href="{{ route('admin.verifications.document', $selectedProperty) }}"
                           target="_blank" rel="noopener"
                           class="small fw-semibold text-decoration-none py-2">Open</a>
                      </div>
                    </div>
                  </div>
                </div>

                @if ($activeStatus === 'pending')
                  <form id="verifyForm" method="post" action="{{ route('admin.verifications.update', $selectedProperty) }}">
                    @csrf
                    @method('PATCH')
                    <label for="vnote" class="form-label mt-3">Admin notes</label>
                    <textarea class="form-control" id="vnote" name="note" rows="2" maxlength="2000"
                              placeholder="e.g. The title deed is not legible">{{ old('note') }}</textarea>
                    <div class="d-flex flex-wrap justify-content-end gap-2 mt-3">
                      <button type="submit" class="btn btn-outline-danger" name="decision" value="reject">Reject</button>
                      <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2" name="decision" value="approve">
                        <i class="bi bi-patch-check"></i>Approve &amp; grant badge
                      </button>
                    </div>
                  </form>
                @elseif ($selectedProperty->admin_notes)
                  <h2 class="h6 mt-4 mb-2">Admin notes</h2>
                  <p>{{ $selectedProperty->admin_notes }}</p>
                @endif
              @else
                <h2 class="h6" id="reviewName">Property review</h2>
                <p class="text-muted mb-0">Select a property to review its ownership document.</p>
              @endif
            </section>
          </div>
        </div>
      </main>
    </div>
@endsection
