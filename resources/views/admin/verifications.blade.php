@extends('layouts.app')

@section('title', __('Makan — Verifications'))
@section('roleStyle', 'admin')
@section('bodyAttributes') data-role="admin" data-page="verifications"@endsection

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
                    >{{ $pendingCount }} {{ __('ownership documents waiting for review') }}</span
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
            <h1>{{ __('Verifications') }}</h1>
            <p>
              {{ __('Review property ownership documents before granting the "Verified" badge.') }}
            </p>
          </div>
        </div>

        <div class="row g-4 align-items-start">
          <!-- 2. Request queue -->
          <div class="col-lg-5 col-xl-4">
            <section
              class="panel p-0 overflow-hidden"
              aria-label="{{ __('Request queue') }}"
            >
              @php($tabs = ['pending' => 'Pending', 'verified' => 'Approved', 'rejected' => 'Rejected'])
              <div class="p-3 pb-0">
                <form method="get" action="{{ route('admin.verifications') }}" class="d-flex gap-2" role="search">
                  <input type="hidden" name="status" value="{{ $activeStatus }}">
                  <input type="search" class="form-control" name="q" maxlength="255" value="{{ request('q') }}"
                         aria-label="{{ __('Search properties') }}" placeholder="{{ __('Search properties') }}">
                  <button class="btn btn-primary" type="submit">{{ __('Search') }}</button>
                </form>
              </div>
              <nav class="nav pill-tabs" aria-label="{{ __('Verification status') }}">
                @foreach ($tabs as $status => $label)
                  <a @class(['nav-link', 'active' => $status === $activeStatus])
                     href="{{ route('admin.verifications', ['status' => $status] + request()->only('q')) }}"
                     @if ($status === $activeStatus) aria-current="page" @endif>
                    {{ __($label) }} @if ($status === 'pending') ({{ $pendingCount }}) @endif
                  </a>
                @endforeach
              </nav>
              @forelse ($queue as $property)
                <a href="{{ route('admin.verifications', ['property' => $property->id, 'status' => $activeStatus] + request()->only(['q', 'page'])) }}#reviewPanel"
                   @class(['queue-item', 'is-active' => $selectedProperty?->id === $property->id])>
                  <span class="avatar">{{ Str::substr($property->localized('title'), 0, 1) }}</span>
                  <span class="flex-grow-1 min-w-0">
                    <b class="d-block">{{ $property->localized('title') }}</b>
                    <small>{{ $property->localized('address') }}</small>
                  </span>
                </a>
              @empty
                <p class="text-muted small text-center p-4 mb-0">{{ __('No matching ownership verifications.') }}</p>
              @endforelse
              <div class="px-3"><x-pagination :items="$queue" /></div>
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
                <p class="text-success" role="status">{{ __(session('success')) }}</p>
              @endif
              @if ($errors->any())
                <p class="text-danger" role="alert">{{ $errors->first() }}</p>
              @endif

              @if ($selectedProperty)
                <div class="review-head d-flex flex-wrap align-items-center gap-3">
                  <span class="avatar avatar-lg">{{ Str::substr($selectedProperty->localized('title'), 0, 1) }}</span>
                  <div class="flex-grow-1">
                    <b class="d-block fs-5" id="reviewName">{{ $selectedProperty->localized('title') }}</b>
                    <span class="small text-muted">{{ $selectedProperty->localized('address') }}</span>
                  </div>
                  <span @class([
                    'status',
                    'status--pending' => $activeStatus === 'pending',
                    'status--success' => $activeStatus === 'verified',
                    'status--danger' => $activeStatus === 'rejected',
                  ])>{{ __($tabs[$activeStatus]) }}</span>
                </div>

                <h2 class="h6 mt-4 mb-2">{{ __('Uploaded document') }}</h2>
                <div class="row g-3" id="reviewDocs">
                  <div class="col-sm-6">
                    <div class="doc-card">
                      <div class="doc-preview"><i class="bi bi-file-earmark-text"></i>{{ __('Ownership document') }}</div>
                      <div class="d-flex justify-content-between align-items-center px-3 py-1">
                        <b class="small">{{ __('Title deed') }}</b>
                        <a href="{{ route('admin.verifications.document', $selectedProperty) }}"
                           target="_blank" rel="noopener"
                           class="small fw-semibold text-decoration-none py-2">{{ __('Open') }}</a>
                      </div>
                    </div>
                  </div>
                </div>

                @if ($activeStatus === 'pending')
                  <form id="verifyForm" method="post" action="{{ route('admin.verifications.update', $selectedProperty) }}">
                    @csrf
                    @method('PATCH')
                    <label for="commission-rate" class="form-label">{{ __('Commission rate (%)') }}</label>
                    <input class="form-control" type="number" id="commission-rate" name="commission_rate"
                           min="0" max="100" step="0.01"
                           value="{{ old('commission_rate', config('commissions.category_rates.'.$selectedProperty->category)) }}">
                    @error('commission_rate')
                      <p class="text-danger small">{{ $message }}</p>
                    @enderror
                    <label for="vnote" class="form-label mt-3">{{ __('Admin notes') }}</label>
                    <textarea class="form-control" id="vnote" name="note" rows="2" maxlength="2000"
                              placeholder="{{ __('e.g. The title deed is not legible') }}">{{ old('note') }}</textarea>
                    <div class="d-flex flex-wrap justify-content-end gap-2 mt-3">
                      <button type="submit" class="btn btn-outline-danger" name="decision" value="reject" formnovalidate>{{ __('Reject') }}</button>
                      <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2" name="decision" value="approve">
                        <i class="bi bi-patch-check"></i>{{ __('Approve &amp; grant badge') }}
                      </button>
                    </div>
                  </form>
                @else
                  @if ($selectedProperty->approved_commission_rate !== null)
                    <p class="mt-3">{{ __('Approved commission:') }} {{ $selectedProperty->approved_commission_rate }}%</p>
                  @endif
                  @if ($selectedProperty->admin_notes)
                    <h2 class="h6 mt-4 mb-2">{{ __('Admin notes') }}</h2>
                    <p>{{ $selectedProperty->admin_notes }}</p>
                  @endif
                @endif
              @else
                <h2 class="h6" id="reviewName">{{ __('Property review') }}</h2>
                <p class="text-muted mb-0">{{ __('Select a property to review its ownership document.') }}</p>
              @endif
            </section>
          </div>
        </div>
      </main>
    </div>
@endsection
