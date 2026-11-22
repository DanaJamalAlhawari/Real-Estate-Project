@extends('layouts.app')
@section('title', 'Makan — Discover')
@section('roleStyle', 'seeker')
@section('bodyAttributes') data-role="seeker" data-page="search"@endsection
@section('content')
<x-seeker-navbar />

<!-- ===== Content ===== -->
<main class="app-page container">
  <div class="page-head">
    <div>
      <h1>Discover homes</h1>
      <p>See the homes picked for your priorities, or browse everything owners have listed.</p>
    </div>
  </div>

  <div class="seeker-results-layout">
    <aside class="panel seeker-filters" aria-labelledby="filterHeading">
      <h2 class="h5 mb-3" id="filterHeading"><i class="bi bi-sliders me-2" aria-hidden="true"></i>Filters</h2>
      <form id="seekerFilters" action="{{ route('seeker.search') }}" method="get">
        @if (request()->filled('city'))
          <input type="hidden" name="city" value="{{ request('city') }}">
          <p class="small">City: {{ request('city') }}</p>
        @endif
        <fieldset class="mb-3">
          <legend class="form-label">Property type</legend>
          <div class="d-grid gap-2">
            <label class="form-check">
              <input class="form-check-input" type="radio" name="category" value="" {{ !request('category') ? 'checked' : '' }}>
              <span class="form-check-label">All</span>
            </label>
            @foreach (\App\Models\Property::CATEGORIES as $value => $label)
              <label class="form-check">
                <input class="form-check-input" type="radio" name="category" value="{{ $value }}" @checked(request('category') === $value)>
                <span class="form-check-label">{{ $label }}</span>
              </label>
            @endforeach
          </div>
        </fieldset>
        <div class="mb-3">
          <label class="form-label" for="filterArea">Area</label>
          <input class="form-control" id="filterArea" name="q" type="search" value="{{ request('q') }}" placeholder="e.g. Amman, Al Jubeiha">
        </div>
        <div class="mb-3">
          <label class="form-label" for="filterBudget">Max monthly cost (JOD)</label>
          <input class="form-control" id="filterBudget" name="max" type="number" min="0" step="1" value="{{ request('max') }}" placeholder="Any budget">
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
          <button class="btn btn-accent" type="submit">Apply filters</button>
          <a class="btn btn-link" href="{{ route('seeker.search') }}">Clear</a>
        </div>
      </form>
    </aside>

    <div class="seeker-results">
      <p class="small text-muted mb-3">{{ $properties->count() }} homes</p>
      <div class="row g-4">
        @forelse ($properties as $property)
          <x-database-property-card :property="$property" :saved="in_array($property->id, $favoriteIds)" />
        @empty
          <div class="col-12 text-center py-5">
            <i class="bi bi-house-slash fs-1 text-muted" aria-hidden="true"></i>
            <h2 class="h5 mt-3">No properties found</h2>
            <p class="text-muted">Try changing your filters to find more homes.</p>
            <a href="{{ route('seeker.search') }}" class="btn btn-outline-primary mt-2">Clear filters</a>
          </div>
        @endforelse
      </div>
    </div>
  </div>
</main>
@endsection
