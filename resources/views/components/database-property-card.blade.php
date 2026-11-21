@props(['property', 'saved' => false])

<div class="col-sm-6 col-xxl-4">
    <article class="prop-card">
        @if (auth()->user()?->role === 'tenant')
        <form
            action="{{ route(
                $saved ? 'seeker.favorites.destroy' : 'seeker.favorites.store',
                ['property' => $property->id]
            ) }}"
            method="POST"
        >
            @csrf

            @if ($saved)
                @method('DELETE')
            @endif

            <button
                type="submit"
                class="fav-btn {{ $saved ? 'is-on' : '' }}"
                style="position:absolute; top:8px; inset-inline-end:8px; z-index:2;"
                aria-label="{{ $saved ? 'Remove from favorites' : 'Add to favorites' }}"
            >
                <i class="{{ $saved ? 'bi bi-heart-fill' : 'bi bi-heart' }}"></i>
            </button>
        </form>
        @endif

        <div class="prop-media">
            @php($primaryImage = $property->images->firstWhere('is_primary', true) ?? $property->images->first())

            @if ($primaryImage)
                <img
                    src="{{ $primaryImage->public_url }}"
                    alt="{{ $property->title }}"
                    class="w-100 h-100"
                    style="object-fit:cover;"
                >
            @else
                <span class="ph-icon">
                    <i class="bi bi-house-door"></i>
                </span>
            @endif

            @if ($property->verification_status === 'verified')
                <span class="badge-verified">
                    <i class="bi bi-patch-check-fill"></i>Verified
                </span>
            @endif
        </div>

        <div class="prop-body">
            <div class="d-flex justify-content-between align-items-start gap-2">
                <a
                    href="{{ route('seeker.property.details', ['property_id' => $property->id]) }}"
                    class="prop-title"
                >
                    {{ $property->title }}
                </a>

                <a
                    href="{{ route('seeker.reviews.index', ['property' => $property->id]) }}"
                    class="property-rating"
                >
                    @if ($property->reviews_count > 0)
                        <i class="bi bi-star-fill"></i>
                        {{ number_format($property->reviews_avg_rating, 1) }}
                        ({{ $property->reviews_count }})
                    @else
                        No reviews
                    @endif
                </a>
            </div>

            <div class="prop-meta">
                <span>
                    <i class="bi bi-geo-alt"></i>
                    {{ $property->area }}, {{ $property->city }}
                </span>
                <span>
                    <i class="bi bi-house-door"></i>
                    {{ $property->bedrooms }} bedrooms
                </span>
                <span>
                    <i class="bi bi-droplet"></i>
                    {{ $property->bathrooms }} bathrooms
                </span>
            </div>

            <div class="prop-price">
                <div>
                    <b>
                        {{ $property->price !== null
                            ? number_format((float) $property->price, 2) . ' JOD'
                            : 'Contact for price' }}
                    </b>
                    <span>per month</span>
                </div>

                <a href="{{ route('seeker.property.details', ['property_id' => $property->id]) }}">
                    View details <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </article>
</div>
