@extends('layouts.app')
@section('title', "Makan — Discover")
@section('roleStyle', 'seeker')
@section('bodyAttributes') data-role="seeker" data-page="search"@endsection
@section('content')
<!-- ===== Navbar ===== -->
<header class="site-header app-nav">
  <nav class="navbar navbar-expand-lg" aria-label="Main">
    <div class="container">
      <a class="brand" href="{{ route('seeker.search') }}"><span class="brand-mark">M</span><span>Makan</span></a>
      <!-- Messages, notifications + user menu (stay visible on phones) -->
      <div class="d-flex align-items-center gap-1 ms-auto order-lg-last">
        <!-- Messages: icon with unread count -->
        <a
          class="icon-btn"
          href="{{ route('seeker.chat') }}"
          aria-label="Messages, 2 unread">
          <i class="bi bi-chat-dots"></i><span class="icon-badge">2</span>
        </a>
        <div class="dropdown">
          <button
            class="icon-btn"
            type="button"
            data-bs-toggle="dropdown"
            aria-label="Notifications">
            <i class="bi bi-bell"></i><span class="dot"></span>
          </button>
          <div
            class="dropdown-menu dropdown-menu-end shadow-sm p-0"
            style="width: 320px">
            <div class="px-3 py-2 border-bottom fw-semibold">Notifications</div>
            <div class="py-1">
              <a
                class="dropdown-item d-flex gap-2 py-2 text-wrap"
                href="{{ route('seeker.property') }}">
                <span
                  class="kpi-icon flex-shrink-0"
                  style="width: 34px; height: 34px; font-size: 1rem"><i class="bi bi-stars"></i></span>
                <span class="small">
                  <span class="d-block">A new home just listed in Al Jubeiha with a 92%
                    match</span>
                  <span class="text-muted" style="font-size: 0.72rem">10 minutes ago</span>
                </span>
              </a>
              <a
                class="dropdown-item d-flex gap-2 py-2 text-wrap"
                href="{{ route('seeker.bookings') }}">
                <span
                  class="kpi-icon flex-shrink-0"
                  style="width: 34px; height: 34px; font-size: 1rem"><i class="bi bi-check2-circle"></i></span>
                <span class="small">
                  <span class="d-block">Abu Ahmad accepted your booking</span>
                  <span class="text-muted" style="font-size: 0.72rem">1 hour ago</span>
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
            aria-label="Account menu, {{ auth()->user()->display_name }}">
            <span class="avatar">{{ auth()->user()->display_initial }}</span>
          </button>
          <ul class="dropdown-menu dropdown-menu-end shadow-sm">
            <li>
              <h6 class="dropdown-header">{{ auth()->user()->display_name }}</h6>
            </li>
            <li><a class="dropdown-item" href="{{ route('seeker.profile') }}">My profile</a></li>
            <li>
              <hr class="dropdown-divider" />
            </li>
            <li>
              <x-logout-form />
            </li>
          </ul>
        </div>
        <button
          class="navbar-toggler border-0"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#appNav"
          aria-controls="appNav"
          aria-expanded="false"
          aria-label="Menu">
          <span class="navbar-toggler-icon"></span>
        </button>
      </div>
      <!-- Page links + search, grouped in one capsule -->
      <div class="collapse navbar-collapse" id="appNav">
        <div class="nav-group">
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link active" aria-current="page" href="{{ route('seeker.search') }}"><i class="bi bi-stars"></i><span>Discover</span></a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="{{ route('seeker.bookings') }}"><i class="bi bi-calendar-check"></i><span>My bookings</span><span class="side-badge">1</span></a>
            </li>
          </ul>
          <span class="nav-group-divider" aria-hidden="true"></span>
          <!-- Search: plain GET form, opens the Discover page -->
          <form
            class="nav-search"
            action="{{ route('seeker.search') }}"
            method="get"
            role="search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input
              type="search"
              class="form-control"
              name="q"
              placeholder="Search by area..."
              aria-label="Search homes by neighborhood" />
          </form>
        </div>
      </div>
    </div>
  </nav>
