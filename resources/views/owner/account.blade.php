@extends('layouts.app')

@section('title', "Makan — My account")
@section('roleStyle', 'owner')
@section('bodyAttributes') data-role="owner" data-page="account"@endsection

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
          <a class="side-link" href="{{ route('owner.properties') }}" title="My properties"
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
          <a class="side-link active" href="{{ route('owner.account') }}" aria-current="page" title="My account"
            ><i class="bi bi-person-badge"></i
            ><span class="flex-grow-1">My account</span></a
          >
        </div>
      </aside>

      <!-- ===== Content ===== -->
      <main class="app-main">
        <div class="page-head"><h1>My account</h1></div>
        @include('partials.account-profile-form')
        <section class="panel mt-3">
          <h2 class="h5">Account verification</h2>
          <p class="mb-0">{{ ucfirst($profile?->verification_status ?? 'unverified') }}</p>
        </section>
      </main>
    </div>
@endsection
