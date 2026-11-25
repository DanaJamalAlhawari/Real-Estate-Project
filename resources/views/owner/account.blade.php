@extends('layouts.app')

@section('title', __('Makan — My account'))
@section('roleStyle', 'owner')
@section('bodyAttributes') data-role="owner" data-page="account"@endsection

@section('content')
    <!-- Desktop: collapses the sidebar to an icon rail (CSS only) -->
    <input
      type="checkbox"
      class="sidebar-toggle"
      id="sidebarToggle"
      aria-label="{{ __('Collapse the menu') }}"
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
          <a class="side-link" href="{{ route('owner.dashboard') }}" title="{{ __('Dashboard') }}"
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
            ><span class="flex-grow-1">{{ __('Booking requests') }}</span></a
          >
          <a class="side-link" href="{{ route('owner.chat') }}" title="{{ __('Messages') }}"
            ><i class="bi bi-chat-dots"></i
            ><span class="flex-grow-1">{{ __('Messages') }}</span></a
          >
          <a class="side-link active" href="{{ route('owner.account') }}" aria-current="page" title="{{ __('My account') }}"
            ><i class="bi bi-person-badge"></i
            ><span class="flex-grow-1">{{ __('My account') }}</span></a
          >
        </div>
      </aside>

      <!-- ===== Content ===== -->
      <main class="app-main">
        <div class="page-head"><h1>{{ __('My account') }}</h1></div>
        @include('partials.account-profile-form')
        <section class="panel mt-3">
          <h2 class="h5">{{ __('Account verification') }}</h2>
          <p class="mb-0">{{ __(\Illuminate\Support\Str::headline($profile?->verification_status ?? 'unverified')) }}</p>
        </section>
      </main>
    </div>
@endsection