</header>
<!-- ===== Content ===== -->
<main class="app-page container">
  <!-- 1. Page header -->
  <div class="page-head">
    <div>
      <h1>Discover homes</h1>
      <p>
        See the homes picked for your priorities, or browse everything
        owners have listed.
      </p>
  <div class="seeker-results-layout" style="margin-top: 35px;">
    <aside class="panel seeker-filters" aria-labelledby="filterHeading">
      <h2 class="h5 mb-3" id="filterHeading"><i class="bi bi-sliders me-2" aria-hidden="true"></i>Filters</h2>
      <form id="seekerFilters" action="{{ route('seeker.search') }}" method="get">
        <fieldset class="mb-3">
          <legend class="form-label">Property type</legend>
          <div class="d-grid gap-2">
            <label class="form-check">
              <input class="form-check-input" type="radio" name="category" value="" {{ !request('category') ? 'checked' : '' }}>
              <span class="form-check-label">All</span>
            </label>
            <label class="form-check">
              <input class="form-check-input" type="radio" name="category" value="student_apartment" {{ request('category') == 'student_apartment' ? 'checked' : '' }}>
              <span class="form-check-label">Student Apartment</span>
            </label>
            <label class="form-check">
              <input class="form-check-input" type="radio" name="category" value="apartment" {{ request('category') == 'apartment' ? 'checked' : '' }}>
              <span class="form-check-label">Apartment</span>
            </label>
            <label class="form-check">
              <input class="form-check-input" type="radio" name="category" value="vacation" {{ request('category') == 'vacation' ? 'checked' : '' }}>
              <span class="form-check-label">Vacation</span>
            </label>
          </div>
        </fieldset>
        <div class="mb-3">
          <label class="form-label" for="filterArea">Area</label>
          <input class="form-control" id="filterArea" name="q" type="search" value="{{ request('q') }}" placeholder="e.g. Amman, Al Jubeiha">
        </div>
        <div class="mb-3">
          <label class="form-label" for="filterBudget">Max monthly cost (JOD)</label>
          <input class="form-control" id="filterBudget" name="max" type="number" min="0" step="1" value="{{ request('max') }}" placeholder="Any budget" />
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
          <button class="btn btn-accent" type="submit">Apply filters</button>
          <a class="btn btn-link" href="{{ route('seeker.search') }}">Clear</a>
        </div>
      </form>
    </aside>
    <div class="seeker-results">
      <!-- Discover tabs: All homes and Favorites -->
      <ul class="nav discover-switch mb-4" role="tablist">
        <li class="nav-item" role="presentation">
          <button
            class="nav-link active"
            id="all-tab"
            data-bs-toggle="tab"
            data-bs-target="#tab-all"
            type="button"
            role="tab"
            aria-controls="tab-all"
            aria-selected="true">
            <i class="bi bi-houses"></i>
            <span class="text-start">
              <b>All homes</b>
              <small>Everything owners have listed</small>
            </span>
            <span class="count">{{ $properties->count() }}</span>
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button
            class="nav-link"
            id="favorites-tab"
            data-bs-toggle="tab"
            data-bs-target="#tab-favorites"
            type="button"
            role="tab"
            aria-controls="tab-favorites"
            aria-selected="false">
            <i class="bi bi-heart"></i>
            <span class="text-start">
              <b>Favorites</b>
              <small>Homes you saved</small>
            </span>
            <span class="count">2</span>
          </button>
        </li>
      </ul>
      <div class="tab-content">
        <!-- ===== All homes ===== -->
        <section
          class="tab-pane fade show active"
          id="tab-all"
          role="tabpanel"
          aria-labelledby="all-tab"
          tabindex="0">
          <p class="small text-muted mb-3">
            {{ $properties->count() }} homes
          </p>
          <!-- Property cards -->
          <div class="row g-4" id="allHomesGrid">
            @forelse ($properties as $property)
            <div class="col-sm-6 col-xxl-4">
              <article class="prop-card">
                {{-- Property Images --}}
                <div class="prop-media ph ph-1">
    @if ($property->images->isNotEmpty())
        <div id="propertyCarousel{{ $property->id }}"
             class="carousel slide property-carousel"
             data-bs-interval="false"
             data-bs-wrap="true">
            <div class="carousel-inner">
                @foreach ($property->images as $image)
                    <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                        <img src="{{ $image->image_url }}"
                             alt="{{ $property->title }}"
                             class="d-block w-100 property-carousel-image">
                    </div>
                @endforeach
            </div>
            @if ($property->images->count() > 1)
                <button class="carousel-control-prev" type="button"
                        data-bs-target="#propertyCarousel{{ $property->id }}"
                        data-bs-slide="prev" aria-label="Previous image">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <button class="carousel-control-next" type="button"
                        data-bs-target="#propertyCarousel{{ $property->id }}"
                        data-bs-slide="next" aria-label="Next image">
                    <i class="bi bi-chevron-right"></i>
                </button>
            @endif
        </div>
    @else
        <span class="ph-icon"><i class="bi bi-house-door"></i></span>
    @endif
