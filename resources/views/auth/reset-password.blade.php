@extends('layouts.app')

@section('title', "Makan — Reset password")

@section('content')
    <main class="auth-wrap">
      <aside class="auth-side">
        <a class="brand" href="{{ route('home') }}"
          ><span class="brand-mark">M</span><span>Makan</span
        ></a>
        <div>
          <h2>Almost done</h2>
          <p class="mb-0">Enter the code from your email and choose a new password.</p>
        </div>
        <small class="opacity-75">Verified homes across Jordan</small>
      </aside>

      <section class="auth-main">
        <div class="auth-top">
          <a class="brand" href="{{ route('home') }}"
            ><span class="brand-mark">M</span><span>Makan</span
          ></a>
        </div>

        <!-- Step 2 of 2: check the emailed code, save the new password, then log in -->
        <form
          class="auth-form"
          id="resetForm"
         method="post" action="{{ route('demo.notice') }}" data-demo-form data-demo-next="{{ route('auth.login') }}">
              @csrf
              <p class="small text-muted" role="note">Demo form. Use sample details only.</p>
          <span class="feature-icon"><i class="bi bi-shield-lock"></i></span>
          <h1>Choose a new password</h1>
          <p class="text-muted mb-4">
            We sent a 6-digit code to
            <b class="text-body" id="resetEmail">name@example.com</b>.
          </p>

          <div class="mb-2">
            <label class="form-label" for="otp">Code</label>
            <input
              type="text"
              class="form-control form-control-lg otp-input"
              id="otp"
              name="code"
              inputmode="numeric"
              autocomplete="one-time-code"
              pattern="[0-9]{6}"
              maxlength="6"
              required
              placeholder="••••••"
              aria-describedby="otpHint"
            />
          </div>
          <p class="small text-muted mb-3" id="otpHint">
            The code expires in 10 minutes.
            <button
              type="button"
              class="btn btn-link btn-sm p-0 fw-semibold align-baseline"
              id="resendCode"
            >
              Resend code
            </button>
          </p>

          <div class="row g-3 mb-4">
            <div class="col-sm-6">
              <label class="form-label" for="newPassword">New password</label>
              <input
                type="password"
                class="form-control"
                id="newPassword"
                name="password"
                autocomplete="new-password"
                required
                minlength="8"
                placeholder="••••••••"
              />
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="confirmPassword"
                >Confirm password</label
              >
              <input
                type="password"
                class="form-control"
                id="confirmPassword"
                name="confirm_password"
                autocomplete="new-password"
                required
                minlength="8"
                placeholder="••••••••"
              />
            </div>
            <div class="col-12 mt-1">
              <small class="text-muted">At least 8 characters.</small>
            </div>
          </div>

          <button type="submit" class="btn btn-accent btn-lg w-100">
            Save new password
          </button>

          <p class="text-center small mt-4 mb-0">
            <a href="{{ route('auth.login') }}" class="fw-semibold"
              ><i class="bi bi-arrow-left"></i> Back to log in</a
            >
          </p>
        </form>
      </section>
    </main>
@endsection
