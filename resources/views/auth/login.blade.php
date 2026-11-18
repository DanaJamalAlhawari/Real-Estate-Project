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
         method="post" action="{{ route('demo.notice') }}" data-demo-form data-demo-next="{{ route('seeker.search') }}">
              @csrf
              <p class="small text-muted" role="note">Demo form. Use sample details only.</p>
          <h1>Welcome back</h1>
          <p class="text-muted mb-4">Log in to pick up where you left off.</p>

          <div class="mb-3">
            <label class="form-label" for="login">Email or phone number</label>
            <div class="input-icon">
              <i class="bi bi-person"></i>
              <input
                type="text"
                class="form-control"
                id="login"
                name="login"
                autocomplete="username"
                required
                placeholder="name@example.com or 07X XXX XXXX"
              />
            </div>
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

          <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="form-check">
              <input
                class="form-check-input"
                type="checkbox"
                id="remember"
                name="remember"
                value="1"
                checked
              />
              <label
                class="form-check-label small"
                for="remember"
              >Remember me</label>
            </div>
            <a href="{{ route('auth.forgot-password') }}" class="small">Forgot password?</a>
          </div>

          <button
            type="submit"
            class="btn btn-accent btn-lg w-100"
          >Log in</button>

          <div class="divider-text">
            <span>or</span>
          </div>

          <!-- Log in with Google / Apple -->
          <div class="d-grid gap-2">
            <button
              type="button"
              class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-center gap-2"
              data-provider="google"
            >
              <i class="bi bi-google"></i><span>Continue with Google</span>
            </button>
            <button
              type="button"
              class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-center gap-2"
              data-provider="apple"
            >
              <i class="bi bi-apple"></i><span>Continue with Apple</span>
            </button>
          </div>

          <p class="text-center small mt-4 mb-0">
            <span class="text-muted">Don't have an account?</span>
            <a href="{{ route('auth.register') }}" class="fw-semibold">Create one</a>
          </p>
        </form>
      </section>
    </main>
@endsection
