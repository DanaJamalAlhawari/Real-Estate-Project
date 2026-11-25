@props(['home', 'layout'])

@php($isSeeker = $layout === 'seeker')
<div class="col-sm-6 {{ $isSeeker ? 'col-xxl-4' : 'col-lg-4' }}">
  <article class="prop-card home-card" data-home-id="{{ $home['id'] }}">
    @if ($isSeeker)
      <button type="button" class="fav-btn" aria-label="{{ __('Add to favorites') }}" aria-pressed="false">
        <i class="bi bi-heart" aria-hidden="true"></i>
      </button>
    @endif
    <div class="prop-media ph ph-{{ $home['id'] }}">
      <span class="ph-icon"><i class="bi bi-house-door"></i></span>
      @if ($home['verified'])
        <span class="badge-verified"><i class="bi bi-patch-check-fill"></i>{{ __('Verified') }}</span>
      @endif
      @if ($home['featured'])
        <span class="badge-featured"><i class="bi bi-star-fill"></i>{{ __('Featured') }}</span>
      @endif
    </div>
    <div class="prop-body">
      <a href="{{ route('seeker.property', ['home' => $home['id']]) }}" class="prop-title stretched-link">
        {{ $home['title'] }}
      </a>
      @if ($isSeeker)
        <span class="listed-by"><i class="bi bi-person"></i> {{ __('Listed by') }} {{ $home['owner'] }}</span>
      @endif
      <div class="prop-meta">
        <span><i class="bi bi-geo-alt"></i> {{ $home['area'] }}{{ $isSeeker ? ', Amman' : '' }}</span>
        <span><i class="bi bi-arrows-angle-expand"></i> {{ $home['size'] }} m²</span>
        <span><i class="bi bi-lamp"></i> {{ $home['furnished'] ? __('Furnished') : 'Unfurnished' }}</span>
      </div>
      <div class="prop-price">
        <div><b>{{ $home['total'] }} {{ __('JOD') }}</b> <span>{{ __('per month, bills included') }}</span></div>
        <span class="view-more">{{ __('View details') }} <i class="bi bi-arrow-right"></i></span>
      </div>
    </div>
  </article>
</div>
