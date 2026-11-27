@props(['property', 'saved' => false])

<div class="col-sm-6 col-xxl-4">
    <article class="prop-card">
        @if (auth()->user()?->role === 'tenant')
            <form
                action="{{ route($saved ? 'seeker.favorites.destroy' : 'seeker.favorites.store', ['property' => $property->id]) }}"
                method="POST">
                @csrf

                @if ($saved)
                    @method('DELETE')
                @endif

                <button type="submit" class="fav-btn {{ $saved ? 'is-on' : '' }}"
                    style="position:absolute; top:8px; inset-inline-end:8px; z-index:2;"
                    aria-label="{{ $saved ? __('Remove from favorites') : __('Add to favorites') }}">
                    <i class="{{ $saved ? 'bi bi-heart-fill' : 'bi bi-heart' }}"></i>
                </button>
            </form>
        @endif

        <div class="prop-media">

            <x-property-image-carousel :property="$property" />

            @if ($property->verification_status === 'verified')
                <span class="badge-verified">
                    <i class="bi bi-patch-check-fill"></i>{{ __('Verified') }}
                </span>
            @endif

        </div>

        <div class="prop-body">
            <div class="d-flex justify-content-between align-items-start gap-2">
                <a href="{{ route('seeker.property.details', ['property_id' => $property->id]) }}" class="prop-title">
                    {{ $property->localized('title') }}
                </a>

                <a href="{{ route('seeker.reviews.index', ['property' => $property->id]) }}" class="property-rating">
                    @if ($property->reviews_count > 0)
                        <i class="bi bi-star-fill"></i>
                        {{ number_format($property->reviews_avg_rating, 1) }}
                        ({{ $property->reviews_count }})
                    @else
                        {{ __('No reviews') }}
                    @endif
                </a>
            </div>

            <div class="prop-meta">
                <span>
                    <i class="bi bi-geo-alt"></i>
                    {{ implode(app()->isLocale('ar') ? '، ' : ', ', array_filter([$property->localized('area'), $property->localized('city')])) }}
                </span>
                <span>
                    <i class="bi bi-house-door"></i>
                    {{ $property->bedrooms }} {{ __('bedrooms') }}
                </span>
                <span>
                    <i class="bi bi-droplet"></i>
                    {{ $property->bathrooms }} {{ __('bathrooms') }}
                </span>
            </div>

            <div class="prop-price">
                <div>
                    <b>
                        {{ $property->price !== null
                            ? number_format((float) $property->price, 2) . ' ' . __('JOD')
                            : __('Contact for price') }}
                    </b>
                    <span>{{ __('per month') }}</span>
                </div>

                <a href="{{ route('seeker.property.details', ['property_id' => $property->id]) }}">
                    {{ __('View details') }} <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </article>
</div>
