@extends('layouts.app')

@section('title', "Makan — Messages")
@section('roleStyle', 'admin')
@section('bodyAttributes') data-role="admin" data-page="chat"@endsection

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
          class="icon-btn is-active"
          href="{{ route('admin.chat') }}"
          aria-label="Messages, 2 unread"
          aria-current="page"
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
                    >4 verification requests waiting for review</span
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
          <a class="side-link" href="{{ route('admin.dashboard') }}" title="Dashboard"
            ><i class="bi bi-grid-1x2"></i
            ><span class="flex-grow-1">Dashboard</span></a
          >
          <a class="side-link" href="{{ route('admin.verifications') }}" title="Verifications"
            ><i class="bi bi-patch-check"></i
            ><span class="flex-grow-1">Verifications</span
            ><span class="side-badge">4</span></a
          >
          <a class="side-link" href="{{ route('admin.users') }}" title="Users"
            ><i class="bi bi-people"></i
            ><span class="flex-grow-1">Users</span></a
          >
          <a class="side-link" href="{{ route('admin.places') }}" title="Areas"
            ><i class="bi bi-geo-alt"></i
            ><span class="flex-grow-1">Areas</span></a
          >
          <a class="side-link active" href="{{ route('admin.chat') }}" aria-current="page" title="Messages"
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
            <h1>Messages</h1>
            <p>Talk with home seekers and property owners.</p>
          </div>
        </div>

        <!-- 2. Chat (same layout as the seeker and owner chat) -->
        <div class="chat">
          <!-- Conversation list: users and owners, each tagged with their type -->
          <div class="chat-list" id="conversationList">
            <div class="chat-list-head">
              <div class="search-input">
                <i class="bi bi-search"></i>
                <input
                  type="search"
                  class="form-control"
                  id="chatSearch"
                  name="q"
                  placeholder="Search users and owners..."
                  aria-label="Search conversations"
                />
              </div>
            </div>
            <a
              href="#chatThread"
              class="chat-item active"
              aria-current="true"
              data-conversation="1"
            >
              <span class="avatar avatar-lg">S</span>
              <span class="meta">
                <b
                  ><span>Sara Khaled <span class="role-tag role-tag--user">User</span></span
                  ><time>10:05 AM</time></b
                >
                <span class="prop">Booking BK-1042</span>
                <span>Great, thanks!</span>
              </span>
              <span class="unread" aria-label="1 unread message">1</span>
            </a>
            <a
              href="#chatThread"
              class="chat-item"
              data-conversation="2"
            >
              <span class="avatar avatar-lg">A</span>
              <span class="meta">
                <b
                  ><span>Abu Ahmad <span class="role-tag role-tag--owner">Owner</span></span
                  ><time>9:30 AM</time></b
                >
                <span class="prop">Verification · 4 properties</span>
                <span>I uploaded the title deed again.</span>
              </span>
              <span class="unread" aria-label="1 unread message">1</span>
            </a>
            <a
              href="#chatThread"
              class="chat-item"
              data-conversation="3"
            >
              <span class="avatar avatar-lg">R</span>
              <span class="meta">
                <b
                  ><span>Al Riyada Real Estate Office <span class="role-tag role-tag--owner">Owner</span></span
                  ><time>Yesterday</time></b
                >
                <span class="prop">Agency plan</span>
                <span>Can we add a second agent account?</span>
              </span>
            </a>
            <a
              href="#chatThread"
              class="chat-item"
              data-conversation="4"
            >
              <span class="avatar avatar-lg">M</span>
              <span class="meta">
                <b
                  ><span>Mohammad Al Ali <span class="role-tag role-tag--user">User</span></span
                  ><time>Monday</time></b
                >
                <span class="prop">Reported listing</span>
                <span>Thanks for checking the listing.</span>
              </span>
            </a>
          </div>

          <!-- Open conversation -->
          <div class="chat-thread" id="chatThread">
            <div class="chat-head">
              <span class="avatar avatar-lg">S</span>
              <div class="flex-grow-1 lh-sm">
                <b>Sara Khaled <span class="role-tag role-tag--user">User</span></b>
                <small class="is-online">Online now</small>
              </div>
              <a href="{{ route('admin.users') }}" class="btn btn-sm btn-outline-primary"
                >View user</a
              >
            </div>
            <div class="chat-context">
              <span class="t ph ph-1" aria-hidden="true"></span>
              <span class="flex-grow-1 text-muted"
                >About booking <b class="text-body">BK-1042</b> · Studio — Al
                Jubeiha · 265 JOD</span
              >
              <a href="{{ route('admin.dashboard') }}" class="fw-semibold">View bookings</a>
            </div>
            <div class="chat-body" id="chatBody" aria-live="polite">
              <span class="day-sep">Today</span>
              <div class="msg">
                Hi, the owner accepted my booking. Do I pay through
                Makan?<time>09:40</time>
              </div>
              <div class="msg me">
                No, you pay the rent directly to Abu Ahmad. Makan never charges
                home seekers.<time>09:52</time>
              </div>
              <div class="msg">Great, thanks!<time>10:05</time></div>
              <div class="msg me">
                Welcome to Makan! Tell us if anything isn't as listed.<time
                  >10:08</time
                >
              </div>
            </div>
            <form class="chat-input" id="chatForm" method="post" action="{{ route('demo.notice') }}" data-demo-form>
              @csrf
              <label for="chatMessage" class="visually-hidden"
                >Your message</label
              >
              <input
                type="text"
                class="form-control"
                id="chatMessage"
                name="message"
                placeholder="Type your message..."
                autocomplete="off"
              />
              <button
                type="submit"
                class="btn btn-primary d-inline-flex align-items-center gap-2"
              >
                <i class="bi bi-send"></i>
                <span>Send</span>
              </button>
            </form>
            <div class="chat-safety">
              <i class="bi bi-lock"></i>Conversations with Makan Support are
              kept for quality.
            </div>
          </div>
        </div>
      </main>
    </div>
@endsection