</div>
                {{-- Property Details --}}
                <div class="prop-body">
                  <h3 class="prop-title">
                    {{ $property->title }}
                  </h3>
                  <div class="prop-meta">
                    <span>
                      <i class="bi bi-geo-alt"></i>
                      {{ $property->area }}, {{ $property->city }}
                    </span>
                    <span>
                      <i class="bi bi-house-door"></i>
                      {{ $property->category }}
                    </span>
                    <span>
                      <i class="bi bi-door-open"></i>
                      {{ $property->bedrooms }} Bedrooms
                    </span>
                    <span>
                      <i class="bi bi-droplet"></i>
                      {{ $property->bathrooms }} Bathrooms
                    </span>
                  </div>
                  <div class="prop-price">
                    <div>
                      <b>{{ number_format($property->price, 2) }} JOD</b>
                    </div>
                  </div>
                </div>
              </article>
            </div>
            @empty
            <div class="col-12 text-center py-5">
              <i class="bi bi-house-slash fs-1 text-muted"></i>
              <h5 class="mt-3">No properties found</h5>
              <p class="text-muted">Try changing your filters to find more homes.</p>
              <a href="{{ route('seeker.search') }}" class="btn btn-outline-primary mt-2">
                Clear filters
              </a>
            </div>
            @endforelse
          </div>
        </section>
        <!-- ===== Favorites ===== -->
        <section
          class="tab-pane fade"
          id="tab-favorites"
          role="tabpanel"
          aria-labelledby="favorites-tab"
          tabindex="0">
          <p class="small text-muted mb-3">2 saved homes</p>
          <!-- Cards: homes the seeker saved with the heart button -->
          <div class="row g-4" id="favoritesGrid">
          </div>
        </section>
      </div>
    </div>
  </div>
</main>
<style>
    /* Image gallery only — other page styles remain unchanged */
    #allHomesGrid .prop-media { position: relative; overflow: hidden; }
    #allHomesGrid .property-carousel,
    #allHomesGrid .carousel-inner,
    #allHomesGrid .carousel-item { width: 100%; height: 100%; }
    #allHomesGrid .property-carousel-image { width: 100%; height: 100%; object-fit: cover; }
    #allHomesGrid .carousel-control-prev,
    #allHomesGrid .carousel-control-next {
        position: absolute;
        top: 50%; bottom: auto;
        transform: translateY(-50%);
        width: 38px; height: 38px;
        border-radius: 50%; border: 1px solid #e5dac9;
        background: #fffdf9; color: #526453;
        opacity: 1; display: flex; align-items: center; justify-content: center;
        font-size: 16px; z-index: 5;
        box-shadow: 0 3px 10px rgba(0,0,0,.08);
    }
    #allHomesGrid .carousel-control-prev { left: 16px; }
    #allHomesGrid .carousel-control-next { right: 16px; }
    #allHomesGrid .carousel-control-prev:hover,
    #allHomesGrid .carousel-control-next:hover { background: #f4efe6; }
</style>
@endsection
