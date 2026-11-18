@extends('layouts.app')

@section('title', "Makan — Verify your email")

@section('content')
    <main class="auth-wrap">
      <aside class="auth-side">
        <a class="brand" href="{{ route('home') }}"
          ><span class="brand-mark">M</span><span>Makan</span
        ></a>
        <div>
          <h2>One last step</h2>
          <p class="mb-0">
            We check every email so owners and home seekers know they're
            talking to real people.
          </p>
        </div>
        <small class="opacity-75">Verified homes across Jordan</small>
      </aside>

      <section class="auth-main">
        <div class="auth-top">
          <a class="brand" href="{{ route('home') }}"
            ><span class="brand-mark">M</span><span>Makan</span
          ></a>
        </div>

        <!-- After a correct code: seekers go to onboarding,
             owners go to their dashboard (decided by the backend) -->
        <form
          class="auth-form"
          id="verifyForm"
         method="post" action="{{ route('demo.notice') }}" data-demo-form data-demo-next="{{ route('auth.onboarding') }}">
              @csrf
              <p class="small text-muted" role="note">Demo form. Use sample details only.</p>
          <span class="feature-icon"><i class="bi bi-envelope-check"></i></span>
          <h1>Check your email</h1>
          <p class="text-muted mb-4">
            We sent a 6-digit code to
            <b class="text-body" id="verifyEmail">name@example.com</b>. Enter it
            below to verify your account.
          </p>

          <div class="mb-2">
            <label class="form-label" for="otp">Verification code</label>
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
          <p class="small text-muted mb-4" id="otpHint">
            The code expires in 10 minutes.
          </p>

          <button type="submit" class="btn btn-accent btn-lg w-100">
            Verify email
          </button>

          <div
            class="d-flex flex-wrap justify-content-between gap-2 small mt-4"
          >
            <span>
              <span class="text-muted">Didn't get it?</span>
              <button
                type="button"
                class="btn btn-link btn-sm p-0 fw-semibold align-baseline"
                id="resendCode"
              >
                Resend code
              </button>
            </span>
            <a href="{{ route('auth.register') }}" class="fw-semibold">Change email</a>
          </div>
        </form>
      </section>
    </main>
@endsection
