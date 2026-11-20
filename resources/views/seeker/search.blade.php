@extends('layouts.app')

@section('title', "Makan — Discover")
@section('roleStyle', 'seeker')
@section('bodyAttributes') data-role="seeker" data-page="search"@endsection

@section('content')
    <!-- ===== Navbar ===== -->
    <header class="site-header app-nav">
      <nav class="navbar navbar-expand-lg" aria-label="Main">
        <div class="container">
          <a class="brand" href="{{ route('seeker.search') }}"
            ><span class="brand-mark">M</span><span>Makan</span></a
          >

          <!-- Messages, notifications + user menu (stay visible on phones) -->
          <div class="d-flex align-items-center gap-1 ms-auto order-lg-last">
            <!-- Messages: icon with unread count -->
            <a
              class="icon-btn"
              href="{{ route('seeker.chat') }}"
              aria-label="Messages, 2 unread"
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
                    href="{{ route('seeker.property') }}"
                  >
                    <span
                      class="kpi-icon flex-shrink-0"
                      style="width: 34px; height: 34px; font-size: 1rem"
                      ><i class="bi bi-stars"></i
                    ></span>
                    <span class="small">
                      <span class="d-block"
                        >A new home just listed in Al Jubeiha with a 92%
                        match</span
                      >
                      <span class="text-muted" style="font-size: 0.72rem"
                        >10 minutes ago</span
                      >
                    </span>
                  </a>
                  <a
                    class="dropdown-item d-flex gap-2 py-2 text-wrap"
                    href="{{ route('seeker.bookings') }}"
                  >
                    <span
                      class="kpi-icon flex-shrink-0"
                      style="width: 34px; height: 34px; font-size: 1rem"
                      ><i class="bi bi-check2-circle"></i
                    ></span>
                    <span class="small">
                      <span class="d-block"
                        >Abu Ahmad accepted your booking</span
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
                aria-label="Account menu, Sara Khaled"
              >
                <span class="avatar">S</span>
              </button>
              <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li><h6 class="dropdown-header">Sara Khaled</h6></li>
                <li><a class="dropdown-item" href="{{ route('seeker.profile') }}">My profile</a></li>
                <li><hr class="dropdown-divider" /></li>
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
              aria-label="Menu"
            >
              <span class="navbar-toggler-icon"></span>
            </button>
          </div>

          <!-- Page links + search, grouped in one capsule -->
          <div class="collapse navbar-collapse" id="appNav">
            <div class="nav-group">
              <ul class="navbar-nav">
                <li class="nav-item">
                  <a class="nav-link active" aria-current="page" href="{{ route('seeker.search') }}"
                    ><i class="bi bi-stars"></i><span>Discover</span></a
                  >
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="{{ route('seeker.bookings') }}"
                    ><i class="bi bi-calendar-check"></i><span>My bookings</span
                    ><span class="side-badge">1</span></a
                  >
                </li>
              </ul>
              <span class="nav-group-divider" aria-hidden="true"></span>
              <!-- Search: plain GET form, opens the Discover page -->
              <form
                class="nav-search"
                action="{{ route('seeker.search') }}"
                method="get"
                role="search"
              >
                <i class="bi bi-search" aria-hidden="true"></i>
                <input
                  type="search"
                  class="form-control"
                  name="q"
                  placeholder="Search by area..."
                  aria-label="Search homes by neighborhood"
                />
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
                  <input class="form-check-input" type="radio" name="category" value="" checked />
                  <span class="form-check-label">All</span>
                </label>
                <label class="form-check">
                  <input class="form-check-input" type="radio" name="category" value="studio" />
                  <span class="form-check-label">Studio</span>
                </label>
                <label class="form-check">
                  <input class="form-check-input" type="radio" name="category" value="apartment" />
                  <span class="form-check-label">Apartment</span>
                </label>
                <label class="form-check">
                  <input class="form-check-input" type="radio" name="category" value="shared-apartment" />
                  <span class="form-check-label">Shared apartment</span>
                </label>
                <label class="form-check">
                  <input class="form-check-input" type="radio" name="category" value="vacation" />
                  <span class="form-check-label">Vacation</span>
                </label>
              </div>
            </fieldset>
            <div class="mb-3">
              <label class="form-label" for="filterArea">Area</label>
              <input class="form-control" id="filterArea" name="q" type="search" placeholder="e.g. Al Jubeiha" />
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
            class="nav-link"
            id="all-tab"
            data-bs-toggle="tab"
            data-bs-target="#tab-all"
            type="button"
            role="tab"
            aria-controls="tab-all"
            aria-selected="false"
          >
            <i class="bi bi-houses"></i>
            <span class="text-start">
              <b>All homes</b>
              <small>Everything owners have listed</small>
            </span>
            <span class="count">6</span>
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button
            class="nav-link active"
            id="matches-tab"
            data-bs-toggle="tab"
            data-bs-target="#tab-matches"
            type="button"
            role="tab"
            aria-controls="tab-matches"
            aria-selected="true"
          >
            <i class="bi bi-stars"></i>
            <span class="text-start">
              <b>Matches for you</b>
              <small>Ranked by your priorities</small>
            </span>
            <span class="count">6</span>
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
            aria-selected="false"
          >
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
        <!-- ===== Matches for you ===== -->
        <section
          class="tab-pane fade show active"
          id="tab-matches"
          role="tabpanel"
          aria-labelledby="matches-tab"
          tabindex="0"
        >
          <p class="small text-muted mb-3">
            6 homes · ranked by how well they match your priorities
          </p>

          <!-- Visitors (not logged in): shown instead of the cards by JS -->
          <div class="guest-prompt panel text-center" id="matchesGuestPrompt" hidden>
            <span class="feature-icon"><i class="bi bi-stars"></i></span>
            <h2 class="h5">See the homes that fit you</h2>
            <p class="text-muted mb-3">
              Create a free account and answer three quick questions. We'll rank
              every home by how well it matches your priorities.
            </p>
            <div class="d-flex flex-wrap justify-content-center gap-2">
              <a href="{{ route('auth.register') }}" class="btn btn-accent">Create account</a>
              <a href="{{ route('auth.login') }}" class="btn btn-outline-primary">Log in</a>
            </div>
          </div>

          <!-- Cards: ranked by match -->
            <div class="row g-4" id="matchesGrid">
              <div class="col-sm-6 col-xxl-4">
                <article class="prop-card">
                  <button
                    type="button"
                    class="fav-btn is-on"
                    aria-label="Remove from favorites"
                    aria-pressed="true"
                  >
                    <i class="bi bi-heart-fill"></i>
                  </button>
                  <a
                    href="{{ route('seeker.property') }}" data-property-link
                    class="prop-media ph ph-1"
                    tabindex="-1"
                    aria-hidden="true"
                  >
                    <span class="ph-icon"
                      ><i class="bi bi-house-door"></i
                    ></span>
                    <span class="badge-verified"
                      ><i class="bi bi-patch-check-fill"></i>Verified</span
                    >
                    <span class="badge-featured"
                      ><i class="bi bi-star-fill"></i>Featured</span
                    >
                    <div class="score-ring" style="--value: 92">
                      <div class="score-ring__inner">
                        92<small>match</small>
                      </div>
                    </div>
                  </a>
                  <div class="prop-body">
                    <a href="{{ route('seeker.property') }}" data-property-link class="prop-title"
                      >Studio — Al Jubeiha</a
                    >
                    <span class="prop-match">High match</span>
                    <div class="prop-meta">
                      <span><i class="bi bi-geo-alt"></i> Al Jubeiha, Amman</span>
                      <span
                        ><i class="bi bi-arrows-angle-expand"></i> 45 m²</span
                      >
                      <span><i class="bi bi-lamp"></i> Furnished</span>
                    </div>
                    <div class="prop-price">
                      <div><b>265 JOD</b> <span>per month, bills included</span></div>
                      <label class="compare-check"
                        ><input
                          type="checkbox"
                          class="form-check-input m-0"
                          checked
                        />Compare</label
                      >
                    </div>
                  </div>
                </article>
              </div>

              <div class="col-sm-6 col-xxl-4">
                <article class="prop-card">
                  <button
                    type="button"
                    class="fav-btn"
                    aria-label="Add to favorites"
                    aria-pressed="false"
                  >
                    <i class="bi bi-heart"></i>
                  </button>
                  <a
                    href="{{ route('seeker.property') }}" data-property-link
                    class="prop-media ph ph-2"
                    tabindex="-1"
                    aria-hidden="true"
                  >
                    <span class="ph-icon"
                      ><i class="bi bi-house-door"></i
                    ></span>
                    <span class="badge-verified"
                      ><i class="bi bi-patch-check-fill"></i>Verified</span
                    >
                    <div class="score-ring" style="--value: 86">
                      <div class="score-ring__inner">
                        86<small>match</small>
                      </div>
                    </div>
                  </a>
                  <div class="prop-body">
                    <a href="{{ route('seeker.property') }}" data-property-link class="prop-title"
                      >2-bedroom apartment — Tla Al Ali</a
                    >
                    <span class="prop-match">High match</span>
                    <div class="prop-meta">
                      <span><i class="bi bi-geo-alt"></i> Tla Al Ali, Amman</span>
                      <span
                        ><i class="bi bi-arrows-angle-expand"></i> 95 m²</span
                      >
                      <span><i class="bi bi-lamp"></i> Unfurnished</span>
                    </div>
                    <div class="prop-price">
                      <div><b>395 JOD</b> <span>per month, bills included</span></div>
                      <label class="compare-check"
                        ><input
                          type="checkbox"
                          class="form-check-input m-0"
                          checked
                        />Compare</label
                      >
                    </div>
                  </div>
                </article>
              </div>

              <div class="col-sm-6 col-xxl-4">
                <article class="prop-card">
                  <button
                    type="button"
                    class="fav-btn"
                    aria-label="Add to favorites"
                    aria-pressed="false"
                  >
                    <i class="bi bi-heart"></i>
                  </button>
                  <a
                    href="{{ route('seeker.property') }}" data-property-link
                    class="prop-media ph ph-3"
                    tabindex="-1"
                    aria-hidden="true"
                  >
                    <span class="ph-icon"
                      ><i class="bi bi-house-door"></i
                    ></span>
                    <div class="score-ring" style="--value: 81">
                      <div class="score-ring__inner">
                        81<small>match</small>
                      </div>
                    </div>
                  </a>
                  <div class="prop-body">
                    <a href="{{ route('seeker.property') }}" data-property-link class="prop-title"
                      >Room in a shared apartment — Al Jubeiha</a
                    >
                    <span class="prop-match">High match</span>
                    <div class="prop-meta">
                      <span><i class="bi bi-geo-alt"></i> Al Jubeiha, Amman</span>
                      <span
                        ><i class="bi bi-arrows-angle-expand"></i> 18 m²</span
                      >
                      <span><i class="bi bi-lamp"></i> Furnished</span>
                    </div>
                    <div class="prop-price">
                      <div><b>170 JOD</b> <span>per month, bills included</span></div>
                      <label class="compare-check"
                        ><input
                          type="checkbox"
                          class="form-check-input m-0"
                        />Compare</label
                      >
                    </div>
                  </div>
                </article>
              </div>

              <div class="col-sm-6 col-xxl-4">
                <article class="prop-card">
                  <button
                    type="button"
                    class="fav-btn is-on"
                    aria-label="Remove from favorites"
                    aria-pressed="true"
                  >
                    <i class="bi bi-heart-fill"></i>
                  </button>
                  <a
                    href="{{ route('seeker.property') }}" data-property-link
                    class="prop-media ph ph-4"
                    tabindex="-1"
                    aria-hidden="true"
                  >
                    <span class="ph-icon"
                      ><i class="bi bi-house-door"></i
                    ></span>
                    <span class="badge-verified"
                      ><i class="bi bi-patch-check-fill"></i>Verified</span
                    >
                    <span class="badge-featured"
                      ><i class="bi bi-star-fill"></i>Featured</span
                    >
                    <div class="score-ring" style="--value: 74">
                      <div class="score-ring__inner">
                        74<small>match</small>
                      </div>
                    </div>
                  </a>
                  <div class="prop-body">
                    <a href="{{ route('seeker.property') }}" data-property-link class="prop-title"
                      >1-bedroom apartment — Shmeisani</a
                    >
                    <span class="prop-match">Good match</span>
                    <div class="prop-meta">
                      <span><i class="bi bi-geo-alt"></i> Shmeisani, Amman</span>
                      <span
                        ><i class="bi bi-arrows-angle-expand"></i> 70 m²</span
                      >
                      <span><i class="bi bi-lamp"></i> Furnished</span>
                    </div>
                    <div class="prop-price">
                      <div><b>440 JOD</b> <span>per month, bills included</span></div>
                      <label class="compare-check"
                        ><input
                          type="checkbox"
                          class="form-check-input m-0"
                        />Compare</label
                      >
                    </div>
                  </div>
                </article>
              </div>

              <div class="col-sm-6 col-xxl-4">
                <article class="prop-card">
                  <button
                    type="button"
                    class="fav-btn"
                    aria-label="Add to favorites"
                    aria-pressed="false"
                  >
                    <i class="bi bi-heart"></i>
                  </button>
                  <a
                    href="{{ route('seeker.property') }}" data-property-link
                    class="prop-media ph ph-5"
                    tabindex="-1"
                    aria-hidden="true"
                  >
                    <span class="ph-icon"
                      ><i class="bi bi-house-door"></i
                    ></span>
                    <span class="badge-verified"
                      ><i class="bi bi-patch-check-fill"></i>Verified</span
                    >
                    <div class="score-ring" style="--value: 69">
                      <div class="score-ring__inner">
                        69<small>match</small>
                      </div>
                    </div>
                  </a>
                  <div class="prop-body">
                    <a href="{{ route('seeker.property') }}" data-property-link class="prop-title"
                      >Studio — Sweileh</a
                    >
                    <span class="prop-match">Good match</span>
                    <div class="prop-meta">
                      <span><i class="bi bi-geo-alt"></i> Sweileh, Amman</span>
                      <span
                        ><i class="bi bi-arrows-angle-expand"></i> 40 m²</span
                      >
                      <span><i class="bi bi-lamp"></i> Unfurnished</span>
                    </div>
                    <div class="prop-price">
                      <div><b>220 JOD</b> <span>per month, bills included</span></div>
                      <label class="compare-check"
                        ><input
                          type="checkbox"
                          class="form-check-input m-0"
                        />Compare</label
                      >
                    </div>
                  </div>
                </article>
              </div>

              <div class="col-sm-6 col-xxl-4">
                <article class="prop-card">
                  <button
                    type="button"
                    class="fav-btn"
                    aria-label="Add to favorites"
                    aria-pressed="false"
                  >
                    <i class="bi bi-heart"></i>
                  </button>
                  <a
                    href="{{ route('seeker.property') }}" data-property-link
                    class="prop-media ph ph-6"
                    tabindex="-1"
                    aria-hidden="true"
                  >
                    <span class="ph-icon"
                      ><i class="bi bi-house-door"></i
                    ></span>
                    <div class="score-ring" style="--value: 58">
                      <div class="score-ring__inner">
                        58<small>match</small>
                      </div>
                    </div>
                  </a>
                  <div class="prop-body">
                    <a href="{{ route('seeker.property') }}" data-property-link class="prop-title"
                      >3-bedroom apartment — Khalda</a
                    >
                    <span class="prop-match">Fair match</span>
                    <div class="prop-meta">
                      <span><i class="bi bi-geo-alt"></i> Khalda, Amman</span>
                      <span
                        ><i class="bi bi-arrows-angle-expand"></i> 150 m²</span
                      >
                      <span><i class="bi bi-lamp"></i> Unfurnished</span>
                    </div>
                    <div class="prop-price">
                      <div><b>595 JOD</b> <span>per month, bills included</span></div>
                      <label class="compare-check"
                        ><input
                          type="checkbox"
                          class="form-check-input m-0"
                        />Compare</label
                      >
                    </div>
                  </div>
                </article>
              </div>
            </div>
        </section>

        <!-- ===== All homes ===== -->
        <section
          class="tab-pane fade"
          id="tab-all"
          role="tabpanel"
          aria-labelledby="all-tab"
          tabindex="0"
        >
          <p class="small text-muted mb-3">
            6 homes · featured listings first
          </p>

          <!-- Cards: every listing; featured (paid) ones first -->
          <div class="row g-4" id="allHomesGrid">
            @foreach ($featuredHomes as $home)
              <x-property-card :home="$home" :layout="'seeker'" />
            @endforeach
          </div>
        </section>

        <!-- ===== Favorites ===== -->
        <section
          class="tab-pane fade"
          id="tab-favorites"
          role="tabpanel"
          aria-labelledby="favorites-tab"
          tabindex="0"
        >
          <p class="small text-muted mb-3">2 saved homes</p>

          <!-- Cards: homes the seeker saved with the heart button -->
          <div class="row g-4" id="favoritesGrid">
            @foreach ($defaultFavorites as $home)
              <x-property-card :home="$home" :layout="'seeker'" />
            @endforeach
          </div>
        </section>
      </div>
        </div>
      </div>
    </main>
@endsection
