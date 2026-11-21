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
      <a class="brand" href="{{ route('owner.dashboard') }}"
        ><span class="brand-mark">M</span><span>Makan</span></a
      >
      <div class="ms-auto d-flex align-items-center gap-1">
        <!-- Messages: icon with unread count -->
        <a
          class="icon-btn"
          href="{{ route('owner.chat') }}"
          aria-label="Messages, 1 unread"
        >
          <i class="bi bi-chat-dots"></i><span class="icon-badge">1</span>
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
                href="{{ route('owner.requests') }}"
              >
                <span
                  class="kpi-icon flex-shrink-0"
                  style="width: 34px; height: 34px; font-size: 1rem"
                  ><i class="bi bi-inbox"></i
                ></span>
                <span class="small">
                  <span class="d-block">New booking request from Sara Khaled</span>
                  <span class="text-muted" style="font-size: 0.72rem"
                    >10 minutes ago</span
                  >
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
                    >Your Al Jubeiha studio has been verified</span
                  >
                  <span class="text-muted" style="font-size: 0.72rem"
                    >1 hour ago</span
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
            <li><a class="dropdown-item" href="{{ route('owner.account') }}">My account</a></li>
            <li><hr class="dropdown-divider" /></li>
            <li>
              <x-logout-form />
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
            ><span class="flex-grow-1">Booking requests</span
            ><span class="side-badge">3</span></a
          >
          <a class="side-link" href="{{ route('owner.chat') }}" title="Messages"
            ><i class="bi bi-chat-dots"></i
            ><span class="flex-grow-1">Messages</span
            ><span class="side-badge">1</span></a
          >
          <a class="side-link active" href="{{ route('owner.account') }}" aria-current="page" title="My account"
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
            <h1>My account</h1>
            <p>Your details, verification, and subscription.</p>
          </div>
        </div>

        <div class="row g-4 align-items-start">
          <!-- ===== Main column ===== -->
          <div class="col-lg-8 d-flex flex-column gap-4">
            <!-- 2. Personal details -->
            <form
              class="panel form-section"
              id="accountForm"
              aria-labelledby="secPersonal"
             method="post" action="{{ route('demo.notice') }}" data-demo-form>
              @csrf
              <h2 id="secPersonal" class="mb-3">Personal details</h2>
              <div class="row g-3">
                <div class="col-md-6">
                  <label for="aName" class="form-label">Name</label>
                  <input
                    type="text"
                    class="form-control"
                    id="aName"
                    name="name"
                    value="{{ auth()->user()->display_name }}"
                    autocomplete="name"
                  />
                </div>
                <div class="col-md-6">
                  <label for="aType" class="form-label">Account type</label>
                  <select class="form-select" id="aType" name="account_type">
                    <option value="individual" selected>Individual owner</option>
                    <option value="agency">Real estate agency</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label for="aEmail" class="form-label">Email</label>
                  <input
                    type="email"
                    class="form-control"
                    id="aEmail"
                    name="email"
                    value="{{ auth()->user()->email }}"
                    autocomplete="email"
                  />
                </div>
                <div class="col-md-6">
                  <label for="aPhone" class="form-label">Phone number</label>
                  <input
                    type="tel"
                    class="form-control text-start"
                    id="aPhone"
                    name="phone"
                    value="{{ auth()->user()->agentProfile?->phone }}"
                    autocomplete="tel"
                  />
                </div>
              </div>
              <div
                class="d-flex align-items-center gap-2 mt-3 small text-muted"
              >
                <i class="bi bi-lock text-primary-bt"></i>
                <span
                  >Your phone number and email aren't shown to seekers until a
                  booking is confirmed.</span
                >
              </div>
            </form>

            <!-- 3. Verification -->
            <section class="panel" aria-labelledby="secVerify">
              <div class="panel-head flex-wrap">
                <h2 id="secVerify">Verification</h2>
                <span class="status status--success status--icon"
                  ><i class="bi bi-patch-check-fill"></i>Verified account</span
                >
              </div>
              <ul class="list-clean" id="verificationDocs">
                <li class="list-row flex-wrap">
                  <span class="kpi-icon"
                    ><i class="bi bi-file-earmark-text"></i
                  ></span>
                  <div class="grow flex-main">
                    <b class="d-block">National ID</b>
                    <small>Uploaded 12 Sep · Reviewed by admin 13 Sep</small>
                  </div>
                  <span class="status status--success">Approved</span>
                </li>
                <li class="list-row flex-wrap">
                  <span class="kpi-icon"
                    ><i class="bi bi-file-earmark-text"></i
                  ></span>
                  <div class="grow flex-main">
                    <b class="d-block">Title deed — Al Jubeiha studio</b>
                    <small>Uploaded 12 Sep · Reviewed by admin 13 Sep</small>
                  </div>
                  <span class="status status--success">Approved</span>
                </li>
              </ul>
              <label
                class="btn btn-outline-primary btn-dashed mt-3"
                for="extraDoc"
              >
                <i class="bi bi-upload"></i>
                <span>Upload another document</span>
              </label>
              <input
                type="file"
                class="visually-hidden"
                id="extraDoc"
                name="document"
                accept="image/*,application/pdf"
              />
            </section>
          </div>

          <!-- ===== Subscription ===== -->
          <aside
            class="col-lg-4 d-flex flex-column gap-3"
            aria-labelledby="secPlans"
            id="plans"
          >
            <h2 class="h5 mb-0" id="secPlans">Subscription</h2>

            <!-- Free -->
            <div class="plan">
              <div
                class="d-flex justify-content-between align-items-center gap-2"
              >
                <b class="fs-5">Free</b>
              </div>
              <div class="price">0 JOD</div>
              <ul class="check-list mt-0">
                <li><i class="bi bi-check2"></i>Up to 3 properties</li>
                <li><i class="bi bi-check2"></i>Basic stats</li>
              </ul>
              <button
                type="button"
                class="btn btn-outline-primary mt-auto"
                data-plan="free"
              >
                Basic plan
              </button>
            </div>

            <!-- Featured (current) -->
            <div class="plan is-current">
              <div
                class="d-flex justify-content-between align-items-center gap-2"
              >
                <b class="fs-5">Featured</b>
                <span class="pill-soft">Your current plan</span>
              </div>
              <div class="price">10 JOD <small>per month per property</small></div>
              <ul class="check-list mt-0">
                <li><i class="bi bi-check2"></i>Your property at the top of results</li>
                <li><i class="bi bi-check2"></i>"Featured" badge</li>
                <li><i class="bi bi-check2"></i>Detailed stats</li>
              </ul>
              <button
                type="button"
                class="btn btn-primary mt-auto"
                data-plan="featured"
              >
                Renew until 30 Nov
              </button>
            </div>

            <!-- Real estate agency -->
            <div class="plan">
              <div
                class="d-flex justify-content-between align-items-center gap-2"
              >
                <b class="fs-5">Agency</b>
              </div>
              <div class="price">35 JOD <small>per month</small></div>
              <ul class="check-list mt-0">
                <li><i class="bi bi-check2"></i>Unlimited properties</li>
                <li><i class="bi bi-check2"></i>5 featured listings</li>
                <li><i class="bi bi-check2"></i>Monthly reports</li>
              </ul>
              <button
                type="button"
                class="btn btn-outline-primary mt-auto"
                data-plan="agency"
              >
                Upgrade your account
              </button>
            </div>

            <div class="d-flex align-items-center gap-2 small text-muted">
              <i class="bi bi-receipt"></i>
              <span>Last payment: 10 JOD · 30 Sep 2026</span>
            </div>
          </aside>
        </div>
      </main>
    </div>
@endsection
