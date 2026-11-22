@extends('layouts.app')

@section('title', "Makan — Create account")

@section('content')
    <main class="auth-wrap">
      <aside class="auth-side">
        <a class="brand" href="{{ route('home') }}"
          ><img class="brand-mark" src="{{ asset('assets/images/makan-mark.svg') }}" alt="" width="36" height="36"><span>Makan</span
        ></a>
        <div>
          <h2>Your next home is waiting</h2>
          <ul class="list-unstyled d-grid gap-3 mt-4 mb-0">
            <li class="d-flex gap-2">
              <i class="bi bi-stars"></i><span>A match score on every home, based on your priorities</span>
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

      <section class="auth-main">
        <div class="auth-top">
          <a class="brand" href="{{ route('home') }}"
            ><img class="brand-mark" src="{{ asset('assets/images/makan-mark.svg') }}" alt="" width="36" height="36"><span>Makan</span
          ></a>
        </div>

        <form
          class="auth-form"
          id="registerForm"
          method="POST"
          action="{{ route('auth.register.store') }}">
          @csrf
          @if ($errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif
          <h1>Create your account</h1>
          <p class="text-muted mb-4">It takes a minute.</p>

          <!-- Account type -->
          <label class="form-label">I'm...</label>
          <div class="row g-2 mb-3">
            <div class="col-sm-6">
              <input
                type="radio"
                class="btn-check"
                name="role"
                id="roleTenant"
                value="tenant"
                @checked(old('role', request('role', 'tenant')) !== 'agent')
              />
              <label class="choice-card" for="roleTenant">
                <span class="ci"><i class="bi bi-search-heart"></i></span>
                <span
                  ><b>Looking for a home</b
                  ><small>Study, work, tourism, medical care, or relocation</small
                ></span>
              </label>
            </div>
            <div class="col-sm-6">
              <input
                type="radio"
                class="btn-check"
                name="role"
                id="roleOwner"
                value="agent"
                @checked(old('role', request('role')) === 'agent')
              />
              <label class="choice-card" for="roleOwner">
                <span class="ci"><i class="bi bi-house-door"></i></span>
                <span
                  ><b>A property owner</b
                  ><small>Individual owner or real estate agency</small
                ></span>
              </label>
            </div>
          </div>

          <!-- Owners only: shown when "A property owner" is picked (CSS :has) -->
          <div class="mb-3 owner-only" id="ownerType">
            <label class="form-label">Account type</label>
            <div class="d-flex flex-wrap gap-2">
              <input
                type="radio"
                class="btn-check"
                name="owner_type"
                id="otIndividual"
                value="individual"
                @checked(old('owner_type', 'individual') === 'individual')
              />
              <label class="chip-select" for="otIndividual"
                ><i class="bi bi-person"></i
                ><span>Individual owner</span
              ></label>
              <input
                type="radio"
                class="btn-check"
                name="owner_type"
                id="otAgency"
                value="agency"
                @checked(old('owner_type') === 'agency')
              />
              <label class="chip-select" for="otAgency"
                ><i class="bi bi-building"></i
                ><span>Real estate agency</span
              ></label>
            </div>
          </div>

          <!-- Agencies only: shown when "Real estate agency" is picked -->
          <div class="mb-3 agency-only">
            <label class="form-label" for="agencyName">Agency name</label>
            <input
              type="text"
              class="form-control"
              id="agencyName"
              name="agency_name"
              value="{{ old('agency_name') }}"
              autocomplete="organization"
              placeholder="e.g. Al Riyada Real Estate"
            />
          </div>

          <div class="row g-3 mb-3">
            @foreach (['first_name' => 'First name', 'last_name' => 'Last name'] as $field => $label)
              <div class="col-sm-6">
                <label class="form-label" for="{{ $field }}">{{ $label }}</label>
                <input class="form-control" id="{{ $field }}" name="{{ $field }}" value="{{ old($field) }}"
                       required>
              </div>
            @endforeach
          </div>
          <div class="row g-3 mb-3">
            <div class="col-sm-6">
              <label class="form-label" for="email">Email</label>
              <input
                type="email"
                class="form-control"
                id="email"
                name="email"
                value="{{ old('email') }}"
                autocomplete="email"
                required
                placeholder="name@example.com"
              />
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="phone">Phone number</label>
              <input
                type="tel"
                class="form-control"
                id="phone"
                name="phone"
                value="{{ old('phone') }}"
                autocomplete="tel"
                required
                placeholder="+9627XXXXXXXX"
              />
            </div>
            <div class="col-12 mt-1">
              <small class="text-muted"
                ><i class="bi bi-telephone"></i>
                <span
                  >Enter the number in international format, for example +9627XXXXXXXX.</span
                ></small
              >
            </div>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-sm-6">
              <label class="form-label" for="password">Password</label>
              <input
                type="password"
                class="form-control"
                id="password"
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
                name="password_confirmation"
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
          <div class="form-check mb-4">
            <input
              class="form-check-input"
              type="checkbox"
              id="terms"
              name="terms"
              value="1"
              required
            />
            <label
              class="form-check-label small"
              for="terms"
            >I agree to the Terms of Service and Privacy Policy</label>
          </div>

          <button
            type="submit"
            class="btn btn-accent btn-lg w-100"
          >Create account</button>

          <p class="text-center small mt-4 mb-0">
            <span class="text-muted">Already have an account?</span>
            <a href="{{ route('auth.login') }}" class="fw-semibold">Log in</a>
          </p>
        </form>
      </section>
    </main>
@endsection
