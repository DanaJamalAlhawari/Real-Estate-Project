@extends('layouts.app')

@section('title', "Makan — Find your home")

@section('content')
    <header class="site-header">
      <nav class="navbar navbar-expand-xl">
        <div class="container">
          <a class="brand" href="{{ route('home') }}">
            <img class="brand-mark" src="{{ asset('assets/images/makan-mark.svg') }}" alt="" width="36" height="36"><span>Makan</span>
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
            <a class="btn btn-outline-primary" href="{{ route('home') }}#homes">All</a>
            @foreach (\App\Models\Property::CATEGORIES as $value => $label)
              <a @class(['btn btn-outline-primary', 'active' => request('category') === $value])
                 href="{{ route('home', ['category' => $value]) }}#homes">{{ $label }}</a>
            @endforeach
          </div>
          <div class="row g-4">
            @forelse ($properties as $property)
              <x-database-property-card :property="$property" :saved="in_array($property->id, $favoriteIds)" />
            @empty
              <p>No verified properties are available in this category.</p>
            @endforelse
          </div>
        </div>
      </section>
      <section class="section" id="problem">
        <div class="container"><h2>Why Makan</h2><p>Browse available, verified listings and contact the property owner in one place.</p></div>
      </section>
      <section class="section section-alt" id="how">
        <div class="container"><h2>How it works</h2><p>Filter listings by category, area, and budget. Open a listing to view photos, save it, or send a booking request.</p></div>
      </section>
      <section class="section" id="features">
        <div class="container"><h2>Features</h2><p>Save your favorites, message property owners, and follow the status of your bookings.</p></div>
      </section>
      <section class="section section-alt" id="who">
        <div class="container"><h2>Who it’s for</h2><p>Makan connects tenants with property owners.</p></div>
      </section>
      <section class="section" id="owners">
        <div class="container">
          <h2>For owners</h2>
          <p>Submit ownership documents for review, then manage your listings and incoming booking requests.</p>
          @guest
            <a class="btn btn-primary" href="{{ route('auth.register', ['role' => 'agent']) }}">Register as an agent</a>
          @endguest
        </div>
      </section>
    </main>

@endsection
