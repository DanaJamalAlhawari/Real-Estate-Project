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
            aria-label="Account menu, Sara Khaled">
            <span class="avatar">S</span>
          </button>
          <ul class="dropdown-menu dropdown-menu-end shadow-sm">
            <li>
              <h6 class="dropdown-header">Sara Khaled</h6>
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
    </div>
    <a href="{{ route('seeker.profile') }}" class="btn btn-outline-primary">
      <i class="bi bi-sliders"></i>
      <span>Edit my priorities</span>
    </a>
  </div>
  <div class="seeker-results-layout">
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
          <input class="form-control" id="filterBudget" name="max" type="number" min="0" step="1" placeholder="Any budget" />
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
          <button class="btn btn-accent" type="submit">Apply filters</button>
          <a class="btn btn-link" href="{{ route('seeker.search') }}">Clear</a>
        </div>
      </form>
    </aside>
    <div class="seeker-results">
      <!-- 2. Three views: all homes / matches for you / favorites -->
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
        <!-- <li class="nav-item" role="presentation">
          <button
            class="nav-link"
            id="matches-tab"
            data-bs-toggle="tab"
            data-bs-target="#tab-matches"
            type="button"
            role="tab"
            aria-controls="tab-matches"
            aria-selected="false">
            <i class="bi bi-stars"></i>
            <span class="text-start">
              <b>Matches for you</b>
              <small>Ranked by your priorities</small>
            </span>
            <span class="count">6</span>
          </button> -->
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
          <!-- Cards: every listing; featured (paid) ones first -->
          <div class="row g-4" id="allHomesGrid">
            @forelse ($properties as $property)
            <div class="col-sm-6 col-xxl-4">
              <article class="prop-card">
                {{-- Property Images --}}



                <div class="prop-media ph ph-1">
                  @if ($property->images->isNotEmpty())
                  <img class="property-slide"
                    src="{{ $property->images->first()->image_url }}"
                    alt="{{ $property->title }}"
                    data-images='@json($property->images->pluck("image_url")->values())'
                    data-index="0"
                    style="width:100%;height:100%;object-fit:cover;">

                  @if ($property->images->count() > 1)
                  <button type="button" class="gallery-prev" aria-label="Previous image">
                    <i class="bi bi-chevron-left"></i>
                  </button>
                  <button type="button" class="gallery-next" aria-label="Next image">
                    <i class="bi bi-chevron-right"></i>
                  </button>
                  @endif
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
  #allHomesGrid .prop-media {
    position: relative;
    overflow: hidden;
  }

  #allHomesGrid .gallery-prev,
  #allHomesGrid .gallery-next {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 38px;
    height: 38px;
    border-radius: 50%;
    border: 1px solid #e5dac9;
    background: #fffdf9;
    color: #526453;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    cursor: pointer;
    z-index: 5;
    box-shadow: 0 3px 10px rgba(0, 0, 0, .08);
  }


  #allHomesGrid .gallery-prev {
    left: 16px;
  }

  #allHomesGrid .gallery-next {
    right: 16px;
  }

  #allHomesGrid .gallery-prev:hover,
  #allHomesGrid .gallery-next:hover {
    background: #f4efe6;
  }
</style>


<script>
  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('#allHomesGrid .prop-media').forEach(function(media) {
      const image = media.querySelector('.property-slide');
      const prevBtn = media.querySelector('.gallery-prev');
      const nextBtn = media.querySelector('.gallery-next');

      if (!image) return;

      let images = [];

      try {
        images = JSON.parse(image.dataset.images || '[]');
      } catch (error) {
        console.error('Invalid property images:', error);
        return;
      }

      if (images.length <= 1) {
        if (prevBtn) prevBtn.style.display = 'none';
        if (nextBtn) nextBtn.style.display = 'none';
        return;
      }

      let currentIndex = 0;

      function changeImage(direction) {
        currentIndex = (currentIndex + direction + images.length) % images.length;
        image.src = images[currentIndex];
      }

      if (prevBtn) {
        prevBtn.addEventListener('click', function(event) {
          event.preventDefault();
          event.stopPropagation();
          changeImage(-1);
        });
      }

      if (nextBtn) {
        nextBtn.addEventListener('click', function(event) {
          event.preventDefault();
          event.stopPropagation();
          changeImage(1);
        });
      }
    });
  });
</script>


@endsection