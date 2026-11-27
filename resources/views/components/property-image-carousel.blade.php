@props([
    'property',
    'compact' => false,
])

@php
    $images = $property->galleryImages();
    $count = $images->count();
    $title = $property->localized('title');
@endphp

<div
    @class([
        'property-image-carousel',
        'property-image-carousel--compact' => $compact,
    ])
    data-property-carousel
    data-current-index="0"
    role="group"
    aria-label="{{ __('Property photos') }}"
>
    @if ($count > 0)

        <div class="property-carousel-track">

            @foreach ($images as $image)
                <button
                    type="button"
                    class="property-carousel-slide"
                    data-gallery-open
                    data-slide-index="{{ $loop->index }}"
                    @if (!$loop->first) hidden @endif
                    aria-label="{{ __('View photo') }} {{ $loop->iteration }}"
                >
                    <img
                        src="{{ $image->public_url }}"
                        alt="{{ $title }} — {{ $loop->iteration }}"
                        @unless ($loop->first)
                            loading="lazy"
                        @endunless
                        draggable="false"
                    >
                </button>
            @endforeach

        </div>

        @if ($count > 1)

            <button
                type="button"
                class="property-carousel-arrow property-carousel-prev"
                data-carousel-prev
                aria-label="{{ __('Previous photo') }}"
            >
                <i class="bi bi-chevron-left"></i>
            </button>

            <button
                type="button"
                class="property-carousel-arrow property-carousel-next"
                data-carousel-next
                aria-label="{{ __('Next photo') }}"
            >
                <i class="bi bi-chevron-right"></i>
            </button>

            <span
                class="property-carousel-counter"
                data-carousel-counter
                aria-live="polite"
                dir="ltr"
            >
                1 / {{ $count }}
            </span>

            @unless ($compact)
                <div class="property-carousel-dots">

                    @foreach ($images as $image)
                        <button
                            type="button"
                            @class([
                                'property-carousel-dot',
                                'is-active' => $loop->first,
                            ])
                            data-carousel-dot="{{ $loop->index }}"
                            aria-label="{{ __('View photo') }} {{ $loop->iteration }}"
                            aria-current="{{ $loop->first ? 'true' : 'false' }}"
                        ></button>
                    @endforeach

                </div>
            @endunless

        @endif

    @else

        <span class="property-carousel-placeholder">
            <i class="bi bi-house-door"></i>
        </span>

    @endif
</div>