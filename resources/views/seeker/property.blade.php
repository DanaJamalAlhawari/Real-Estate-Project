@extends('layouts.app')
@section('title', __('Makan — :property', ['property' => $property->localized('title')]))
@section('roleStyle', 'seeker')
@section('bodyAttributes') data-role="seeker" data-page="property"@endsection
@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/maplibre-gl@5.6.1/dist/maplibre-gl.css">
@endpush

@push('scripts')
    <script src="https://unpkg.com/maplibre-gl@5.6.1/dist/maplibre-gl.js"></script>
    <script>
        maplibregl.setRTLTextPlugin(
            'https://unpkg.com/@mapbox/mapbox-gl-rtl-text@0.3.0/dist/mapbox-gl-rtl-text.js',
            true
        );
    </script>
    <script src="{{ asset('assets/js/controllers/localized-map.js') }}"></script>
@endpush
@section('content')
    <script src="{{ asset('assets/js/controllers/booking-calendar.js') }}" defer></script>
    <x-seeker-navbar />

    <main class="app-page container">
        <a href="{{ route('seeker.search') }}" class="back-link"><i
                class="bi bi-arrow-left"></i>{{ __('Back to results') }}</a>
        @php
            $galleryImages = $property->galleryImages();
            $galleryCount = $galleryImages->count();
        @endphp

        <div class="gallery property-detail-gallery mt-2 mb-4" data-property-gallery
            data-gallery-title="{{ $property->localized('title') }}" aria-label="{{ __('Property photos') }}">
            @forelse ($galleryImages as $image)
                <button type="button" @class(['property-detail-gallery-item', 'g-main' => $loop->first]) data-gallery-open data-slide-index="{{ $loop->index }}"
                    aria-label="{{ __('View photo') }} {{ $loop->iteration }}"
                    @if ($loop->index >= 5) hidden @endif>
                    <img src="{{ $image->public_url }}"
                        alt="{{ __(':property — photo :number', [
                            'property' => $property->localized('title'),
                            'number' => $loop->iteration,
                        ]) }}"
                        class="w-100 h-100 object-fit-cover" @unless ($loop->first) loading="lazy" @endunless
                        draggable="false">

                    @if ($loop->first)
                        <span class="property-detail-gallery-count" dir="ltr">
                            <i class="bi bi-images"></i>
                            {{ $galleryCount }}
                        </span>
                    @endif

                    @if ($loop->index === 4 && $galleryCount > 5)
                        <span class="property-detail-gallery-more">
                            +{{ $galleryCount - 5 }}
                        </span>
                    @endif
                </button>
            @empty
                <div class="g-main ph">
                    <i class="bi bi-house-door"></i>
                    {{ __('No property photos yet.') }}
                </div>
            @endforelse
        </div>

        <div class="row g-4 align-items-start">
            <div class="col-lg-8 d-flex flex-column gap-3">
                <div class="prop-head">
                    <h1>{{ $property->localized('title') }}</h1>
                    <p class="text-muted">
                        {{ $property->localized('city') }}
                    </p>
                    <div class="facts">
                        <span
                            class="fact">{{ __(\App\Models\Property::CATEGORIES[$property->category] ?? $property->category) }}</span>
                        <span class="fact">{{ $property->bedrooms }} {{ __('bedrooms') }}</span>
                        <span class="fact">{{ $property->bathrooms }} {{ __('bathrooms') }}</span>
                        <span class="fact">
                            <i class="bi bi-arrows-angle-expand"></i>
                            {{ rtrim(rtrim(number_format((float) $property->area_sqm, 2, '.', ''), '0'), '.') }}
                            {{ __('m²') }}
                        </span>
                        <span class="status status--info"><i class="bi bi-patch-check-fill"></i>
                            {{ __('Verified') }}</span>
                    </div>
                </div>
                <section class="panel">
                    <h2 class="h5">{{ __('About the home') }}</h2>
                    <p>{{ $property->localized('description') }}</p>
                    <ul class="amenities list-unstyled mb-0">
                        @forelse ($property->services as $service)
                            <li class="amenity">{{ __($service->name) }}</li>
                        @empty
                            <li class="text-muted">{{ __('No amenities listed.') }}</li>
                        @endforelse
                    </ul>
                    <a class="btn btn-outline-primary mt-3"
                        href="{{ route('seeker.reviews.index', $property) }}">{{ __('Reviews') }}</a>
                </section>
                <section class="panel">
                    <h2 class="h5">{{ __('Location') }}</h2>
                    <p class="d-flex align-items-center gap-2">
                        <i class="bi bi-geo-alt"></i>

                        <span>
                            {{ implode(
                                app()->isLocale('ar') ? '، ' : ', ',
                                array_filter([$property->localized('city'), $property->localized('address')]),
                            ) }}
                        </span>
                    </p>

                    @if ($property->latitude !== null && $property->longitude !== null)
                        @php
                            $latitude = (float) $property->latitude;
                            $longitude = (float) $property->longitude;
                        @endphp

                        @if ($property->location_is_approximate)
                            <p class="small text-muted">
                                {{ __('Approximate location') }}
                            </p>
                        @endif

                        <div id="propertyDetailMap" class="property-detail-map" role="region"
                            aria-label="{{ __('Property location map') }}" data-latitude="{{ $latitude }}"
                            data-longitude="{{ $longitude }}" style="width: 100%; height: 350px; border-radius: 12px;">
                        </div>

                        <a class="small"
                            href="https://www.openstreetmap.org/?mlat={{ $latitude }}&amp;mlon={{ $longitude }}#map=16/{{ $latitude }}/{{ $longitude }}"
                            target="_blank" rel="noopener">
                            {{ __('Open map') }}
                        </a>
                    @else
                        <p class="small text-muted mb-0">
                            {{ __('A map pin is not available for this address.') }}
                        </p>
                    @endif
                </section>
                <section class="panel">
                    <h2 class="h5">{{ __('Property owner') }}</h2>
                    <p>{{ $property->agent?->display_name ?? __('Name not listed') }}</p>
                    @if ($property->agent?->agentProfile?->agency_name)
                        <p class="text-muted">{{ $property->agent->agentProfile->agency_name }}</p>
                    @endif
                    <form method="post" action="{{ route('seeker.property.conversation', $property) }}">
                        @csrf
                        <button class="btn btn-outline-primary" type="submit">{{ __('Message owner') }}</button>
                    </form>
                </section>
            </div>

            <div class="col-lg-4">
                <aside class="panel sticky-box" aria-label="{{ __('Booking') }}">
                    @if ($property->price !== null)
                        <p><b class="fs-3">{{ number_format((float) $property->price, 2) }} {{ __('JOD') }}</b>
                            {{ __('per month') }}</p>
                        @if ($errors->any())
                            <p class="text-danger" role="alert">{{ $errors->first() }}</p>
                        @endif
                        <form method="post" action="{{ route('seeker.bookings.store', $property) }}" data-booking-calendar
                            data-reserved="{{ $reservedDates->toJson() }}">
                            @csrf
                            <div class="mb-3">
                                <label for="moveIn" class="form-label">{{ __('Start date') }}</label>
                                <input type="date" id="moveIn" name="moveIn" class="form-control"
                                    min="{{ today()->toDateString() }}" value="{{ old('moveIn') }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="moveOut" class="form-label">{{ __('End date (checkout)') }}</label>
                                <input type="date" id="moveOut" name="moveOut" class="form-control"
                                    min="{{ today()->addDay()->toDateString() }}" value="{{ old('moveOut') }}" required>
                            </div>
                            <div class="mb-3" data-calendar hidden>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-calendar-previous
                                        aria-label="{{ __('Previous month') }}">‹</button>
                                    <strong class="small" data-calendar-month aria-live="polite"></strong>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-calendar-next
                                        aria-label="{{ __('Next month') }}">›</button>
                                </div>
                                <div class="d-grid gap-1 text-center"
                                    style="grid-template-columns: repeat(7, minmax(0, 1fr))" data-calendar-days></div>
                                <p class="small text-muted mt-2 mb-0" data-calendar-help aria-live="polite"></p>
                            </div>
                            <button type="submit"
                                class="btn btn-accent btn-lg w-100">{{ __('Request booking') }}</button>
                        </form>
                    @else
                        <p>{{ __('Contact the owner for a price before booking.') }}</p>
                    @endif
                    <div class="d-flex gap-2 mt-3">
                        <form
                            action="{{ route($saved ? 'seeker.favorites.destroy' : 'seeker.favorites.store', $property) }}"
                            method="post">
                            @csrf
                            @if ($saved)
                                @method('DELETE')
                            @endif
                            <button class="btn btn-outline-secondary"
                                type="submit">{{ $saved ? 'Unsave' : __('Save') }}</button>
                        </form>
                        <button type="button" id="sharePropertyBtn" class="btn btn-outline-secondary"
                            data-share-url="{{ route('seeker.property.details', ['property_id' => $property->id]) }}"
                            data-share-title="{{ $property->localized('title') }}">{{ __('Share') }}</button>
                    </div>
                    <p id="sharePropertyStatus" class="small mt-2" aria-live="polite"></p>
                    <input id="sharePropertyLink" class="form-control" aria-label="{{ __('Property link') }}" readonly
                        hidden>
                </aside>
            </div>
        </div>
    </main>
    <script>
        (() => {
            const button = document.getElementById("sharePropertyBtn");
            const status = document.getElementById("sharePropertyStatus");
            const link = document.getElementById("sharePropertyLink");

            button.addEventListener("click", async () => {
                const url = button.dataset.shareUrl;

                status.textContent = "";
                link.hidden = true;

                if (navigator.share) {
                    try {
                        await navigator.share({
                            title: button.dataset.shareTitle,
                            url,
                        });
                        return;
                    } catch (error) {
                        if (error.name === "AbortError") return;
                    }
                }

                try {
                    await navigator.clipboard.writeText(url);
                    status.textContent = window.makanTranslate("Link copied.");
                } catch {
                    link.value = url;
                    link.hidden = false;
                    link.focus();
                    link.select();
                    status.textContent = window.makanTranslate("Copy the property link below.");
                }
            });
        })();
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mapElement = document.getElementById('propertyDetailMap');

            if (!mapElement || !window.MakanMap) {
                return;
            }

            const latitude = Number(mapElement.dataset.latitude);
            const longitude = Number(mapElement.dataset.longitude);

            if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) {
                return;
            }

            const map = window.MakanMap.createMap(mapElement, {
                center: [longitude, latitude],
                zoom: 16
            });

            if (map) {
                window.MakanMap.createMarker(map, latitude, longitude);
            }
        });
    </script>
@endsection
