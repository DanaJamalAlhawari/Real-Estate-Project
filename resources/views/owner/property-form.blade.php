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
                    <form id="propertyForm" method="POST" data-popup-validation
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
                        @php
                            $bilingualValue = function (string $field, string $language) use (&$property) {
                                $translation = $field . '_' . $language;

                                $translatedValue = $property?->{$translation};

                                $hasTranslation =
                                    filled($property?->{$field . '_ar'}) || filled($property?->{$field . '_en'});

                                if ($hasTranslation) {
                                    return old($translation, $translatedValue ?? '');
                                }

                                $original = $property?->{$field} ?? '';

                                $originalLanguage = preg_match('/\p{Arabic}/u', $original) ? 'ar' : 'en';

                                return old($translation, $originalLanguage === $language ? $original : '');
                            };
                        @endphp
                        <!-- 1. Basic details -->

                        <section class="panel form-section" aria-labelledby="secBasics">

                            <h2 id="secBasics">{{ __('1. Basic details') }}</h2>

                            <p>{{ __('Property type and specs.') }}</p>

                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label for="title_ar" class="form-label">
                                        {{ __('Property title') }} ({{ __('Arabic') }})
                                    </label>

                                    <input type="text" class="form-control" id="title_ar" name="title_ar"
                                        maxlength="255" dir="rtl" value="{{ $bilingualValue('title', 'ar') }}">

                                    @error('title_ar')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="title_en" class="form-label">
                                        {{ __('Property title') }} ({{ __('English') }})
                                    </label>

                                    <input type="text" class="form-control" id="title_en" name="title_en"
                                        maxlength="255" dir="ltr" value="{{ $bilingualValue('title', 'en') }}">

                                    @error('title_en')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                @error('title')
                                    <div class="col-12 text-danger small">{{ $message }}</div>
                                @enderror

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

                                    <div class="col-md-4">

                                        <label for="fBedrooms" class="form-label">

                                            {{ __('Bedrooms') }}

                                        </label>

                                        <input type="number" class="form-control" id="fBedrooms" name="bedrooms"
                                            min="0" value="{{ old('bedrooms', $property->bedrooms ?? 0) }}"
                                            required>

                                    </div>

                                    <!-- Bathrooms -->

                                    <div class="col-md-4">

                                        <label for="fBathrooms" class="form-label">

                                            {{ __('Bathrooms') }}

                                        </label>

                                        <input type="number" class="form-control" id="fBathrooms" name="bathrooms"
                                            min="0" value="{{ old('bathrooms', $property->bathrooms ?? 0) }}"
                                            required>

                                    </div>
                                    <div class="col-md-4">
                                        <label for="fAreaSqm" class="form-label">
                                            {{ __('Property Area (m²)') }}
                                            <span class="text-danger">*</span>
                                        </label>

                                        <input type="number" class="form-control @error('area_sqm') is-invalid @enderror"
                                            id="fAreaSqm" name="area_sqm"
                                            value="{{ old('area_sqm', $property->area_sqm ?? '') }}" min="0.01"
                                            max="99999999.99" step="0.01" placeholder="120" required>

                                        @error('area_sqm')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                </div>

                            </div>

                </div>

                <!-- Amenities srevices -->

                <fieldset class="mt-3">

                    <legend class="visually-hidden">{{ __('Amenities') }}</legend>

                    <div class="d-flex flex-wrap gap-2" id="amenities">

                        @foreach ($services ?? [] as $service)
                            <input type="checkbox" class="btn-check" id="service{{ $service->id }}" name="services[]"
                                value="{{ $service->id }}"
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

                    <div class="mb-3">
                        <label for="description_ar" class="form-label">
                            {{ __('Property description') }} ({{ __('Arabic') }})
                        </label>

                        <textarea class="form-control" id="description_ar" name="description_ar" rows="4" dir="rtl">{{ $bilingualValue('description', 'ar') }}</textarea>

                        @error('description_ar')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label for="description_en" class="form-label">
                            {{ __('Property description') }} ({{ __('English') }})
                        </label>

                        <textarea class="form-control" id="description_en" name="description_en" rows="4" dir="ltr">{{ $bilingualValue('description', 'en') }}</textarea>

                        @error('description_en')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                </section>


                <section class="panel form-section" aria-labelledby="secLocation">

                    <h2 id="secLocation">{{ __('3. Location') }}</h2>

                    <p>{{ __('Search for the neighborhood, then place the marker at the exact building.') }}</p>

                    <div class="row g-3 mb-3">





                        <div class="col-12">

                            <label for="locationSearch" class="form-label">{{ __('Search Property Location') }}</label>

                            <div class="input-group">

                                <input type="text" id="locationSearch" class="form-control"
                                    placeholder="{{ __('e.g. Khalda, Amman') }}">

                                <button type="button" id="searchLocationBtn" class="btn btn-primary"><i
                                        class="bi bi-search"></i> {{ __('Search') }}</button>

                            </div>

                            <div id="locationResults" class="list-group mt-2" aria-live="polite"></div>

                        </div>

                        <div class="col-12">

                            <div id="locationMap" style="height:400px;width:100%;border-radius:12px;overflow:hidden;">
                            </div>

                            <input type="hidden" id="fLat" name="latitude"
                                value="{{ old('latitude', $property->latitude ?? '') }}">

                            <input type="hidden" id="fLng" name="longitude"
                                value="{{ old('longitude', $property->longitude ?? '') }}">
                            <input type="hidden" id="fApproximate" name="location_is_approximate"
                                value="{{ (int) old('location_is_approximate', $property->location_is_approximate ?? false) }}">

                            <p class="small text-muted mt-2 mb-0">
                                {{ __('Search for an address, select a result, then drag the marker to the exact property location.') }}
                            </p>

                        </div>
                        <!-- Bilingual property location -->

                        <div class="col-md-6">
                            <label for="city_ar" class="form-label">
                                {{ __('City') }} ({{ __('Arabic') }})
                            </label>

                            <input type="text" class="form-control" id="city_ar" name="city_ar" maxlength="255"
                                dir="rtl" value="{{ $bilingualValue('city', 'ar') }}">

                            @error('city_ar')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="city_en" class="form-label">
                                {{ __('City') }} ({{ __('English') }})
                            </label>

                            <input type="text" class="form-control" id="city_en" name="city_en" maxlength="255"
                                dir="ltr" value="{{ $bilingualValue('city', 'en') }}">

                            @error('city_en')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="address_ar" class="form-label">
                                {{ __('Street / Building Address') }} ({{ __('Arabic') }})
                            </label>

                            <input type="text" class="form-control" id="address_ar" name="address_ar"
                                maxlength="255" dir="rtl" value="{{ $bilingualValue('address', 'ar') }}">

                            @error('address_ar')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="address_en" class="form-label">
                                {{ __('Street / Building Address') }} ({{ __('English') }})
                            </label>

                            <input type="text" class="form-control" id="address_en" name="address_en"
                                maxlength="255" dir="ltr" value="{{ $bilingualValue('address', 'en') }}">

                            @error('address_en')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>

                </section>



                <!-- 3. Photos -->
                <section class="panel form-section" aria-labelledby="secPhotos">

                    <h2 id="secPhotos">{{ __('4. Photos') }}</h2>

                    @php
                        $existingCover = isset($property) ? $property->coverImage() : null;
                    @endphp

                    <p>{{ __('Upload a default photo and optional additional photos.') }}</p>

                    <!-- Default property photo -->
                    <h3 class="h6 fw-semibold">
                        {{ __('Default photo') }}
                        <span class="text-danger">*</span>
                    </h3>

                    <p class="small text-muted">
                        {{ __('This photo will appear on the property card.') }}
                    </p>

                    <label class="dropzone d-block mb-3" for="fDefaultPhoto">
                        <i class="bi bi-image"></i>

                        <span class="d-block fw-semibold text-body">
                            {{ __('Choose default photo') }}
                        </span>

                        <span class="d-block small">
                            {{ __('JPG or PNG · 1 photo · Max 5 MB') }}
                        </span>
                    </label>

                    <input type="file" class="visually-hidden" id="fDefaultPhoto" name="default_photo"
                        accept="image/jpeg,image/png" @required(!$existingCover) />

                    @error('default_photo')
                        <div class="text-danger small mb-2">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="thumbs mb-4" id="defaultPhotoThumbs"></div>

                    <!-- Additional property photos -->
                    <h3 class="h6 fw-semibold">
                        {{ __('Additional photos') }}
                        <span class="small text-muted">
                            ({{ __('Optional') }})
                        </span>
                    </h3>

                    <label class="dropzone d-block mb-3" for="fPhotos">
                        <i class="bi bi-cloud-arrow-up"></i>

                        <span class="d-block fw-semibold text-body">
                            {{ __('Choose additional photos') }}
                        </span>

                        <span class="d-block small">
                            {{ __('JPG or PNG · up to 9 additional photos') }}
                        </span>
                    </label>

                    <input type="file" class="visually-hidden" id="fPhotos" name="photos[]"
                        accept="image/jpeg,image/png" multiple />

                    @error('photos')
                        <div class="text-danger small mb-2">
                            {{ $message }}
                        </div>
                    @enderror

                    @foreach ($errors->get('photos.*') as $messages)
                        @foreach ($messages as $message)
                            <div class="text-danger small mb-2">
                                {{ $message }}
                            </div>
                        @endforeach
                    @endforeach

                    <div class="thumbs mb-3" id="photoThumbs"></div>

                    <!-- Existing property photos -->
                    @if (isset($property) && $property->images->whereNull('room_id')->isNotEmpty())

                        <h3 class="h6 fw-semibold mt-3">
                            {{ __('Existing photos') }}
                        </h3>

                        <div class="thumbs" id="existingPhotoThumbs">

                            @foreach ($property->images->whereNull('room_id') as $image)
                                <div class="t" id="property-image-{{ $image->id }}"
                                    data-existing-cover="{{ $existingCover?->id === $image->id ? '1' : '0' }}"
                                    style="position: relative;">

                                    <img src="{{ $image->public_url }}" alt="{{ __('Property image') }}"
                                        style="width: 100%; height: 100%; object-fit: cover;">

                                    @if ($existingCover?->id === $image->id)
                                        <span class="cover">{{ __('Cover') }}</span>
                                    @endif

                                    <label
                                        style="position: absolute;
                            top: 5px;
                            right: 5px;
                            background: #dc3545;
                            color: white;
                            border-radius: 50%;
                            width: 25px;
                            height: 25px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            cursor: pointer;"
                                        title="{{ __('Delete image') }}">

                                        <input type="checkbox" name="deleted_images[]" value="{{ $image->id }}"
                                            class="visually-hidden"
                                            onchange="this.closest('.t').style.opacity = this.checked ? '0.4' : '1';">

                                        <i class="bi bi-x-lg"></i>

                                    </label>

                                </div>
                            @endforeach

                        </div>

                    @endif

                    @error('deleted_images')
                        <div class="text-danger small mt-2">
                            {{ $message }}
                        </div>
                    @enderror

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
                                value="{{ old('price', $property->price ?? '') }}" min="0" step="0.01"
                                required />

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

        @php
            $jordanSites = config('jordan_locations.sites');
        @endphp
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
                let reverseRequest = null;

                const locationFields = [
                    'city_ar',
                    'city_en',
                    'address_ar',
                    'address_en'
                ];

                const cancelReverseLookup = () => {
                    const previousRequest = reverseRequest;

                    reverseRequest = null;

                    previousRequest?.abort();
                };

                const reverseGeocode = async (lat, lng) => {
                    cancelReverseLookup();

                    const controller = new AbortController();
                    reverseRequest = controller;

                    locationFields.forEach(id => {
                        element(id).value = '';

                        element(id).dispatchEvent(new Event('input', {
                            bubbles: true
                        }));
                    });

                    search.value = '';

                    results.textContent = window.makanTranslate(
                        'Updating property address...'
                    );

                    const timeout = setTimeout(() => {
                        controller.abort();
                    }, 10000);

                    const fetchAddress = async (language) => {
                        const url = new URL(
                            'https://photon.komoot.io/reverse'
                        );

                        url.searchParams.set('lat', lat);
                        url.searchParams.set('lon', lng);
                        url.searchParams.set('lang', language);
                        url.searchParams.set('limit', '5');
                        url.searchParams.set('radius', '0.5');

                        url.searchParams.append('layer', 'house');
                        url.searchParams.append('layer', 'street');

                        const response = await fetch(url, {
                            signal: controller.signal
                        });

                        if (!response.ok) {
                            throw new Error('Reverse geocoding failed');
                        }

                        const data = await response.json();

                        return (data.features ?? [])
                            .map(feature => feature.properties)
                            .filter(place =>
                                place?.countrycode?.toUpperCase() === 'JO'
                            );
                    };

                    const fillLocationField = (field, value) => {
                        if (typeof value !== 'string' || !value.trim()) {
                            return;
                        }

                        const language = /\p{Script=Arabic}/u.test(value) ?
                            'ar' :
                            'en';

                        const input = element(field + '_' + language);

                        if (input.value.trim() !== '') {
                            return;
                        }

                        input.value = value;

                        input.dispatchEvent(new Event('input', {
                            bubbles: true
                        }));
                    };

                    try {
                        const responses = await Promise.allSettled([
                            fetchAddress('en'),
                            fetchAddress('ar')
                        ]);

                        if (reverseRequest !== controller) {
                            return;
                        }

                        const englishPlaces = responses[0].status === 'fulfilled' ?
                            responses[0].value :
                            [];

                        const arabicPlaces = responses[1].status === 'fulfilled' ?
                            responses[1].value :
                            [];

                        const samePlace = (first, second) => {
                            return first?.osm_id != null &&
                                second?.osm_id != null &&
                                first.osm_id === second.osm_id &&
                                first.osm_type === second.osm_type &&
                                first.type === second.type &&
                                (
                                    !first.housenumber ||
                                    !second.housenumber ||
                                    first.housenumber === second.housenumber
                                );
                        };

                        const primaryPlace = englishPlaces[0] ?? arabicPlaces[0];

                        if (primaryPlace) {
                            const matchingPlace = primaryPlace === englishPlaces[0] ?
                                arabicPlaces.find(place => samePlace(primaryPlace, place)) :
                                englishPlaces.find(place => samePlace(primaryPlace, place));

                            const houseNumber = primaryPlace.type === 'house' ?
                                primaryPlace.housenumber ||
                                matchingPlace?.housenumber ||
                                '' :
                                '';

                            [primaryPlace, matchingPlace]
                            .filter(Boolean)
                                .forEach(place => {
                                    const city = place.city ||
                                        place.town ||
                                        place.village ||
                                        place.municipality ||
                                        '';

                                    const street = place.street ||
                                        (place.type === 'street' ? place.name : '') ||
                                        '';

                                    const address = street ?
                                        [street, houseNumber]
                                        .filter(Boolean)
                                        .join(' ') :
                                        '';

                                    fillLocationField('city', city);
                                    fillLocationField('address', address);
                                });
                        }

                        const hasCity = element('city_ar').value.trim() ||
                            element('city_en').value.trim();

                        const hasAddress = element('address_ar').value.trim() ||
                            element('address_en').value.trim();

                        results.textContent = window.makanTranslate(
                            hasCity && hasAddress ?
                            'Property address updated. Please verify the details.' :
                            'Could not find the complete address. Please enter the missing details manually.'
                        );

                    } finally {
                        clearTimeout(timeout);

                        if (reverseRequest === controller) {
                            reverseRequest = null;
                        }
                    }
                };

                const createDraggableMarker = (lat, lng) => {
                    const newMarker = window.MakanMap
                        .createMarker(map, lat, lng)
                        .setDraggable(true);

                    newMarker.on('dragend', () => {
                        const position = newMarker.getLngLat();

                        if (
                            position.lat < 29.18 ||
                            position.lat > 33.4 ||
                            position.lng < 34.9 ||
                            position.lng > 39.32
                        ) {
                            newMarker.setLngLat([
                                Number(longitude.value),
                                Number(latitude.value)
                            ]);

                            results.textContent = window.makanTranslate(
                                'Please select a location in Jordan.'
                            );

                            return;
                        }

                        latitude.value = position.lat.toFixed(7);
                        longitude.value = position.lng.toFixed(7);
                        approximate.value = '0';

                        latitude.dispatchEvent(new Event('input', {
                            bubbles: true
                        }));

                        longitude.dispatchEvent(new Event('input', {
                            bubbles: true
                        }));

                        reverseGeocode(position.lat, position.lng);
                    });

                    return newMarker;
                };

                if (map) {
                    if (saved) {
                        marker = createDraggableMarker(
                            +latitude.value,
                            +longitude.value
                        );
                    }
                } else {
                    mapEl.hidden = true;
                }



                const selectPlace = place => {
                    cancelReverseLookup();
                    const coordinates = [place.longitude, place.latitude];
                    const changed = +latitude.value !== place.latitude || +longitude.value !== place.longitude;
                    if (changed) {
                        ['city_ar', 'address_ar', 'city_en', 'address_en'].forEach(id => {
                            if (element(id)) element(id).value = '';
                        });
                    }
                    Object.entries(place.translations || {}).forEach(([id, value]) => {
                        if (element(id)) element(id).value = value;
                    });
                    if (!place.translations) {
                        const cityLanguage = /\p{Script=Arabic}/u.test(place.city) ?
                            'ar' :
                            'en';

                        const addressLanguage = /\p{Script=Arabic}/u.test(place.address) ?
                            'ar' :
                            'en';

                        element('city_' + cityLanguage).value = place.city;
                        element('address_' + addressLanguage).value = place.address;
                    }
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
                            marker = createDraggableMarker(
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

                        address_ar: addressAr,
                        city_en: city,

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

                        basic: (filled('title_ar') || filled('title_en')) &&
                            filled('fCategory') &&
                            filled('fAreaSqm') &&
                            field('fAreaSqm').checkValidity(),

                        description: filled('description_ar') ||
                            filled('description_en'),

                        photos: (
                            (field('fDefaultPhoto')?.files.length || 0) > 0 ||
                            Boolean(document.querySelector(
                                '#existingPhotoThumbs [data-existing-cover="1"] input:not(:checked)'
                            ))
                        ),

                        location: (
                                filled('city_ar') || filled('city_en')
                            ) && (
                                filled('address_ar') || filled('address_en')
                            ) &&
                            filled('fLat') &&
                            filled('fLng'),

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

        <!-- Preview and validate property photos -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('propertyForm');
                const defaultInput = document.getElementById('fDefaultPhoto');
                const additionalInput = document.getElementById('fPhotos');

                const defaultThumbs = document.getElementById('defaultPhotoThumbs');
                const additionalThumbs = document.getElementById('photoThumbs');
                const existingThumbs = document.getElementById('existingPhotoThumbs');

                if (!form || !defaultInput || !additionalInput ||
                    !defaultThumbs || !additionalThumbs) return;

                const MAX_SIZE = 5 * 1024 * 1024;
                const MAX_PHOTOS = 10;
                const ALLOWED_TYPES = ['image/jpeg', 'image/png'];

                let selectedAdditionalPhotos = [];
                let selectedDefaultPhoto = null;

                const translate = text =>
                    window.makanTranslate?.(text) ?? text;

                const existingImages = () =>
                    Array.from(existingThumbs?.querySelectorAll('.t') || []);

                function retainedImages() {
                    return existingImages().filter(image =>
                        !image.querySelector('input[type="checkbox"]')?.checked
                    );
                }

                function hasExistingCover() {
                    return retainedImages().some(image =>
                        image.dataset.existingCover === '1'
                    );
                }

                function totalPhotos() {
                    return retainedImages().length +
                        defaultInput.files.length +
                        additionalInput.files.length;
                }

                function validateFile(file) {
                    if (!ALLOWED_TYPES.includes(file.type)) {
                        return translate('Only JPG and PNG images are allowed.');
                    }

                    if (file.size > MAX_SIZE) {
                        return translate('Each photo must not exceed 5 MB.');
                    }

                    return '';
                }

                function updatePhotoState() {
                    const hasNewCover = defaultInput.files.length > 0;

                    defaultInput.required = !hasExistingCover();

                    existingImages().forEach(image => {
                        const deleted = image.querySelector(
                            'input[type="checkbox"]'
                        )?.checked;

                        const badge = image.querySelector('.cover');

                        image.style.opacity = deleted ? '0.4' : '1';

                        if (badge) {
                            badge.hidden = hasNewCover || deleted;
                        }
                    });
                }

                function syncPhotoInput(input, photos) {
                    const transfer = new DataTransfer();

                    photos.forEach(file => {
                        transfer.items.add(file);
                    });

                    input.files = transfer.files;
                }

                function renderPreviews(input, container, isCover = false) {
                    container.replaceChildren();

                    Array.from(input.files).forEach((file, index) => {
                        const preview = document.createElement('div');
                        preview.className = 't';
                        preview.style.position = 'relative';

                        const img = document.createElement('img');
                        const objectUrl = URL.createObjectURL(file);

                        img.src = objectUrl;
                        img.alt = translate('New property photo');
                        img.style.cssText =
                            'width:100%;height:100%;object-fit:cover;';

                        img.addEventListener('load', () => {
                            URL.revokeObjectURL(objectUrl);
                        }, {
                            once: true
                        });

                        img.addEventListener('error', () => {
                            URL.revokeObjectURL(objectUrl);
                        }, {
                            once: true
                        });

                        preview.appendChild(img);

                        if (isCover) {
                            const badge = document.createElement('span');
                            badge.className = 'cover';
                            badge.textContent = translate('Cover');
                            preview.appendChild(badge);
                        }

                        const removeBtn = document.createElement('button');

                        removeBtn.type = 'button';
                        removeBtn.className = 'btn btn-danger btn-sm';
                        removeBtn.innerHTML = '<i class="bi bi-x-lg"></i>';
                        removeBtn.title = translate('Remove photo');
                        removeBtn.setAttribute(
                            'aria-label',
                            translate('Remove photo')
                        );

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

                        removeBtn.addEventListener('click', function() {
                            if (isCover) {
                                selectedDefaultPhoto = null;

                                syncPhotoInput(defaultInput, []);
                            } else {
                                selectedAdditionalPhotos.splice(index, 1);

                                syncPhotoInput(
                                    additionalInput,
                                    selectedAdditionalPhotos
                                );
                            }

                            renderPreviews(input, container, isCover);
                            updatePhotoState();
                        });
                        preview.appendChild(removeBtn);
                        container.appendChild(preview);
                    });
                }

                function handleSelection(input, container, isCover) {
                    const errors = new Set();
                    const validFiles = [];

                    Array.from(input.files).forEach(file => {
                        const error = validateFile(file);

                        if (error) {
                            errors.add(error);
                            return;
                        }

                        validFiles.push(file);
                    });

                    if (isCover) {
                        const nextCover =
                            validFiles[0] || selectedDefaultPhoto;

                        const nextTotal =
                            retainedImages().length +
                            (nextCover ? 1 : 0) +
                            selectedAdditionalPhotos.length;

                        if (nextTotal > MAX_PHOTOS) {
                            errors.add(translate(
                                'A property can have a maximum of 10 photos.'
                            ));
                        } else {
                            selectedDefaultPhoto = nextCover;
                        }

                        syncPhotoInput(
                            defaultInput,
                            selectedDefaultPhoto ? [selectedDefaultPhoto] : []
                        );

                    } else {
                        const mergedPhotos = [...selectedAdditionalPhotos];

                        validFiles.forEach(file => {
                            const exists = mergedPhotos.some(photo =>
                                photo.name === file.name &&
                                photo.size === file.size &&
                                photo.lastModified === file.lastModified
                            );

                            if (!exists) {
                                mergedPhotos.push(file);
                            }
                        });

                        const nextTotal =
                            retainedImages().length +
                            (selectedDefaultPhoto ? 1 : 0) +
                            mergedPhotos.length;

                        if (
                            mergedPhotos.length > 9 ||
                            nextTotal > MAX_PHOTOS
                        ) {
                            errors.add(translate(
                                'A property can have a maximum of 10 photos.'
                            ));
                        } else {
                            selectedAdditionalPhotos = mergedPhotos;
                        }

                        syncPhotoInput(
                            additionalInput,
                            selectedAdditionalPhotos
                        );
                    }

                    renderPreviews(input, container, isCover);
                    updatePhotoState();

                    if (errors.size > 0) {
                        MakanPopup.error(
                            Array.from(errors).join('\n')
                        );
                    }
                }
                defaultInput.addEventListener('change', function() {
                    handleSelection(defaultInput, defaultThumbs, true);
                });

                additionalInput.addEventListener('change', function() {
                    handleSelection(additionalInput, additionalThumbs, false);
                });

                existingThumbs?.addEventListener('change', function() {
                    updatePhotoState();
                });

                form.addEventListener('submit', function(event) {
                    if (!defaultInput.files.length && !hasExistingCover()) {
                        event.preventDefault();

                        MakanPopup.error(translate(
                            'Please upload a default property photo.'
                        ));

                        return;
                    }

                    if (totalPhotos() > MAX_PHOTOS ||
                        additionalInput.files.length > 9) {
                        event.preventDefault();

                        MakanPopup.error(translate(
                            'A property can have a maximum of 10 photos.'
                        ));

                        return;
                    }

                    const selectedFiles = [
                        ...defaultInput.files,
                        ...additionalInput.files
                    ];

                    for (const file of selectedFiles) {
                        const error = validateFile(file);

                        if (error) {
                            event.preventDefault();
                            MakanPopup.error(error);
                            return;
                        }
                    }
                });

                updatePhotoState();
            });
        </script>
    @endpush
@endsection
