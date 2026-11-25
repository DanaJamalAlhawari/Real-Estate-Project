@extends('layouts.app')

@section('title', __('Makan — Find your home'))

@section('content')
    <header class="site-header">
      <nav class="navbar navbar-expand-xl">
        <div class="container">
          <x-language-switch />
          <a class="brand" href="{{ route('home') }}">
            <img class="brand-mark" src="{{ asset('assets/images/makan-mark.svg') }}" alt="" width="36" height="36"><span>Makan</span>
          </a>
          <button
            class="navbar-toggler border-0"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#publicNav"
            aria-label="{{ __('Menu') }}"
          >
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="publicNav">
            <!-- One link per section, in page order -->
            <ul class="navbar-nav mx-auto gap-xl-1 landing-nav">
              <li class="nav-item">
                <a class="nav-link" href="#homes">{{ __('Homes') }}</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#problem">{{ __('Why Makan') }}</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#how">{{ __('How it works') }}</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#features">{{ __('Features') }}</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#who">{{ __('Who it\'s for') }}</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#owners">{{ __('For owners') }}</a>
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
                } }}">{{ __('Go to my dashboard') }}</a>
              @else
                <a class="btn btn-outline-primary" href="{{ route('auth.login') }}">{{ __('Log in') }}</a>
                <a class="btn btn-accent" href="{{ route('auth.register') }}">{{ __('Start matching') }}</a>
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
              <p class="hero-kicker"><i class="bi bi-geo-alt"></i> {{ __('TEMPORARY HOMES IN JORDAN') }}</p>
              <h1 id="hero-title">{{ __('Your next home,') }}<br class="d-none d-xl-inline" /> <em>{{ __('made to fit') }}</em> {{ __('your life.') }}</h1>
              <p class="lead">
                {{ __('Tell us why you\'re coming and what matters to you. We rank homes by how well they match you, with the true monthly cost up front.') }}
              </p>
              <div
                class="d-flex flex-wrap gap-2 justify-content-center justify-content-lg-start"
              >
                <a
                  class="btn btn-accent btn-lg"
                  href="{{ route('auth.register') }}"
                >{{ __('Start matching') }}</a>
                <a
                  class="btn btn-outline-primary btn-lg"
                  href="{{ route('seeker.search') }}"
                >{{ __('Browse homes') }}</a>
              </div>
              <div class="hero-trust">
                <span
                  ><i class="bi bi-patch-check-fill"></i>
                  <span>{{ __('Verified owners') }}</span
                ></span>
                <span
                  ><i class="bi bi-arrow-repeat"></i>
                  <span>{{ __('Listings refreshed weekly') }}</span
                ></span>
                <span
                  ><i class="bi bi-gift"></i>
                  <span>{{ __('Free for home seekers') }}</span
                ></span>
              </div>
            </div>

            <div class="col-lg-6">
              <div class="hero-visual">
                <img class="hero-photo" src="{{ asset('assets/images/hero.jpg') }}" alt="{{ __('Contemporary home with warm stone finishes and landscaped entry') }}" width="1450" height="962" fetchpriority="high" />
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
              <span class="eyebrow">{{ __('Homes on Makan') }}</span>
              <h2 class="mb-1">{{ __('Homes available now') }}</h2>
              <p class="text-muted mb-0">
                {{ __('Click any home to see photos, the full monthly cost, and the neighborhood.') }}
              </p>
            </div>
          </div>

          <x-property-search :action="route('home')" :category="request('category')" fragment="#homes" placeholder="Search by property name or area" />
          <div class="home-filters mb-4" role="group" aria-label="{{ __('Property type') }}">
            <a class="btn btn-outline-primary" href="{{ route('home', request()->only('q')) }}#homes">{{ __('All') }}</a>
            @foreach (\App\Models\Property::CATEGORIES as $value => $label)
              <a @class(['btn btn-outline-primary', 'active' => request('category') === $value])
                 href="{{ route('home', ['category' => $value] + request()->only('q')) }}#homes">{{ __($label) }}</a>
            @endforeach
          </div>
          <div class="row g-4">
            @forelse ($properties as $property)
              <x-database-property-card :property="$property" :saved="in_array($property->id, $favoriteIds)" />
            @empty
              <p>{{ __('No verified properties are available in this category.') }}</p>
            @endforelse
          </div>
        <x-pagination :items="$properties" />
        </div>
      </section>
      <section class="section" id="problem">
        <div class="container"><h2>{{ __('Why Makan') }}</h2><p>{{ __('Browse available, verified listings and contact the property owner in one place.') }}</p></div>
      </section>
      <section class="section section-alt" id="how">
        <div class="container"><h2>{{ __('How it works') }}</h2><p>{{ __('Filter listings by category, area, and budget. Open a listing to view photos, save it, or send a booking request.') }}</p></div>
      </section>
      <section class="section" id="features">
        <div class="container"><h2>{{ __('Features') }}</h2><p>{{ __('Save your favorites, message property owners, and follow the status of your bookings.') }}</p></div>
      </section>
      <section class="section section-alt" id="who">
        <div class="container"><h2>{{ __('Who it’s for') }}</h2><p>{{ __('Makan connects tenants with property owners.') }}</p></div>
      </section>
      <section class="section" id="owners">
        <div class="container">
          <h2>{{ __('For owners') }}</h2>
          <p>{{ __('Submit ownership documents for review, then manage your listings and incoming booking requests.') }}</p>
          @guest
            <a class="btn btn-primary" href="{{ route('auth.register', ['role' => 'agent']) }}">{{ __('Register as an agent') }}</a>
          @endguest
        </div>
      </section>
    </main>

@endsection
