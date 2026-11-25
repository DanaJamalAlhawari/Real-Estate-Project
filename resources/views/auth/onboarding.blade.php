@extends('layouts.app')

@section('title', __('Makan — Get to know you'))

@section('content')
    <header class="app-topbar">
      <a class="brand" href="{{ route('home') }}"
        ><img class="brand-mark" src="{{ asset('assets/images/makan-mark.svg') }}" alt="" width="36" height="36"><span>Makan</span
      ></a>
      <div class="ms-auto d-flex align-items-center gap-2">
        <a
          href="{{ route('seeker.search') }}"
          class="btn btn-sm btn-link text-muted text-decoration-none"
        >{{ __('Skip for now') }}</a>
      </div>
      <x-language-switch />
    </header>

    <main class="container py-4 py-lg-5">
      <!-- All three steps are shown one after another -->
      <form class="wizard" id="wizard" method="post" action="{{ route('demo.notice') }}" data-demo-form>
              @csrf
              <p class="small text-muted" role="note">{{ __('Demo form. Use sample details only.') }}</p>
        <!-- ===== Step 1: Why are you coming ===== -->
        <section class="wizard-step" data-step="1">
          <span class="step-tag">{{ __('Step 1 of 3') }}</span>
          <h1 class="h2">{{ __('Why are you coming?') }}</h1>
          <p class="text-muted mb-4">
            {{ __('Pick one or more, and we\'ll suggest the right priorities.') }}
          </p>

          <div class="row row-cols-2 row-cols-md-3 g-2 mb-4">
            <div class="col">
              <input
                type="checkbox"
                class="btn-check"
                name="purpose"
                id="pStudy"
                value="study"
                checked
              /><label class="choice-card align-items-center" for="pStudy"
                ><span class="ci"><i class="bi bi-mortarboard"></i></span
                ><b>{{ __('Study') }}</b
              ></label>
            </div>
            <div class="col">
              <input
                type="checkbox"
                class="btn-check"
                name="purpose"
                id="pWork"
                value="work"
              /><label class="choice-card align-items-center" for="pWork"
                ><span class="ci"><i class="bi bi-briefcase"></i></span
                ><b>{{ __('Work') }}</b
              ></label>
            </div>
            <div class="col">
              <input
                type="checkbox"
                class="btn-check"
                name="purpose"
                id="pTourism"
                value="tourism"
              /><label class="choice-card align-items-center" for="pTourism"
                ><span class="ci"><i class="bi bi-camera"></i></span
                ><b>{{ __('Tourism') }}</b
              ></label>
            </div>
            <div class="col">
              <input
                type="checkbox"
                class="btn-check"
                name="purpose"
                id="pMedical"
                value="medical"
              /><label class="choice-card align-items-center" for="pMedical"
                ><span class="ci"><i class="bi bi-hospital"></i></span
                ><b>{{ __('Medical care') }}</b
              ></label>
            </div>
            <div class="col">
              <input
                type="checkbox"
                class="btn-check"
                name="purpose"
                id="pRelocation"
                value="relocation"
              /><label class="choice-card align-items-center" for="pRelocation"
                ><span class="ci"><i class="bi bi-truck"></i></span
                ><b>{{ __('Relocation') }}</b
              ></label>
            </div>
            <div class="col">
              <input
                type="checkbox"
                class="btn-check"
                name="purpose"
                id="pOther"
                value="other"
              /><label class="choice-card align-items-center" for="pOther"
                ><span class="ci"><i class="bi bi-three-dots"></i></span
                ><b>{{ __('Other') }}</b
              ></label>
            </div>
          </div>

          <div class="row g-3">
            <div class="col-md-7">
              <label class="form-label">{{ __('How long will you stay?') }}</label>
              <div class="d-flex flex-wrap gap-2">
                <input
                  type="radio"
                  class="btn-check"
                  name="duration"
                  id="d1"
                /><label class="chip-select" for="d1">{{ __('Under a month') }}</label>
                <input
                  type="radio"
                  class="btn-check"
                  name="duration"
                  id="d3"
                /><label class="chip-select" for="d3">{{ __('1 – 3 months') }}</label>
                <input
                  type="radio"
                  class="btn-check"
                  name="duration"
                  id="d6"
                  checked
                /><label class="chip-select" for="d6">{{ __('3 – 6 months') }}</label>
                <input
                  type="radio"
                  class="btn-check"
                  name="duration"
                  id="d12"
                /><label class="chip-select" for="d12">{{ __('6 months or more') }}</label>
              </div>
            </div>
            <div class="col-md-5">
              <label
                class="form-label"
                for="moveIn"
              >{{ __('When do you want to move in?') }}</label>
              <input
                type="date"
                class="form-control"
                id="moveIn"
                value="2026-11-01"
              />
            </div>
          </div>
        </section>

        <!-- ===== Step 2: Where do you want to live ===== -->
        <section class="wizard-step" data-step="2">
          <span class="step-tag">{{ __('Step 2 of 3') }}</span>
          <h1 class="h2">{{ __('Where do you want to live?') }}</h1>
          <p class="text-muted mb-4">
            {{ __('Pick a city, then an area if you have one in mind. We\'ll show homes there first.') }}
          </p>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label" for="city">{{ __('City') }}</label>
              <select class="form-select form-select-lg" id="city" name="city" required>
                <option value="" selected disabled>{{ __('Choose a city') }}</option>
                <option value="amman">{{ __('Amman') }}</option>
                <option value="irbid">{{ __('Irbid') }}</option>
                <option value="zarqa">{{ __('Zarqa') }}</option>
                <option value="aqaba">{{ __('Aqaba') }}</option>
                <option value="salt">{{ __('Salt') }}</option>
                <option value="madaba">{{ __('Madaba') }}</option>
                <option value="jerash">{{ __('Jerash') }}</option>
                <option value="ajloun">{{ __('Ajloun') }}</option>
                <option value="mafraq">{{ __('Mafraq') }}</option>
                <option value="karak">{{ __('Karak') }}</option>
                <option value="tafilah">{{ __('Tafilah') }}</option>
                <option value="maan">{{ __('Ma\'an') }}</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="area"
                >{{ __('Area') }} <span class="text-muted fw-normal">{{ __('(optional)') }}</span></label
              >
              <!-- Areas are grouped by city; the backend fills this list -->
              <select class="form-select form-select-lg" id="area" name="area">
                <option value="" selected>{{ __('Any area') }}</option>
                <optgroup label="Amman">
                  <option>{{ __('Abdali') }}</option>
                  <option>{{ __('Al Jubeiha') }}</option>
                  <option>{{ __('Khalda') }}</option>
                  <option>{{ __('Shmeisani') }}</option>
                  <option>{{ __('Sweileh') }}</option>
                  <option>{{ __('Tla Al Ali') }}</option>
                </optgroup>
                <optgroup label="Irbid">
                  <option>{{ __('Al Hay Al Janoubi') }}</option>
                  <option>{{ __('Al Nuzha') }}</option>
                </optgroup>
                <optgroup label="Aqaba">
                  <option>{{ __('Al Shallaleh') }}</option>
                  <option>{{ __('Area 9') }}</option>
                </optgroup>
              </select>
            </div>
          </div>
        </section>

        <!-- ===== Step 3: Priorities ===== -->
        <section class="wizard-step" data-step="3">
          <span class="step-tag">{{ __('Step 3 of 3') }}</span>
          <h1 class="h2">{{ __('What matters most?') }}</h1>
          <p class="text-muted mb-4">
            {{ __('Set your budget and tick anything you need.') }}
          </p>

          <!-- Budget: type any amount, or pick one from the list -->
          <div class="panel mb-3">
            <label class="form-label" for="budget"
              >{{ __('Monthly budget (bills included)') }}</label
            >
            <div class="input-group budget-input">
              <input
                type="number"
                class="form-control form-control-lg"
                id="budget"
                name="budget"
                list="budgetOptions"
                min="100"
                max="2000"
                step="10"
                value="300"
                placeholder="{{ __('e.g. 300') }}"
                aria-describedby="budgetHint"
              />
              <span class="input-group-text">{{ __('JOD / month') }}</span>
            </div>
            <datalist id="budgetOptions">
              <option value="150"></option>
              <option value="200"></option>
              <option value="250"></option>
              <option value="300"></option>
              <option value="400"></option>
              <option value="500"></option>
              <option value="750"></option>
              <option value="1000"></option>
            </datalist>
            <small class="form-text text-muted" id="budgetHint"
              >{{ __('Type an amount or pick one from the list.') }}</small
            >
          </div>

          <!-- What you need (yes/no) -->
          <div class="panel" id="priorities">
            <div class="priority priority--check">
              <label for="pr-furnished"
                ><i class="bi bi-lamp"></i
                ><span
                  >{{ __('Furnished') }}
                  <small class="d-block text-muted fw-normal"
                    >{{ __('I want it furnished') }}</small
                  ></span
                ></label
              >
              <input
                type="checkbox"
                class="form-check-input"
                id="pr-furnished"
                name="furnished"
                value="1"
              />
            </div>
            <div class="priority priority--check">
              <label for="pr-access"
                ><i class="bi bi-universal-access"></i
                ><span
                  >{{ __('Easy access') }}
                  <small class="d-block text-muted fw-normal"
                    >{{ __('Elevator or ground floor') }}</small
                  ></span
                ></label
              >
              <input
                type="checkbox"
                class="form-check-input"
                id="pr-access"
                name="easy_access"
                value="1"
              />
            </div>
            <!-- Shared housing: only usable when "Study" is picked in step 1
                 (CSS: .wizard:not(:has(#pStudy:checked)) .priority--students) -->
            <div class="priority priority--check priority--students">
              <label for="pr-sharing"
                ><i class="bi bi-people"></i
                ><span
                  >{{ __('Shared housing') }}
                  <small class="students-only-note d-block fw-normal"
                    >{{ __('For students only') }}</small
                  ></span
                ></label
              >
              <input
                type="checkbox"
                class="form-check-input"
                id="pr-sharing"
                name="shared_housing"
                value="1"
              />
            </div>
          </div>
        </section>

        <div class="wizard-nav justify-content-end">
          <a href="{{ route('seeker.search') }}" class="btn btn-accent btn-lg"
            ><span>{{ __('Show my matches') }}</span>
            <i class="bi bi-arrow-right"></i
          ></a>
        </div>
      </form>
    </main>
@endsection
