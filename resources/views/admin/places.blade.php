@extends('layouts.app')

@section('title', __('Makan — Areas'))
@section('roleStyle', 'admin')
@section('bodyAttributes') data-role="admin" data-page="places"@endsection

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
        <a
          class="icon-btn"
          href="{{ route('admin.chat') }}"
          aria-label="{{ __('Messages, 2 unread') }}"
        >
          <i class="bi bi-chat-dots"></i><span class="icon-badge">2</span>
        </a>
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
                    >4 verification requests waiting for review</span
                  >
                  <span class="text-muted" style="font-size: 0.72rem"
                    >{{ __('Today') }}</span
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
          <div class="side-label">{{ __('Admin') }}</div>
          <a class="side-link" href="{{ route('admin.dashboard') }}" title="{{ __('Dashboard') }}"
            ><i class="bi bi-grid-1x2"></i
            ><span class="flex-grow-1">{{ __('Dashboard') }}</span></a
          >
          <a class="side-link" href="{{ route('admin.verifications') }}" title="{{ __('Verifications') }}"
            ><i class="bi bi-patch-check"></i
            ><span class="flex-grow-1">{{ __('Verifications') }}</span
            ><span class="side-badge">4</span></a
          >
          <a class="side-link" href="{{ route('admin.users') }}" title="{{ __('Users') }}"
            ><i class="bi bi-people"></i
            ><span class="flex-grow-1">{{ __('Users') }}</span></a
          >
          <a class="side-link active" href="{{ route('admin.places') }}" aria-current="page" title="{{ __('Areas') }}"
            ><i class="bi bi-geo-alt"></i
            ><span class="flex-grow-1">{{ __('Areas') }}</span></a
          >
          <a class="side-link" href="{{ route('admin.chat') }}" title="{{ __('Messages') }}"
            ><i class="bi bi-chat-dots"></i
            ><span class="flex-grow-1">{{ __('Messages') }}</span
            ><span class="side-badge">2</span></a
          >
        </div>
      </aside>

      <!-- ===== Content ===== -->
      <main class="app-main">
        <!-- 1. Page header -->
        <div class="page-head">
          <div>
            <h1>{{ __('Areas') }}</h1>
            <p>
              {{ __('Average bills and internet for each area power the true monthly cost on every home.') }}
            </p>
          </div>
          <button
            type="button"
            class="btn btn-primary d-inline-flex align-items-center gap-2"
            id="addHoodBtn"
            data-action="add-hood"
          >
            <i class="bi bi-plus-lg"></i><span>{{ __('Add area') }}</span>
          </button>
        </div>

          <!-- ===== 2. Areas ===== -->
          <section aria-label="{{ __('Areas') }}">
            <div class="panel panel-table">
              <div class="table-responsive">
                <table class="table bt-table hoods-table">
                  <thead>
                    <tr>
                      <th scope="col">{{ __('Area') }}</th>
                      <th scope="col">{{ __('City') }}</th>
                      <th scope="col">{{ __('Avg. electricity &amp; water') }}</th>
                      <th scope="col">{{ __('Internet') }}</th>
                      <th scope="col">{{ __('Properties') }}</th>
                      <th scope="col">
                        <span class="visually-hidden">{{ __('Actions') }}</span>
                      </th>
                    </tr>
                  </thead>
                  <tbody id="hoodsBody">
                    <tr>
                      <td class="fw-semibold">{{ __('Al Jubeiha') }}</td>
                      <td class="text-muted">{{ __('Amman') }}</td>
                      <td>30 JOD</td>
                      <td>15 JOD</td>
                      <td class="text-muted">2</td>
                      <td>
                        <button
                          type="button"
                          class="btn-icon"
                          data-action="edit-hood"
                          aria-label="{{ __('Edit Al Jubeiha') }}"
                        >
                          <i class="bi bi-pencil"></i>
                        </button>
                      </td>
                    </tr>
                    <tr>
                      <td class="fw-semibold">{{ __('Sweileh') }}</td>
                      <td class="text-muted">{{ __('Amman') }}</td>
                      <td>25 JOD</td>
                      <td>15 JOD</td>
                      <td class="text-muted">1</td>
                      <td>
                        <button
                          type="button"
                          class="btn-icon"
                          data-action="edit-hood"
                          aria-label="{{ __('Edit Sweileh') }}"
                        >
                          <i class="bi bi-pencil"></i>
                        </button>
                      </td>
                    </tr>
                    <tr>
                      <td class="fw-semibold">{{ __('Tla Al Ali') }}</td>
                      <td class="text-muted">{{ __('Amman') }}</td>
                      <td>35 JOD</td>
                      <td>20 JOD</td>
                      <td class="text-muted">1</td>
                      <td>
                        <button
                          type="button"
                          class="btn-icon"
                          data-action="edit-hood"
                          aria-label="{{ __('Edit Tla Al Ali') }}"
                        >
                          <i class="bi bi-pencil"></i>
                        </button>
                      </td>
                    </tr>
                    <tr>
                      <td class="fw-semibold">{{ __('Shmeisani') }}</td>
                      <td class="text-muted">{{ __('Amman') }}</td>
                      <td>40 JOD</td>
                      <td>20 JOD</td>
                      <td class="text-muted">1</td>
                      <td>
                        <button
                          type="button"
                          class="btn-icon"
                          data-action="edit-hood"
                          aria-label="{{ __('Edit Shmeisani') }}"
                        >
                          <i class="bi bi-pencil"></i>
                        </button>
                      </td>
                    </tr>
                    <tr>
                      <td class="fw-semibold">{{ __('Khalda') }}</td>
                      <td class="text-muted">{{ __('Amman') }}</td>
                      <td>40 JOD</td>
                      <td>20 JOD</td>
                      <td class="text-muted">1</td>
                      <td>
                        <button
                          type="button"
                          class="btn-icon"
                          data-action="edit-hood"
                          aria-label="{{ __('Edit Khalda') }}"
                        >
                          <i class="bi bi-pencil"></i>
                        </button>
                      </td>
                    </tr>
                    <tr>
                      <td class="fw-semibold">{{ __('Abdali') }}</td>
                      <td class="text-muted">{{ __('Amman') }}</td>
                      <td>40 JOD</td>
                      <td>20 JOD</td>
                      <td class="text-muted">0</td>
                      <td>
                        <button
                          type="button"
                          class="btn-icon"
                          data-action="edit-hood"
                          aria-label="{{ __('Edit Abdali') }}"
                        >
                          <i class="bi bi-pencil"></i>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </section>
      </main>
    </div>
@endsection
