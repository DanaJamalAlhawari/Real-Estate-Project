@extends('layouts.app')

@section('title', "Makan — Log in")

@section('content')
    <main class="auth-wrap">
      <!-- Colored side panel (desktop only) -->
      <aside class="auth-side">
        <a class="brand" href="{{ route('home') }}"
          ><span class="brand-mark">M</span><span>Makan</span
        ></a>
        <div>
          <h2>Your next home is waiting</h2>
          <ul class="list-unstyled d-grid gap-3 mt-4 mb-0">
            <li class="d-flex gap-2">
              <i class="bi bi-stars"></i
              ><span>A match score on every home, based on your priorities</span>
            </li>
            <li class="d-flex gap-2">
              <i class="bi bi-receipt"></i
              ><span>The full monthly cost before you decide</span>
            </li>
            <li class="d-flex gap-2">
              <i class="bi bi-gift"></i
              ><span>Free for home seekers — no commission</span>
            </li>
          </ul>
        </div>
        <small class="opacity-75">Verified homes across Jordan</small>
      </aside>

      <!-- Form -->
      <section class="auth-main">
        <div class="auth-top">
          <a class="brand" href="{{ route('home') }}"
            ><span class="brand-mark">M</span><span>Makan</span
          ></a>
        </div>

        <!-- After log in (decided by the backend): seekers go to Discover,
             owners to their dashboard, admins to the admin dashboard -->
        <form
          class="auth-form"
          id="loginForm"
          method="POST"
          action="{{ route('auth.login.store') }}">
          @csrf
          <h1>Welcome back</h1>
          <p class="text-muted mb-4">Log in to pick up where you left off.</p>

          <div class="mb-3">
            <label class="form-label" for="email">Email address</label>
            <div class="input-icon">
              <i class="bi bi-person"></i>
              <input
                class="form-control"
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                autocomplete="username"
                required
                placeholder="name@example.com"
              />
            </div>
            @error('email')
              <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-2">
            <label class="form-label" for="password">Password</label>
            <div class="input-icon position-relative">
              <i class="bi bi-lock"></i>
              <input
                type="password"
                class="form-control pe-5"
                id="password"
                name="password"
                autocomplete="current-password"
                required
                placeholder="••••••••"
              />
              <!-- Eye button: visual only in the static version -->
              <button
                type="button"
                class="btn btn-link position-absolute top-50 translate-middle-y text-muted"
                style="inset-inline-end: 0.25rem"
                data-toggle-password="password"
                aria-label="Show password"
              >
                <i class="bi bi-eye"></i>
              </button>
            </div>
          </div>

          <div class="text-end mb-4">
            <a href="{{ route('auth.forgot-password') }}" class="small">Forgot password?</a>
          </div>

          <button
            type="submit"
            class="btn btn-accent btn-lg w-100"
          >Log in</button>

          <p class="text-center small mt-4 mb-0">
            <span class="text-muted">Don't have an account?</span>
            <a href="{{ route('auth.register') }}" class="fw-semibold">Create one</a>
          </p>
        </form>
      </section>
    </main>
@endsection
