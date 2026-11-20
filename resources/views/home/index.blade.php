@extends('layouts.app')

@section('title', "Makan — Find your home")

@section('content')
    <header class="site-header">
      <nav class="navbar navbar-expand-xl">
        <div class="container">
          <a class="brand" href="{{ route('home') }}">
            <span class="brand-mark">M</span><span>Makan</span>
          </a>
          <button
            class="navbar-toggler border-0"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#publicNav"
            aria-label="Menu"
          >
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="publicNav">
            <!-- One link per section, in page order -->
            <ul class="navbar-nav mx-auto gap-xl-1 landing-nav">
              <li class="nav-item">
                <a class="nav-link" href="#homes">Homes</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#problem">Why Makan</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#how">How it works</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#features">Features</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#who">Who it's for</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#owners">For owners</a>
              </li>
            </ul>
            <div
              class="d-flex flex-column flex-xl-row align-items-xl-center gap-2 py-3 py-xl-0"
            >
              @auth
                <a class="btn btn-outline-primary" href="{{ match (auth()->user()->role) {
                    'tenant' => route('seeker.search'),
                    'agent' => route('owner.dashboard'),
                    'admin' => route('admin.dashboard'),
                    default => route('home'),
                } }}">Go to my dashboard</a>
              @else
                <a class="btn btn-outline-primary" href="{{ route('auth.login') }}">Log in</a>
                <a class="btn btn-accent" href="{{ route('auth.register') }}">Start matching</a>
              @endauth
            </div>
          </div>
        </div>
      </nav>
    </header>

    <main>
      <!-- ===== Hero ===== -->
      <section class="hero" aria-labelledby="hero-title">
        <div class="container">
          <div class="row align-items-center g-4 g-lg-4 g-xl-5">
            <div class="col-lg-6">
              <p class="hero-kicker"><i class="bi bi-geo-alt"></i> TEMPORARY HOMES IN JORDAN</p>
              <h1 id="hero-title">Your next home,<br class="d-none d-xl-inline" /> <em>made to fit</em> your life.</h1>
              <p class="lead">
                Tell us why you're coming and what matters to you. We rank
                homes by how well they match you, with the true
                monthly cost up front.
              </p>
              <div
                class="d-flex flex-wrap gap-2 justify-content-center justify-content-lg-start"
              >
                <a
                  class="btn btn-accent btn-lg"
                  href="{{ route('auth.register') }}"
                >Start matching</a>
                <a
                  class="btn btn-outline-primary btn-lg"
                  href="{{ route('seeker.search') }}"
                >Browse homes</a>
              </div>
              <div class="hero-trust">
                <span
                  ><i class="bi bi-patch-check-fill"></i>
                  <span>Verified owners</span
                ></span>
                <span
                  ><i class="bi bi-arrow-repeat"></i>
                  <span>Listings refreshed weekly</span
                ></span>
                <span
                  ><i class="bi bi-gift"></i>
                  <span>Free for home seekers</span
                ></span>
              </div>
            </div>

            <div class="col-lg-6">
              <div class="hero-visual">
                <img class="hero-photo" src="{{ asset('assets/images/hero.jpg') }}" alt="Contemporary home with warm stone finishes and landscaped entry" width="1450" height="962" fetchpriority="high" />
                <article class="bt-card property-card">
                  <div
                    class="p-3 d-flex justify-content-between align-items-center gap-3"
                  >
                    <div>
                      <span class="hero-card-label"><i class="bi bi-patch-check"></i> Verified listing</span>
                      <h3 class="h6 mb-1 text-body">
                        Studio — Al Jubeiha
                      </h3>
                      <div class="small text-muted mb-1">
                        <i class="bi bi-geo-alt"></i>
                        <span>Al Jubeiha, Amman</span>
                      </div>
                      <div>
                        <strong>265 JOD</strong>
                        <span
                          class="small text-muted"
                        >per month, bills included</span>
                      </div>
                    </div>
                    <div class="score-ring" style="--value: 92">
                      <div class="score-ring__inner">
                        92<small>match</small>
                      </div>
                    </div>
                  </div>
                </article>
              </div>
            </div>
          </div>
        </div>
      </section>
      <!-- ===== Homes: each card opens the property page ===== -->
      <section class="section section-alt" id="homes">
        <div class="container">
          <div
            class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4"
          >
            <div>
              <span class="eyebrow">Homes on Makan</span>
              <h2 class="mb-1">Homes available now</h2>
              <p class="text-muted mb-0">
                Click any home to see photos, the full monthly cost, and the
                neighborhood.
              </p>
            </div>
          </div>

          <div class="home-filters mb-4" role="group" aria-label="Property type">
            <button class="btn btn-outline-primary" type="button" data-home-category="" aria-pressed="true">All</button>
            <button class="btn btn-outline-primary" type="button" data-home-category="studio" aria-pressed="false">Studio</button>
            <button class="btn btn-outline-primary" type="button" data-home-category="apartment" aria-pressed="false">Apartment</button>
            <button class="btn btn-outline-primary" type="button" data-home-category="shared-apartment" aria-pressed="false">Shared apartment</button>
            <button class="btn btn-outline-primary" type="button" data-home-category="vacation" aria-pressed="false">Vacation</button>
          </div>
          <p id="homesFilterStatus" class="text-muted" role="status"></p>

          <div class="row g-4" id="homesGrid">
            @foreach ($homes as $home)
              <x-property-card :home="$home" :layout="'landing'" />
            @endforeach
          </div>
        </div>
      </section>
      <!-- ===== Problem ===== -->
      <section class="section" id="problem">
        <div class="container">
          <div class="section-head">
            <span class="eyebrow">The problem</span>
            <h2>Why is finding temporary housing so hard?</h2>
            <p class="text-muted mb-0">
              Listing sites show photos and prices, but never answer the real
              question: does this place fit me?
            </p>
          </div>

          <div class="row g-4">
            <div class="col-md-4">
              <article class="bt-card feature-card h-100">
                <span class="feature-icon"><i class="bi bi-puzzle"></i></span>
                <h3 class="h5">No matching</h3>
                <p class="text-muted mb-0">
                  Thousands of listings, and none of them tell you if a place
                  fits your life.
                </p>
              </article>
            </div>
            <div class="col-md-4">
              <article class="bt-card feature-card h-100">
                <span class="feature-icon"><i class="bi bi-receipt"></i></span>
                <h3 class="h5">Hidden costs</h3>
                <p class="text-muted mb-0">
                  Rent is one number. Bills and internet show up later and break
                  your budget.
                </p>
              </article>
            </div>
            <div class="col-md-4">
              <article class="bt-card feature-card h-100">
                <span class="feature-icon"
                  ><i class="bi bi-shield-exclamation"></i
                ></span>
                <h3 class="h5">No trust</h3>
                <p class="text-muted mb-0">
                  Outdated ads, unverified owners, and no protection if the home
                  isn't what was shown.
                </p>
              </article>
            </div>
          </div>
        </div>
      </section>
      <!-- ===== How it works ===== -->
      <section class="section section-alt" id="how">
        <div class="container">
          <div class="section-head">
            <span class="eyebrow">How it works</span>
            <h2>Three questions, and your home finds you</h2>
            <p class="text-muted mb-0">
              Instead of scrolling hundreds of listings, tell us about yourself
              once.
            </p>
          </div>

          <div class="row g-4 steps">
            <div class="col-md-4">
              <div class="step">
                <span class="step-icon"
                  ><i class="bi bi-signpost-2"></i
                  ><span class="step-num">1</span></span
                >
                <h3 class="h5">Why are you coming?</h3>
                <p class="text-muted mb-0">
                  Study, work, tourism, treatment, or relocation. Pick one or
                  more.
                </p>
              </div>
            </div>
            <div class="col-md-4">
              <div class="step">
                <span class="step-icon"
                  ><i class="bi bi-geo-alt"></i
                  ><span class="step-num">2</span></span
                >
                <h3 class="h5">Where do you go every day?</h3>
                <p class="text-muted mb-0">
                  Your university, hospital, or office. Pick from the list or
                  drop a pin on the map.
                </p>
              </div>
            </div>
            <div class="col-md-4">
              <div class="step">
                <span class="step-icon"
                  ><i class="bi bi-sliders"></i
                  ><span class="step-num">3</span></span
                >
                <h3 class="h5">What matters most?</h3>
                <p class="text-muted mb-0">
                  Rank your priorities: price, distance, quiet, accessibility,
                  and more.
                </p>
              </div>
            </div>
          </div>

          <div class="how-result">
            <i class="bi bi-stars"></i>
            <span
              >That's it. We rank homes by how well they match you, and tell you why.</span
            >
          </div>
        </div>
      </section>
      <!-- ===== Features ===== -->
      <section class="section" id="features">
        <div class="container">
          <div class="section-head">
            <span class="eyebrow">Features</span>
            <h2>Everything you need to decide with confidence</h2>
            <p class="text-muted mb-0">
              Three things you won't find together anywhere else.
            </p>
          </div>

          <!-- 1. Matching -->
          <div class="row align-items-center g-4 g-lg-4 g-xl-5 feature-row">
            <div class="col-lg-6">
              <span class="feature-tag"
                ><i class="bi bi-stars"></i
                ><span>Smart matching</span
              ></span>
              <h3>Know why a home fits you</h3>
              <p class="text-muted">
                Every home gets a match score based on your own priorities, with
                a clear reason behind each number.
              </p>
              <ul class="check-list">
                <li>
                  <i class="bi bi-check2-circle"></i
                  ><span>Homes ranked from best fit to least</span>
                </li>
                <li>
                  <i class="bi bi-check2-circle"></i
                  ><span>Homes in the city and area you chose</span>
                </li>
                <li>
                  <i class="bi bi-check2-circle"></i
                  ><span>Alerts as soon as a high-match home is listed</span>
                </li>
              </ul>
            </div>
            <div class="col-lg-6">
              <div class="bt-card visual-card">
                <div
                  class="d-flex justify-content-between align-items-center mb-3"
                >
                  <strong>Studio — Al Jubeiha</strong>
                  <div class="score-ring" style="--value: 92">
                    <div class="score-ring__inner">
                      92<small>match</small>
                    </div>
                  </div>
                </div>
                <div class="small d-grid gap-2">
                  <div>
                    <i class="bi bi-check-circle-fill ic-ok"></i>
                    <span>In Al Jubeiha, the area you chose</span>
                  </div>
                  <div>
                    <i class="bi bi-check-circle-fill ic-ok"></i>
                    <span>Furnished, as you asked</span>
                  </div>
                  <div>
                    <i class="bi bi-check-circle-fill ic-ok"></i>
                    <span>Elevator for easy access</span>
                  </div>
                </div>
                <hr />
                <div class="small d-grid gap-2">
                  <div>
                    <i class="bi bi-check-circle-fill ic-ok"></i>
                    <span>265 JOD a month, within your budget</span>
                  </div>
                  <div>
                    <i class="bi bi-exclamation-circle-fill ic-warn"></i>
                    <span>No parking space</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- 2. True cost (visual on the opposite side) -->
          <div class="row align-items-center g-4 g-lg-4 g-xl-5 feature-row">
            <div class="col-lg-6 order-lg-2">
              <span class="feature-tag"
                ><i class="bi bi-receipt"></i
                ><span>True cost</span
              ></span>
              <h3>One number, no surprises</h3>
              <p class="text-muted">
                We add rent, bills, and internet together, so you know what
                you'll actually pay each month.
              </p>
              <ul class="check-list">
                <li>
                  <i class="bi bi-check2-circle"></i
                  ><span>Full monthly cost on every home</span>
                </li>
                <li>
                  <i class="bi bi-check2-circle"></i
                  ><span>Compare neighborhoods on the same math</span>
                </li>
              </ul>
            </div>
            <div class="col-lg-6 order-lg-1">
              <div class="bt-card visual-card">
                <strong class="d-block mb-2">Monthly cost</strong>
                <div class="cost-row">
                  <span>Rent</span
                  ><span>220 JOD</span>
                </div>
                <div class="cost-row">
                  <span
                    ><span>Electricity &amp; water</span
                    ><span class="badge-soft">estimate</span></span
                  ><span>30 JOD</span>
                </div>
                <div class="cost-row">
                  <span>Internet</span
                  ><span>15 JOD</span>
                </div>
                <div class="cost-total">
                  <span>Total</span
                  ><span>265 JOD</span>
                </div>
              </div>
            </div>
          </div>

          <!-- 3. Trust -->
          <div class="row align-items-center g-4 g-lg-4 g-xl-5 feature-row">
            <div class="col-lg-6">
              <span class="feature-tag"
                ><i class="bi bi-shield-check"></i
                ><span>Trust &amp; safety</span
              ></span>
              <h3>What you see is what you get</h3>
              <p class="text-muted">
                Verified owners, fresh listings, and no fees for home seekers.
                You agree the rent with the owner and pay them directly.
              </p>
              <ul class="check-list">
                <li>
                  <i class="bi bi-check2-circle"></i
                  ><span>Verified badge after admin review</span>
                </li>
                <li>
                  <i class="bi bi-check2-circle"></i
                  ><span>Listings disappear if not confirmed within a week</span>
                </li>
                <li>
                  <i class="bi bi-check2-circle"></i
                  ><span>Chat with owners directly on Makan</span>
                </li>
              </ul>
            </div>
            <div class="col-lg-6">
              <div class="bt-card visual-card">
                <ul class="timeline">
                  <li>
                    <span class="tl-icon is-done"
                      ><i class="bi bi-patch-check"></i></span
                    ><span>Owner verified</span>
                  </li>
                  <li>
                    <span class="tl-icon is-done"
                      ><i class="bi bi-arrow-repeat"></i></span
                    ><span>Listing confirmed 2 days ago</span>
                  </li>
                  <li>
                    <span class="tl-icon is-done"
                      ><i class="bi bi-gift"></i></span
                    ><span>No fees for home seekers</span>
                  </li>
                  <li>
                    <span class="tl-icon"
                      ><i class="bi bi-house-check"></i></span
                    ><span
                      class="text-muted"
                    >You pay the rent directly to the owner</span>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </section>
      <!-- ===== Who it is for ===== -->
      <section class="section section-alt" id="who">
        <div class="container">
          <div class="section-head">
            <span class="eyebrow">Who is Makan for?</span>
            <h2>Whatever brings you here, there's a home that fits</h2>
            <p class="text-muted mb-0">
              You tell us why you're coming, and we suggest the right
              priorities. Change them anytime.
            </p>
          </div>

          <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-5 g-3">
            <div class="col">
              <div class="purpose-card">
                <span class="purpose-icon"
                  ><i class="bi bi-mortarboard"></i
                ></span>
                <h3 class="h6">Study</h3>
                <p>
                  Near your university, on a student budget, with the option to
                  share.
                </p>
              </div>
            </div>
            <div class="col">
              <div class="purpose-card">
                <span class="purpose-icon"
                  ><i class="bi bi-briefcase"></i
                ></span>
                <h3 class="h6">Work</h3>
                <p>
                  Close to work, quiet and comfortable, ready for a longer stay.
                </p>
              </div>
            </div>
            <div class="col">
              <div class="purpose-card">
                <span class="purpose-icon"><i class="bi bi-camera"></i></span>
                <h3 class="h6">Tourism</h3>
                <p>Near the sights, ready right away, for short stays.</p>
              </div>
            </div>
            <div class="col">
              <div class="purpose-card">
                <span class="purpose-icon"><i class="bi bi-hospital"></i></span>
                <h3 class="h6">Medical care</h3>
                <p>Near the hospital, easy access, and room for companions.</p>
              </div>
            </div>
            <div class="col">
              <div class="purpose-card">
                <span class="purpose-icon"><i class="bi bi-truck"></i></span>
                <h3 class="h6">Relocation</h3>
                <p>
                  Moving between governorates, with clear costs and services
                  nearby.
                </p>
              </div>
            </div>
          </div>

          <p class="text-center text-muted small mt-4 mb-0">
            <i class="bi bi-sliders"></i> <span>Reason not listed? Choose “Other” and set your own priorities.</span>
          </p>
        </div>
      </section>
      <!-- ===== For owners ===== -->
      <section class="section" id="owners">
        <div class="container">
          <div class="row align-items-center g-4 g-lg-4 g-xl-5">
            <div class="col-lg-6">
              <span class="eyebrow">For owners &amp; agents</span>
              <h2>Have a place to rent? Get it to the right person</h2>
              <p class="text-muted">
                Instead of dozens of calls from people who don't fit, your home
                reaches the ones it actually suits.
              </p>
              <ul class="check-list mb-4">
                <li>
                  <i class="bi bi-patch-check"></i
                  ><span>A verified badge that builds visitor trust</span>
                </li>
                <li>
                  <i class="bi bi-shield-check"></i
                  ><span>Write your listing in seconds with the AI assistant</span>
                </li>
                <li>
                  <i class="bi bi-graph-up"></i
                  ><span>Analytics: who viewed your home and how well it matches them</span>
                </li>
                <li>
                  <i class="bi bi-star"></i><span>Featured listings put your home at the top</span>
                </li>
                <li>
                  <i class="bi bi-tag"></i
                  ><span>Simple monthly packages — no commission.</span>
                </li>
              </ul>
              <a
                class="btn btn-primary"
                href="{{ route('auth.register', ['role' => 'owner']) }}"
              >List your property</a>
            </div>

            <div class="col-lg-6">
              <div class="bt-card visual-card">
                <div class="stat-tiles">
                  <div class="stat-tile">
                    <b>1,240</b><span>Views</span>
                  </div>
                  <div class="stat-tile">
                    <b>18</b><span>Requests</span>
                  </div>
                  <div class="stat-tile">
                    <b>84%</b><span>Avg. match</span>
                  </div>
                </div>
                <div class="listing-row">
                  <span class="listing-thumb img-placeholder"></span>
                  <div class="flex-grow-1 min-w-0">
                    <div
                      class="fw-semibold small"
                    >Studio — Al Jubeiha</div>
                    <span class="badge-featured"
                      ><i class="bi bi-star-fill"></i>
                      <span>Featured</span
                    ></span>
                  </div>
                  <div class="text-center">
                    <div
                      class="text-muted"
                      style="font-size: 0.7rem"
                    >Still available?</div>
                    <button
                      type="button"
                      class="btn btn-sm btn-outline-primary"
                    >Confirm</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ===== Final call to action ===== -->
      <section>
        <div class="container">
          <div class="cta-card">
            <h2>Ready to find your home?</h2>
            <p>
              Just 3 questions, and you'll see your best-fit homes in a minute.
            </p>
            <div class="d-flex flex-wrap justify-content-center gap-2">
              <a
                class="btn btn-accent btn-lg"
                href="{{ route('auth.register') }}"
              >Start matching</a>
              <a
                class="btn btn-outline-light btn-lg"
                href="{{ route('auth.register', ['role' => 'owner']) }}"
              >I own a property</a>
            </div>
          </div>
        </div>
      </section>
    </main>

    <footer class="site-footer">
      <div class="container">
        <div class="row g-4">
          <div class="col-lg-5">
            <a class="brand mb-3" href="{{ route('home') }}">
              <span class="brand-mark">M</span><span>Makan</span>
            </a>
            <p class="mb-0">Smart matching for temporary housing in Jordan.</p>
          </div>
          <div class="col-6 col-lg-3 offset-lg-1">
            <h6>Platform</h6>
            <ul class="list-unstyled d-grid gap-2 mb-0">
              <li><a href="{{ route('home') }}#how">How it works</a></li>
              <li><a href="{{ route('home') }}#features">Features</a></li>
              <li><a href="{{ route('home') }}#owners">For owners</a></li>
            </ul>
          </div>
          <div class="col-6 col-lg-3">
            <h6>Company</h6>
            <ul class="list-unstyled d-grid gap-2 mb-0">
              <li><a href="#">About us</a></li>
              <li><a href="#">Contact</a></li>
              <li><a href="#">Terms of service</a></li>
              <li><a href="#">Privacy policy</a></li>
            </ul>
          </div>
        </div>
        <div
          class="footer-bottom d-flex flex-wrap justify-content-between gap-2"
        >
          <span>© 2026 Makan. All rights reserved.</span>
        </div>
      </div>
    </footer>
@endsection
