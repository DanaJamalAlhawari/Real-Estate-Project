@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/maplibre-gl@5.6.1/dist/maplibre-gl.css">
@endpush
@section('title', isset($property) ? __('Makan — Edit property') : __('Makan — Add property'))
@section('roleStyle', 'owner')
@section('bodyAttributes') data-role="owner" data-page="property-form"@endsection
@section('content')
    <input type="checkbox" class="sidebar-toggle" id="sidebarToggle" aria-label="{{ __('Collapse the menu') }}" />
    <!-- ===== Topbar ===== -->
    <x-owner-topbar />

    <div class="app-shell">
        <!-- ===== Sidebar ===== -->
        <aside class="app-sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="appSidebar">
            <div class="offcanvas-header border-bottom">
                <a class="brand" href="{{ route('home') }}"><img class="brand-mark"
                        src="{{ asset('assets/images/makan-mark.svg') }}" alt="" width="36"
                        height="36"><span>Makan</span></a>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#appSidebar"
                    aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="offcanvas-body">
                <div class="side-label">{{ __('Property owner') }}</div>
                <a class="side-link" href="{{ route('owner.dashboard') }}" title="{{ __('Dashboard') }}"><i
                        class="bi bi-grid-1x2"></i><span class="flex-grow-1">{{ __('Dashboard') }}</span></a>
                <a class="side-link" href="{{ route('owner.properties') }}" title="{{ __('My properties') }}"><i
                        class="bi bi-house-door"></i><span class="flex-grow-1">{{ __('My properties') }}</span></a>
                <a class="side-link active" href="{{ route('owner.property-form') }}" aria-current="page"
                    title="{{ __('Add property') }}"><i class="bi bi-plus-square"></i><span
                        class="flex-grow-1">{{ __('Add property') }}</span></a>
                <a class="side-link" href="{{ route('owner.requests') }}" title="{{ __('Booking requests') }}"><i
                        class="bi bi-inbox"></i><span class="flex-grow-1">{{ __('Booking requests') }}</span></a>
                <a class="side-link" href="{{ route('owner.chat') }}" title="{{ __('Messages') }}"><i
                        class="bi bi-chat-dots"></i><span class="flex-grow-1">{{ __('Messages') }}</span></a>
                <a class="side-link" href="{{ route('owner.account') }}" title="{{ __('My account') }}"><i
                        class="bi bi-person-badge"></i><span class="flex-grow-1">{{ __('My account') }}</span></a>
            </div>
        </aside>

        <!-- ===== Content ===== -->
        <main class="app-main">
            @isset($property)
                <div class="page-head">
                    <div>
                        <h1>{{ __('Edit property') }}</h1>
                        <p>{{ $property->localized('title') }}</p>
                    </div>
                    <a href="{{ route('owner.properties') }}" class="btn btn-outline-secondary">{{ __('My properties') }}</a>
                </div>
                <form class="panel form-section" action="{{ route('properties.verification.store') }}" method="post"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="property_id" value="{{ $property->id }}">
                    <h2>{{ __('Ownership verification') }}</h2>
                    <p>{{ __('Status:') }} {{ __(\Illuminate\Support\Str::headline($property->verification_status)) }}</p>
                    @if (session('success'))
                        <p class="text-success" role="status">{{ __(session('success')) }}</p>
                    @endif
                    @if ($errors->any())
                        <p class="text-danger" role="alert">{{ $errors->first() }}</p>
                    @endif
                    <label for="ownershipDocument"
                        class="form-label">{{ __('Ownership document (PDF, JPG, or PNG, up to 5 MB)') }}</label>
                    <input id="ownershipDocument" class="form-control mb-3" type="file" name="document"
                        accept=".pdf,.jpg,.jpeg,.png" required>
                    <button class="btn btn-accent" type="submit">{{ __('Upload document') }}</button>
                </form>
            @else
                <div class="page-head">
                    <div>
                        <h1>{{ __('Add property') }}</h1>
                        <p>{{ __('The more details you fill in, the more accurately your property matches with seekers.') }}
                        </p>
                    </div>
                </div>
            @endisset
            <div class="row g-4 align-items-start">
                <div class="col-lg-8">
                    <form id="propertyForm" method="POST"
                        action="{{ isset($property) ? route('properties.update', $property->id) : route('properties.store') }}"
                        enctype="multipart/form-data">
                        @csrf
                        @if (isset($property))
                            @method('PUT')
                        @endif
                        @if ($errors->any())
                            <div class="alert alert-danger mt-3" role="alert">
                                <strong>{{ __('Failed to save property:') }}</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <!-- 1. Basic details -->

                        <section class="panel form-section" aria-labelledby="secBasics">

                            <h2 id="secBasics">{{ __('1. Basic details') }}</h2>

                            <p>{{ __('Property type and specs.') }}</p>

                            <div class="row g-3">

                                <div class="col-12">

                                    <label for="fTitle" class="form-label">{{ __('Listing title') }}</label>

                                    <input type="text" class="form-control" id="fTitle" name="title"
                                        value="{{ old('title', $property->title ?? '') }}" required />

                                </div>

                                <div class="col-sm-6 col-xl-3">

                                    <label for="fCategory" class="form-label">

                                        {{ __('Property Category') }}

                                    </label>

                                    <select class="form-select" id="fCategory" name="category" required>

                                        <option value="">{{ __('Select Category') }}</option>

                                        @foreach (\App\Models\Property::CATEGORIES as $value => $label)
                                            <option value="{{ $value }}" @selected(old('category', $property->category ?? '') === $value)>
                                                {{ __($label) }}</option>
                                        @endforeach
                                    </select>

                                </div>

                                <div class="row g-3 mt-2">

                                    <!-- Bedrooms -->

                                    <div class="col-md-6">

                                        <label for="fBedrooms" class="form-label">

                                            {{ __('Bedrooms') }}

                                        </label>

                                        <input type="number" class="form-control" id="fBedrooms" name="bedrooms"
                                            min="0" value="{{ old('bedrooms', $property->bedrooms ?? 0) }}"
                                            required>

                                    </div>

                                    <!-- Bathrooms -->

                                    <div class="col-md-6">

                                        <label for="fBathrooms" class="form-label">

                                            {{ __('Bathrooms') }}

                                        </label>

                                        <input type="number" class="form-control" id="fBathrooms" name="bathrooms"
                                            min="0" value="{{ old('bathrooms', $property->bathrooms ?? 0) }}"
                                            required>

                                    </div>

                                </div>

                            </div>

                            <!-- Amenities srevices -->

                            <fieldset class="mt-3">

                                <legend class="visually-hidden">{{ __('Amenities') }}</legend>

                                <div class="d-flex flex-wrap gap-2" id="amenities">

                                    @foreach ($services ?? [] as $service)
                                        <input type="checkbox" class="btn-check" id="service{{ $service->id }}"
                                            name="services[]" value="{{ $service->id }}"
                                            {{ in_array(
                                                $service->id,

                                                old(
                                                    'services',

                                                    isset($property) ? $property->services->pluck('id')->all() : [],
                                                ),
                                            )
                                                ? 'checked'
                                                : '' }} />

                                        <label class="chip-select" for="service{{ $service->id }}">

                                            @if (!empty($service->icon_name))
                                                <i class="bi {{ $service->icon_name }}"></i>
                                            @endif

                                            {{ __($service->name) }}

                                        </label>
                                    @endforeach

                                </div>

                            </fieldset>

                        </section>

                        <section class="panel form-section" aria-labelledby="secDesc">
                            <h2 id="secDesc">{{ __('2. Description') }}</h2>
                            <label for="fDescEn" class="form-label">{{ __('Property description') }}</label>
                            <textarea class="form-control" id="fDescEn" name="description" rows="5" required>{{ old('description', $property->description ?? '') }}</textarea>
                        </section>

                        <details class="panel form-section" open>
                            <summary class="h5">{{ __('Property translations') }}</summary>
                            <div class="row g-3 mt-1">
                                @foreach (['ar' => 'Arabic', 'en' => 'English'] as $language => $languageLabel)
                                    <fieldset class="col-md-6">
                                        <legend class="h6">{{ __($languageLabel) }}</legend>
                                        @foreach (['title' => 'Property title', 'description' => 'Property description', 'city' => 'City', 'area' => 'Area', 'address' => 'Address'] as $field => $label)
                                            @php($translation = $field . '_' . $language)
                                            <div class="mb-3">
                                                <label class="form-label"
                                                    for="{{ $translation }}">{{ __($label) }}</label>
                                                @if ($field === 'description')
                                                    <textarea class="form-control" id="{{ $translation }}" name="{{ $translation }}" rows="3" dir="auto">{{ old($translation, $property?->{$translation} ?? '') }}</textarea>
                                                @else
                                                    <input type="text" class="form-control" id="{{ $translation }}"
                                                        name="{{ $translation }}" maxlength="255" dir="auto"
                                                        value="{{ old($translation, $property?->{$translation} ?? '') }}">
                                                @endif
                                                @error($translation)
                                                    <div class="text-danger small">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        @endforeach
                                    </fieldset>
                                @endforeach
                            </div>
                        </details>

                        <!-- 3. Photos -->

                        <section class="panel form-section" aria-labelledby="secPhotos">

                            <h2 id="secPhotos">{{ __('3. Photos') }}</h2>

                            <p>{{ __('Real, recent photos. The first photo is the cover.') }}</p>

                            <label class="dropzone d-block mb-3" for="fPhotos">

                                <i class="bi bi-cloud-arrow-up"></i>

                                <span class="d-block fw-semibold text-body">{{ __('Drag photos here, or') }}

                                    <span
                                        class="text-primary-bt text-decoration-underline">{{ __('choose from your device') }}</span></span>

                                <span class="d-block small">{{ __('JPG or PNG · up to 10 photos') }}</span>

                            </label>

                            <!-- Upload new property photos -->

                            <input type="file" class="visually-hidden" id="fPhotos" name="photos[]"
                                accept="image/jpeg,image/png" multiple />

                            <!-- images -->

                            <div class="thumbs" id="photoThumbs">

                                @if (isset($property) && $property->images->isNotEmpty())

                                    @foreach ($property->images as $image)
                                        <div class="t" id="property-image-{{ $image->id }}"
                                            style="position: relative;">

                                            <img src="{{ $image->public_url }}" alt="{{ __('Property image') }}"
                                                style="width: 100%; height: 100%; object-fit: cover;">

                                            @if ($image->is_primary)
                                                <span class="cover">{{ __('Cover') }}</span>
                                            @endif

                                            <!-- Mark existing image for deletion -->

                                            <label
                                                style="position: absolute; top: 5px; right: 5px;

                           background: #dc3545; color: white;

                           border-radius: 50%; width: 25px; height: 25px;

                           display: flex; align-items: center;

                           justify-content: center; cursor: pointer;"
                                                title="{{ __('Delete image') }}">

                                                <input type="checkbox" name="deleted_images[]"
                                                    value="{{ $image->id }}" class="visually-hidden"
                                                    onchange="

                            this.closest('.t').style.opacity =

                                this.checked ? '0.4' : '1';

                        ">

                                                <i class="bi bi-x-lg"></i>

                                            </label>

                                        </div>
                                    @endforeach

                                @endif

                            </div>

                        </section>

                        @unless (isset($property))
                            <section class="panel form-section" aria-labelledby="secOwnership">
                                <h2 id="secOwnership">{{ __('Ownership verification') }}</h2>
                                <label for="createOwnershipDocument"
                                    class="form-label">{{ __('Ownership document (PDF, JPG or PNG, up to 5 MB)') }}</label>
                                <input id="createOwnershipDocument" class="form-control" type="file" name="document"
                                    accept=".pdf,.jpg,.jpeg,.png" required>
                            </section>
                        @endunless

                        <!-- 4. Location -->

                        <section class="panel form-section" aria-labelledby="secLocation">

                            <h2 id="secLocation">{{ __('4. Location') }}</h2>

                            <p>{{ __('Search for the neighborhood, then place the marker at the exact building.') }}</p>

                            <div class="row g-3 mb-3">

                                <div class="col-md-6">

                                    <label for="fCity" class="form-label">{{ __('City') }}</label>

                                    <input type="text" class="form-control" id="fCity"
                                        value="{{ old('city', $property->city ?? '') }}" disabled>
                                    <input type="hidden" id="fCityValue" name="city"
                                        value="{{ old('city', $property->city ?? '') }}">

                                </div>

                                <div class="col-md-6">

                                    <label for="fArea" class="form-label">{{ __('Area / Neighborhood') }}</label>

                                    <input type="text" class="form-control" id="fArea"
                                        value="{{ old('area', $property->area ?? '') }}" disabled>
                                    <input type="hidden" id="fAreaValue" name="area"
                                        value="{{ old('area', $property->area ?? '') }}">

                                </div>

                                <div class="col-12">

                                    <label for="fStreet"
                                        class="form-label">{{ __('Street / Building Address') }}</label>

                                    <input type="text" class="form-control" id="fStreet"
                                        value="{{ old('address', $property->address ?? '') }}" disabled>
                                    <input type="hidden" id="fStreetValue" name="address"
                                        value="{{ old('address', $property->address ?? '') }}">

                                </div>

                                <div class="col-12">

                                    <label for="locationSearch"
                                        class="form-label">{{ __('Search Property Location') }}</label>

                                    <div class="input-group">

                                        <input type="text" id="locationSearch" class="form-control"
                                            placeholder="{{ __('e.g. Khalda, Amman') }}">

                                        <button type="button" id="searchLocationBtn" class="btn btn-primary"><i
                                                class="bi bi-search"></i> {{ __('Search') }}</button>

                                    </div>

                                    <div id="locationResults" class="list-group mt-2" aria-live="polite"></div>

                                </div>

                                <div class="col-12">

                                    <div id="locationMap"
                                        style="height:400px;width:100%;border-radius:12px;overflow:hidden;"></div>

                                    <input type="hidden" id="fLat" name="latitude"
                                        value="{{ old('latitude', $property->latitude ?? '') }}">

                                    <input type="hidden" id="fLng" name="longitude"
                                        value="{{ old('longitude', $property->longitude ?? '') }}">
                                    <input type="hidden" id="fApproximate" name="location_is_approximate"
                                        value="{{ (int) old('location_is_approximate', $property->location_is_approximate ?? false) }}">

                                    <p class="small text-muted mt-2 mb-0">
                                        {{ __('Search for a Jordan address and select a result to fill the location fields.') }}
                                    </p>

                                </div>

                            </div>

                        </section>

                        <!-- 5. Property Price -->

                        <section class="panel form-section" aria-labelledby="secPrice">

                            <h2 id="secPrice">{{ __('5. Property Price') }}</h2>

                            <p>{{ __('Set the monthly rental price for your property.') }}</p>

                            <div class="row g-3">

                                <div class="col-sm-6">

                                    <label for="fRent" class="form-label">

                                        {{ __('Monthly Rent (JOD)') }}

                                    </label>

                                    <input type="number" class="form-control" id="fRent" name="price"
                                        value="{{ old('price', $property->price ?? '') }}" min="0"
                                        step="0.01" required />

                                </div>

                            </div>

                        </section>

                        <!-- Save buttons -->

                        <div class="d-flex flex-wrap justify-content-end gap-2 mt-4">

                            <button type="submit" class="btn btn-accent btn-lg" name="action" value="publish">

                                {{ __('Publish listing') }}

                            </button>

                        </div>

                    </form>

                </div>

                <!-- ===== Side summary ===== -->

                <aside class="col-lg-4">

                    <div class="sticky-box d-flex flex-column gap-3">

                        <!-- Listing readiness -->

                        <section class="panel" aria-labelledby="readyTitle">

                            <h2 class="fs-6 mb-2" id="readyTitle">{{ __('Listing readiness') }}</h2>

                            <div class="progress mb-3" role="progressbar" aria-label="{{ __('Listing readiness') }}"
                                aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">

                                <div class="progress-bar" id="readinessBar" style="width: 0%"></div>

                            </div>

                            <ul class="list-clean d-flex flex-column gap-2 small" id="readinessList">

                                <li class="d-flex align-items-center gap-2" data-check="basic">

                                    <i class="bi bi-exclamation-circle-fill ic-warn"></i>{{ __('Basic details') }}

                                </li>

                                <li class="d-flex align-items-center gap-2" data-check="description">

                                    <i class="bi bi-exclamation-circle-fill ic-warn"></i>{{ __('Description') }}

                                </li>

                                <li class="d-flex align-items-center gap-2" data-check="photos">

                                    <i class="bi bi-exclamation-circle-fill ic-warn"></i>{{ __('Photos') }}

                                </li>

                                <li class="d-flex align-items-center gap-2" data-check="location">

                                    <i class="bi bi-exclamation-circle-fill ic-warn"></i>{{ __('Location') }}

                                </li>

                                <li class="d-flex align-items-center gap-2" data-check="price">

                                    <i class="bi bi-exclamation-circle-fill ic-warn"></i>{{ __('Price') }}

                                </li>

                            </ul>

                        </section>

                        <!-- After publishing -->

                        <section class="panel" aria-labelledby="afterTitle">

                            <h2 class="fs-6 mb-2" id="afterTitle">{{ __('After publishing') }}</h2>

                            <div class="d-flex gap-2 small text-muted mb-2">

                                <i class="bi bi-patch-check text-primary-bt"></i>

                                <span>{{ __('Your property listing will be submitted for admin review.') }}</span>

                            </div>

                            <div class="d-flex gap-2 small text-muted">

                                <i class="bi bi-arrow-repeat text-primary-bt"></i>

                                <span>{{ __('You can edit your property details whenever needed.') }}</span>

                            </div>

                        </section>

                    </div>

                </aside>

            </div>
        </main>
    </div>
    @push('scripts')
        <script src="https://unpkg.com/maplibre-gl@5.6.1/dist/maplibre-gl.js"></script>

        <script>
            maplibregl.setRTLTextPlugin(
                'https://unpkg.com/@mapbox/mapbox-gl-rtl-text@0.3.0/dist/mapbox-gl-rtl-text.js',
                true
            );
        </script>

        <script src="{{ asset('assets/js/controllers/localized-map.js') }}"></script>

        @php($jordanSites = config('jordan_locations.sites'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const element = id => document.getElementById(id);
                const mapEl = element('locationMap');
                const search = element('locationSearch');
                const button = element('searchLocationBtn');
                const results = element('locationResults');
                if (!mapEl || !search || !button || !results) return;

                const latitude = element('fLat');
                const longitude = element('fLng');
                const approximate = element('fApproximate');
                const saved = latitude.value !== '' && longitude.value !== '';
                const initial = saved ? [+longitude.value, +latitude.value] : [35.9106, 31.9539];

                const map = window.MakanMap?.createMap(mapEl, {
                    center: initial,
                    zoom: saved ? 16 : 11
                }) || null;

                let marker;

                if (map) {
                    if (saved) {
                        marker = window.MakanMap.createMarker(
                            map,
                            +latitude.value,
                            +longitude.value
                        );
                    }
                } else {
                    mapEl.hidden = true;
                }

                const fillField = (id, value) => {
                    element(id).value = value;
                    const hidden = element(id + 'Value');
                    hidden.value = value;
                    hidden.dispatchEvent(new Event('input', {
                        bubbles: true
                    }));
                };

                const selectPlace = place => {
                    const coordinates = [place.longitude, place.latitude];
                    const changed = +latitude.value !== place.latitude || +longitude.value !== place.longitude;
                    if (changed) {
                        ['city_ar', 'area_ar', 'address_ar', 'city_en', 'area_en', 'address_en'].forEach(id => {
                            if (element(id)) element(id).value = '';
                        });
                    }
                    Object.entries(place.translations || {}).forEach(([id, value]) => {
                        if (element(id)) element(id).value = value;
                    });
                    fillField('fCity', place.city);
                    fillField('fArea', place.area);
                    fillField('fStreet', place.address);
                    latitude.value = place.latitude.toFixed(7);
                    longitude.value = place.longitude.toFixed(7);
                    approximate.value = place.approximate ? '1' : '0';
                    latitude.dispatchEvent(new Event('input', {
                        bubbles: true
                    }));
                    if (map) {
                        map.jumpTo({
                            center: coordinates,
                            zoom: 16
                        });

                        if (marker) {
                            marker.setLngLat(coordinates);
                        } else {
                            marker = window.MakanMap.createMarker(
                                map,
                                place.latitude,
                                place.longitude
                            );
                        }
                    }
                    search.value = place.label;
                    results.textContent = window.makanTranslate('Location selected.');
                };

                const sites = Object.values(@json($jordanSites));
                const localResults = query => sites.filter(place => {
                    const normalize = value => value.toLowerCase().replace(/[\u064b-\u065f]/g, '').replace(
                        /[أإآ]/g, 'ا');
                    const text = normalize(place.slice(0, 6).join(' '));
                    return normalize(query).split(/[\s,،]+/).every(term => text.includes(term));
                }).map(([city, cityAr, area, areaAr, address, addressAr, latitude, longitude]) => ({
                    city,
                    area,
                    address,
                    latitude,
                    longitude,
                    approximate: true,
                    label: document.documentElement.lang === 'ar' ? [addressAr, areaAr, cityAr].join('، ') :
                        [address, area, city].join(', '),
                    translations: {
                        city_ar: cityAr,
                        area_ar: areaAr,
                        address_ar: addressAr,
                        city_en: city,
                        area_en: area,
                        address_en: address
                    },
                }));

                const fromPhoton = feature => {
                    const place = feature.properties || {};
                    if (place.countrycode?.toUpperCase() !== 'JO') return null;
                    const [longitude, latitude] = feature.geometry?.coordinates || [];
                    const municipality = place.type === 'city' || place.type === 'town' || place.type === 'village';
                    const city = place.city || place.town || place.village || (municipality ? place.name : '') ||
                        place.county || place.state;
                    const area = place.neighbourhood || place.suburb || place.district || place.locality || (place
                        .type === 'district' ? place.name : '') || '';
                    const address = [place.street, place.housenumber].filter(Boolean).join(' ') || place.name;
                    if (!city || !address || !Number.isFinite(latitude) || !Number.isFinite(longitude)) return null;
                    const label = [...new Set([address, area, city].filter(Boolean))].join(', ');
                    return {
                        city,
                        area,
                        address,
                        latitude,
                        longitude,
                        label,
                        approximate: !place.housenumber
                    };
                };

                const findLocation = async () => {
                    const query = search.value.trim();
                    if (query.length < 3) {
                        results.textContent = window.makanTranslate('Please enter at least 3 characters.');
                        return;
                    }
                    button.disabled = true;
                    results.textContent = window.makanTranslate('Searching...');
                    let places = localResults(query);
                    try {
                        const params = new URLSearchParams({
                            q: query,
                            countrycode: 'JO',
                            limit: '8',
                            lang: 'en'
                        });
                        const response = await fetch('https://photon.komoot.io/api/?' + params, {
                            signal: AbortSignal.timeout(7000)
                        });
                        if (!response.ok) throw new Error('Location search failed');
                        const payload = await response.json();
                        places = [...places, ...(payload.features || []).map(fromPhoton).filter(Boolean)];
                    } catch {
                        if (!places.length) {
                            results.textContent = window.makanTranslate(
                                'Location search is unavailable. Try again.');
                            return;
                        }
                    } finally {
                        button.disabled = false;
                    }

                    results.replaceChildren();
                    if (places.length === 0) {
                        results.textContent = window.makanTranslate('No matching locations in Jordan.');
                    }
                    for (const place of places) {
                        const option = document.createElement('button');
                        option.type = 'button';
                        option.className = 'list-group-item list-group-item-action';
                        option.textContent = place.label;
                        option.addEventListener('click', () => selectPlace(place));
                        results.append(option);
                    }
                };

                button.addEventListener('click', findLocation);
                search.addEventListener('keydown', event => {
                    if (event.key !== 'Enter') return;
                    event.preventDefault();
                    if (!button.disabled) findLocation();
                });
            });
        </script>

        <!-- Listing Readiness: Dynamic Progress -->

        <script>
            document.addEventListener('DOMContentLoaded', function() {

                const form = document.getElementById('propertyForm');

                const list = document.getElementById('readinessList');

                const bar = document.getElementById('readinessBar');

                const progress = bar?.closest('.progress');

                if (!form || !list || !bar || !progress) return;

                const field = id => document.getElementById(id);

                const filled = id => Boolean(field(id)?.value.trim());

                function updateReadiness() {

                    const checks = {

                        basic: filled('fTitle') && filled('fCategory'),

                        description: filled('fDescEn'),

                        photos: (field('fPhotos')?.files.length || 0) > 0 ||

                            document.querySelectorAll('#photoThumbs .t').length > 0,

                        location: filled('fCity') && filled('fStreet') &&

                            filled('fLat') && filled('fLng'),

                        price: filled('fRent') && Number(field('fRent').value) > 0

                    };

                    let completed = 0;

                    Object.entries(checks).forEach(([key, valid]) => {

                        const item = list.querySelector(`[data-check="${key}"]`);

                        if (!item) return;

                        const icon = item.querySelector('i');

                        if (valid) {

                            completed++;

                            icon.className = 'bi bi-check-circle-fill ic-ok';

                        } else {

                            icon.className = 'bi bi-exclamation-circle-fill ic-warn';

                        }

                    });

                    const percentage = Math.round(completed / 5 * 100);

                    bar.style.width = percentage + '%';

                    progress.setAttribute('aria-valuenow', percentage);

                }

                form.addEventListener('input', updateReadiness);

                form.addEventListener('change', updateReadiness);

                // Recheck when the map updates the hidden coordinates.*

                const observer = new MutationObserver(updateReadiness);

                ['fLat', 'fLng'].forEach(id => {

                    const input = field(id);

                    if (input) observer.observe(input, {

                        attributes: true

                    });

                });

                updateReadiness();

            });
        </script>

        <!-- Preview newly selected property photos -->

        <script>
            document.addEventListener('DOMContentLoaded', function() {

                const photoInput = document.getElementById('fPhotos');

                const photoThumbs = document.getElementById('photoThumbs');

                if (!photoInput || !photoThumbs) return;

                let newPreviews = [];

                photoInput.addEventListener('change', function() {

                    // Remove only previous previews, not saved property images.*

                    newPreviews.forEach(preview => preview.remove());

                    newPreviews = [];

                    Array.from(photoInput.files).forEach(file => {

                        if (!['image/jpeg', 'image/png'].includes(file.type)) return;

                        const preview = document.createElement('div');

                        preview.className = 't';

                        const img = document.createElement('img');

                        img.src = URL.createObjectURL(file);

                        img.alt = 'New property photo';

                        img.style.cssText = 'width:100%;height:100%;object-fit:cover;';

                        img.addEventListener('load', function() {

                            URL.revokeObjectURL(img.src);

                        }, {

                            once: true

                        });

                        // Add image to preview.*

                        preview.appendChild(img);

                        // Create remove button for newly selected photo.*

                        const removeBtn = document.createElement('button');

                        removeBtn.type = 'button';

                        removeBtn.className = 'btn btn-danger btn-sm';

                        removeBtn.innerHTML = '<i class="bi bi-x-lg"></i>';

                        removeBtn.title = 'Remove photo';

                        removeBtn.style.cssText = `

    position: absolute;

    top: 5px;

    right: 5px;

    border-radius: 50%;

    width: 25px;

    height: 25px;

    padding: 0;

    display: flex;

    align-items: center;

    justify-content: center;

`;

                        preview.style.position = 'relative';

                        removeBtn.addEventListener('click', function() {

                            // Remove selected photo from the actual file input.*

                            const files = new DataTransfer();

                            Array.from(photoInput.files).forEach(selectedFile => {

                                if (selectedFile !== file) {

                                    files.items.add(selectedFile);

                                }

                            });

                            photoInput.files = files.files;

                            // Remove photo preview.*

                            preview.remove();

                            newPreviews = newPreviews.filter(item => item !== preview);

                            // Update Listing Readiness.*

                            photoInput.dispatchEvent(new Event('change', {

                                bubbles: true

                            }));

                        });

                        preview.appendChild(removeBtn);

                        photoThumbs.appendChild(preview);

                        newPreviews.push(preview);

                    });

                });

            });
        </script>
    @endpush
@endsection
