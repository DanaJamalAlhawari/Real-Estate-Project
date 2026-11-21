@extends('layouts.app')
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush
@section('title', isset($property) ? 'Makan — Edit property' : 'Makan — Add property')
@section('roleStyle', 'owner')
@section('bodyAttributes') data-role="owner" data-page="property-form"@endsection
@section('content')
<input type="checkbox" class="sidebar-toggle" id="sidebarToggle" aria-label="Collapse the menu" />
<!-- ===== Topbar ===== -->
    <header class="app-topbar">
      <button
        class="icon-btn d-lg-none"
        type="button"
        data-bs-toggle="offcanvas"
        data-bs-target="#appSidebar"
        aria-label="Menu"
      >
        <i class="bi bi-list"></i>
      </button>
      <label
        for="sidebarToggle"
        class="icon-btn sidebar-burger d-none d-lg-inline-flex"
        title="Collapse or expand the menu"
        aria-hidden="true"
      >
        <i class="bi bi-list"></i>
      </label>
      <a class="brand" href="{{ route('owner.dashboard') }}"
        ><span class="brand-mark">M</span><span>Makan</span></a
      >
      <div class="ms-auto d-flex align-items-center gap-1">
        <!-- Messages: icon with unread count -->
        <a
          class="icon-btn"
          href="{{ route('owner.chat') }}"
          aria-label="Messages, 1 unread"
        >
          <i class="bi bi-chat-dots"></i><span class="icon-badge">1</span>
        </a>
        <div class="dropdown">
          <button
            class="icon-btn"
            type="button"
            data-bs-toggle="dropdown"
            aria-label="Notifications"
          >
            <i class="bi bi-bell"></i><span class="dot"></span>
          </button>
          <div
            class="dropdown-menu dropdown-menu-end shadow-sm p-0"
            style="width: 320px"
          >
            <div class="px-3 py-2 border-bottom fw-semibold">Notifications</div>
            <div class="py-1">
              <a
                class="dropdown-item d-flex gap-2 py-2 text-wrap"
                href="{{ route('owner.requests') }}"
              >
                <span
                  class="kpi-icon flex-shrink-0"
                  style="width: 34px; height: 34px; font-size: 1rem"
                  ><i class="bi bi-inbox"></i
                ></span>
                <span class="small">
                  <span class="d-block">New booking request from Sara Khaled</span>
                  <span class="text-muted" style="font-size: 0.72rem"
                    >10 minutes ago</span
                  >
                </span>
              </a>
              <a
                class="dropdown-item d-flex gap-2 py-2 text-wrap"
                href="{{ route('owner.properties') }}"
              >
                <span
                  class="kpi-icon flex-shrink-0"
                  style="width: 34px; height: 34px; font-size: 1rem"
                  ><i class="bi bi-shield-check"></i
                ></span>
                <span class="small">
                  <span class="d-block"
                    >Your Al Jubeiha studio has been verified</span
                  >
                  <span class="text-muted" style="font-size: 0.72rem"
                    >1 hour ago</span
                  >
                </span>
              </a>
            </div>
          </div>
        </div>
        <div class="dropdown">
          <button
            class="app-user app-user--avatar"
            type="button"
            data-bs-toggle="dropdown"
            aria-expanded="false"
            aria-label="Account menu, {{ auth()->user()->display_name }}"
          >
            <span class="avatar">{{ auth()->user()->display_initial }}</span>
          </button>
          <ul class="dropdown-menu dropdown-menu-end shadow-sm">
            <li><h6 class="dropdown-header">{{ auth()->user()->display_name }}</h6></li>
            <li><a class="dropdown-item" href="{{ route('owner.account') }}">My account</a></li>
            <li><hr class="dropdown-divider" /></li>
            <li>
              <x-logout-form />
            </li>
          </ul>
        </div>
      </div>
    </header>

    <div class="app-shell">
      <!-- ===== Sidebar ===== -->
      <aside
        class="app-sidebar offcanvas-lg offcanvas-start"
        tabindex="-1"
        id="appSidebar"
      >
        <div class="offcanvas-header border-bottom">
          <a class="brand" href="{{ route('home') }}"
            ><span class="brand-mark">M</span><span>Makan</span></a
          >
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="offcanvas"
            data-bs-target="#appSidebar"
            aria-label="Close"
          ></button>
        </div>
        <div class="offcanvas-body">
          <div class="side-label">Property owner</div>
          <a class="side-link" href="{{ route('owner.dashboard') }}" title="Dashboard"
            ><i class="bi bi-grid-1x2"></i
            ><span class="flex-grow-1">Dashboard</span></a
          >
          <a class="side-link" href="{{ route('owner.properties') }}" title="My properties"
            ><i class="bi bi-house-door"></i
            ><span class="flex-grow-1">My properties</span></a
          >
          <a
            class="side-link active"
            href="{{ route('owner.property-form') }}"
            aria-current="page"
            title="Add property"
            ><i class="bi bi-plus-square"></i
            ><span class="flex-grow-1">Add property</span></a
          >
          <a class="side-link" href="{{ route('owner.requests') }}" title="Booking requests"
            ><i class="bi bi-inbox"></i
            ><span class="flex-grow-1">Booking requests</span
            ><span class="side-badge">3</span></a
          >
          <a class="side-link" href="{{ route('owner.chat') }}" title="Messages"
            ><i class="bi bi-chat-dots"></i
            ><span class="flex-grow-1">Messages</span
            ><span class="side-badge">1</span></a
          >
          <a class="side-link" href="{{ route('owner.account') }}" title="My account"
            ><i class="bi bi-person-badge"></i
            ><span class="flex-grow-1">My account</span></a
          >
        </div>
      </aside>

      <!-- ===== Content ===== -->
      <main class="app-main">
        @isset($property)
          <div class="page-head">
            <div>
              <h1>Edit property</h1>
              <p>{{ $property->title }}</p>
            </div>
            <a href="{{ route('owner.properties') }}" class="btn btn-outline-secondary">My properties</a>
          </div>
          <form class="panel form-section" action="{{ route('properties.verification.store') }}" method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="property_id" value="{{ $property->id }}">
            <h2>Ownership verification</h2>
            <p>Status: {{ ucfirst($property->verification_status) }}</p>
            @if (session('success'))
              <p class="text-success" role="status">{{ session('success') }}</p>
            @endif
            @if ($errors->any())
              <p class="text-danger" role="alert">{{ $errors->first() }}</p>
            @endif
            <label for="ownershipDocument" class="form-label">Ownership document (PDF, JPG, or PNG, up to 5 MB)</label>
            <input id="ownershipDocument" class="form-control mb-3" type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" required>
            <button class="btn btn-accent" type="submit">Upload document</button>
          </form>
        @else
          <div class="page-head">
            <div>
              <h1>Add property</h1>
              <p>The more details you fill in, the more accurately your property matches with seekers.</p>
            </div>
          </div>
        @endisset
        <div class="row g-4 align-items-start">
          <div class="col-lg-8">
            <form id="propertyForm" method="POST" action="{{ isset($property) ? route('properties.update', $property->id) : route('properties.store') }}" enctype="multipart/form-data">
              @csrf
              @if(isset($property))
                @method('PUT')
              @endif
              @if ($errors->any())
                <div class="alert alert-danger mt-3" role="alert">
                  <strong>Failed to save property:</strong>
                  <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                      <li>{{ $error }}</li>
                    @endforeach
                  </ul>
                </div>
              @endif
          <!-- 1. Basic details -->

          <section class="panel form-section" aria-labelledby="secBasics">

            <h2 id="secBasics">1. Basic details</h2>

            <p>Property type and specs.</p>

            <div class="row g-3">

              <div class="col-12">

                <label for="fTitle" class="form-label">Listing title</label>

                <input

                  type="text"

                  class="form-control"

                  id="fTitle"

                  name="title"

                  value="{{ old('title', $property->title ?? '') }}"

                  required />

              </div>

              <div class="col-sm-6 col-xl-3">

                <label for="fCategory" class="form-label">

                  Property Category

                </label>

                <select

                  class="form-select"

                  id="fCategory"

                  name="category"

                  required>

                  <option value="">Select Category</option>

                  <option value="student_apartment"

                    {{ old('category', $property->category ?? '') == 'student_apartment' ? 'selected' : '' }}>

                    Student Apartment

                  </option>

                  <option value="apartment"

                    {{ old('category', $property->category ?? '') == 'apartment' ? 'selected' : '' }}>

                    Apartment

                  </option>

                  <option value="vacation"

                    {{ old('category', $property->category ?? '') == 'vacation' ? 'selected' : '' }}>

                    Vacation

                  </option>

                </select>

              </div>

              <div class="col-sm-6 col-xl-3">

                <label for="fSize" class="form-label">Size (m²)</label>

                <input

                  type="number"

                  class="form-control"

                  id="fSize"

                  name="size"

                  value="{{ old('size', $property->size ?? '') }}"

                  min="1" />

              </div>

              <div class="col-sm-6 col-xl-3">

                <label for="fFloor" class="form-label">Floor</label>

                <input

                  type="number"

                  class="form-control"

                  id="fFloor"

                  name="floor"

                  value="{{ old('floor', $property->floor ?? '') }}" />

              </div>

              <div class="row g-3 mt-2">

                <!-- Bedrooms -->

                <div class="col-md-6">

                  <label for="fBedrooms" class="form-label">

                    Bedrooms

                  </label>

                  <input

                    type="number"

                    class="form-control"

                    id="fBedrooms"

                    name="bedrooms"

                    min="0"

                    value="{{ old('bedrooms', $property->bedrooms ?? 0) }}"

                    required>

                </div>

                <!-- Bathrooms -->

                <div class="col-md-6">

                  <label for="fBathrooms" class="form-label">

                    Bathrooms

                  </label>

                  <input

                    type="number"

                    class="form-control"

                    id="fBathrooms"

                    name="bathrooms"

                    min="0"

                    value="{{ old('bathrooms', $property->bathrooms ?? 0) }}"

                    required>

                </div>

              </div>

              <fieldset class="col-12">

                <legend class="form-label fs-6">Furnished?</legend>

                <div

                  class="d-flex flex-wrap gap-2"

                  role="radiogroup"

                  id="furnishing">

                  <input

                    type="radio"

                    class="btn-check"

                    id="fFurnished"

                    name="furnished"

                    value="yes"

                    {{ old('furnished', $property->furnished ?? 'yes') == 'yes' ? 'checked' : '' }} />

                  <label class="chip-select" for="fFurnished"><i class="bi bi-lamp"></i>Furnished</label>

                  <input

                    type="radio"

                    class="btn-check"

                    id="fUnfurnished"

                    name="furnished"

                    value="no"

                    {{ old('furnished', $property->furnished ?? 'yes') == 'no' ? 'checked' : '' }} />

                  <label class="chip-select" for="fUnfurnished"><i class="bi bi-square"></i>Unfurnished</label>

                </div>

              </fieldset>

            </div>

            <!-- Amenities srevices -->

            <fieldset class="mt-3">

              <legend class="visually-hidden">Amenities</legend>

              <div class="d-flex flex-wrap gap-2" id="amenities">

                @foreach ($services ?? [] as $service)

                <input

                  type="checkbox"

                  class="btn-check"

                  id="service{{ $service->id }}"

                  name="services[]"

                  value="{{ $service->id }}"

                  {{ in_array(

            $service->id,

            old(

                'services',

                isset($property)

                    ? $property->services->pluck('id')->all()

                    : []

            )

        ) ? 'checked' : '' }} />

                <label

                  class="chip-select"

                  for="service{{ $service->id }}">

                  @if (!empty($service->icon_name))

                  <i class="bi {{ $service->icon_name }}"></i>

                  @endif

                  {{ $service->name }}

                </label>

                @endforeach

                <!-- Add Other Service -->

                <div class="mt-3">

                  <label for="customService" class="form-label">

                    Add Other Service

                  </label>

                  <div class="input-group">

                    <input

                      type="text"

                      class="form-control"

                      id="customService"

                      placeholder="e.g. Solar Panels"

                      value="{{ old('new_service') }}">

                    <button

                      type="button"

                      class="btn btn-success"

                      id="addServiceBtn">

                      <i class="bi bi-plus-lg"></i>

                      Add Service

                    </button>

                  </div>

                  <small class="text-muted">

                    Can't find a service? Add it here.

                  </small>

                  <!-- New services will appear here -->

                  <div id="newServicesList" class="d-flex flex-wrap gap-2 mt-3"></div>

                </div>

              </div>

      </fieldset>

      </section>

      <!-- 2. Description + smart assistant -->

      <section class="panel form-section" aria-labelledby="secDesc">

        <h2 id="secDesc">2. Description</h2>

        <p>

          Write it yourself, or let the assistant write it from the

          details you've filled in.

        </p>

        <div class="ai-box flex-wrap mb-3">

          <i class="bi bi-magic"></i>

          <div class="flex-grow-1">

            <b class="d-block">Smart listing writer</b>

            <span class="small text-muted">Writes a ready Arabic and English description in

              seconds.</span>

          </div>

          <button

            type="button"

            class="btn btn-primary"

            data-action="ai-write"

            aria-controls="fDescAr fDescEn">

            <i class="bi bi-stars"></i>

            <span>Write it for me</span>

          </button>

        </div>

        <ul class="nav seg-tabs mb-2" role="tablist">

          <li class="nav-item" role="presentation">

            <button

              class="nav-link"

              data-bs-toggle="tab"

              data-bs-target="#desc-ar"

              type="button"

              role="tab"

              aria-controls="desc-ar"

              aria-selected="false">

              Arabic

            </button>

          </li>

          <li class="nav-item" role="presentation">

            <button

              class="nav-link active"

              data-bs-toggle="tab"

              data-bs-target="#desc-en"

              type="button"

              role="tab"

              aria-controls="desc-en"

              aria-selected="true">

              English

            </button>

          </li>

        </ul>

        <div class="tab-content">

          <div

            class="tab-pane fade"

            id="desc-ar"

            role="tabpanel"

            tabindex="0">

            <label for="fDescAr" class="visually-hidden">Description in Arabic</label>

            <textarea

              class="form-control"

              id="fDescAr"

              name="description_ar"

              rows="5"

              placeholder="Write the description in Arabic"></textarea>

          </div>

          <div

            class="tab-pane fade show active"

            id="desc-en"

            role="tabpanel"

            tabindex="0">

            <label for="fDescEn" class="visually-hidden">Description in English</label>

            <textarea

              class="form-control"

              id="fDescEn"

              name="description"

              rows="5"

              placeholder="Description in English..."

              required>{{ old('description', $property->description ?? '') }}</textarea>

          </div>

        </div>

      </section>

      <!-- 3. Photos -->

      <section class="panel form-section" aria-labelledby="secPhotos">

        <h2 id="secPhotos">3. Photos</h2>

        <p>Real, recent photos. The first photo is the cover.</p>

        <label class="dropzone d-block mb-3" for="fPhotos">

          <i class="bi bi-cloud-arrow-up"></i>

          <span class="d-block fw-semibold text-body">Drag photos here, or

            <span class="text-primary-bt text-decoration-underline">choose from your device</span></span>

          <span class="d-block small">JPG or PNG · up to 10 photos</span>

        </label>

        <!-- Upload new property photos -->

        <input

          type="file"

          class="visually-hidden"

          id="fPhotos"

          name="photos[]"

          accept="image/jpeg,image/png"

          multiple />

        <!-- images -->

        <div class="thumbs" id="photoThumbs">

          @if(isset($property) && $property->images->isNotEmpty())

          @foreach($property->images as $image)

          <div class="t" id="property-image-{{ $image->id }}"

            style="position: relative;">

            <img

              src="{{ asset('storage/' . $image->image_url) }}"

              alt="Property image"

              style="width: 100%; height: 100%; object-fit: cover;">

            @if($image->is_primary)

            <span class="cover">Cover</span>

            @endif

            <!-- Mark existing image for deletion -->

            <label

              style="position: absolute; top: 5px; right: 5px;

                           background: #dc3545; color: white;

                           border-radius: 50%; width: 25px; height: 25px;

                           display: flex; align-items: center;

                           justify-content: center; cursor: pointer;"

              title="Delete image">

              <input

                type="checkbox"

                name="deleted_images[]"

                value="{{ $image->id }}"

                class="visually-hidden"

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

      @unless(isset($property))
        <section class="panel form-section" aria-labelledby="secOwnership">
          <h2 id="secOwnership">Ownership verification</h2>
          <label for="createOwnershipDocument" class="form-label">Ownership document (optional, up to 5 MB)</label>
          <input id="createOwnershipDocument" class="form-control" type="file" name="document" accept=".pdf,.jpg,.jpeg,.png">
        </section>
      @endunless

      <!-- 4. Location -->

      <section class="panel form-section" aria-labelledby="secLocation">

        <h2 id="secLocation">4. Location</h2>

        <p>Search for the neighborhood, then place the marker at the exact building.</p>

        <div class="row g-3 mb-3">

          <div class="col-md-6">

            <label for="fCity" class="form-label">City</label>

            <input type="text" class="form-control" id="fCity" name="city" value="{{ old('city', $property->city ?? '') }}">

          </div>

          <div class="col-md-6">

            <label for="fArea" class="form-label">Area / Neighborhood</label>

            <input type="text" class="form-control" id="fArea" name="area" value="{{ old('area', $property->area ?? '') }}">

          </div>

          <div class="col-12">

            <label for="fStreet" class="form-label">Street / Building Address</label>

            <input type="text" class="form-control" id="fStreet" name="address" value="{{ old('address', $property->address ?? '') }}">

          </div>

          <div class="col-12">

            <label for="locationSearch" class="form-label">Search Property Location</label>

            <div class="input-group">

              <input type="text" id="locationSearch" class="form-control" placeholder="e.g. Khalda, Amman">

              <button type="button" id="searchLocationBtn" class="btn btn-primary"><i class="bi bi-search"></i> Search</button>

            </div>

            <div id="locationResults" class="list-group mt-2" aria-live="polite"></div>

          </div>

          <div class="col-12">

            <div id="locationMap" style="height:400px;width:100%;border-radius:12px;overflow:hidden;"></div>

            <input type="hidden" id="fLat" name="latitude" value="{{ old('latitude', $property->latitude ?? '') }}">

            <input type="hidden" id="fLng" name="longitude" value="{{ old('longitude', $property->longitude ?? '') }}">

            <p class="small text-muted mt-2 mb-0">Choose a search result to move the map, then drag the pin to the exact building. You can also click the map directly.</p>

          </div>

        </div>

      </section>

      <!-- 5. Property Price -->

      <section class="panel form-section" aria-labelledby="secPrice">

        <h2 id="secPrice">5. Property Price</h2>

        <p>Set the monthly rental price for your property.</p>

        <div class="row g-3">

          <div class="col-sm-6">

            <label for="fRent" class="form-label">

              Monthly Rent (JOD)

            </label>

            <input

              type="number"

              class="form-control"

              id="fRent"

              name="price"

              value="{{ old('price', $property->price ?? '') }}"

              min="0"

              step="0.01"

              required />

          </div>

        </div>

      </section>

      <!-- Save buttons -->

      <div class="d-flex flex-wrap justify-content-end gap-2 mt-4">

        <button

          type="submit"

          class="btn btn-outline-primary btn-lg"

          name="action"

          value="draft">

          Save as draft

        </button>

        <button

          type="submit"

          class="btn btn-accent btn-lg"

          name="action"

          value="publish">

          Publish listing

        </button>

      </div>

      </form>

    </div>

    <!-- ===== Side summary ===== -->

    <aside class="col-lg-4">

      <div class="sticky-box d-flex flex-column gap-3">

        <!-- Listing readiness -->

        <section class="panel" aria-labelledby="readyTitle">

          <h2 class="fs-6 mb-2" id="readyTitle">Listing readiness</h2>

          <div class="progress mb-3" role="progressbar"

            aria-label="Listing readiness"

            aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">

            <div class="progress-bar" id="readinessBar" style="width: 0%"></div>

          </div>

          <ul class="list-clean d-flex flex-column gap-2 small" id="readinessList">

            <li class="d-flex align-items-center gap-2" data-check="basic">

              <i class="bi bi-exclamation-circle-fill ic-warn"></i>Basic details

            </li>

            <li class="d-flex align-items-center gap-2" data-check="description">

              <i class="bi bi-exclamation-circle-fill ic-warn"></i>Description

            </li>

            <li class="d-flex align-items-center gap-2" data-check="photos">

              <i class="bi bi-exclamation-circle-fill ic-warn"></i>Photos

            </li>

            <li class="d-flex align-items-center gap-2" data-check="location">

              <i class="bi bi-exclamation-circle-fill ic-warn"></i>Location

            </li>

            <li class="d-flex align-items-center gap-2" data-check="price">

              <i class="bi bi-exclamation-circle-fill ic-warn"></i>Price

            </li>

          </ul>

        </section>

        <!-- After publishing -->

        <section class="panel" aria-labelledby="afterTitle">

          <h2 class="fs-6 mb-2" id="afterTitle">After publishing</h2>

          <div class="d-flex gap-2 small text-muted mb-2">

            <i class="bi bi-patch-check text-primary-bt"></i>

            <span>Your property listing will be submitted for admin review.</span>

          </div>

          <div class="d-flex gap-2 small text-muted">

            <i class="bi bi-arrow-repeat text-primary-bt"></i>

            <span>You can edit your property details whenever needed.</span>

          </div>

        </section>

      </div>

    </aside>

</div>
      </main>
    </div>
@push('scripts')

<script>

  document.addEventListener('DOMContentLoaded', function() {

    const serviceInput = document.getElementById('customService');

    const addServiceBtn = document.getElementById('addServiceBtn');

    const servicesList = document.getElementById('newServicesList');

    addServiceBtn.addEventListener('click', function() {

      const serviceName = serviceInput.value.trim();

      if (!serviceName) {

        alert('Please enter a service name.');

        return;

      }

      const existing = Array.from(

        servicesList.querySelectorAll('input[name="new_services[]"]')

      );

      if (existing.some(input =>

          input.value.toLowerCase() === serviceName.toLowerCase()

        )) {

        alert('Service already added.');

        return;

      }

      const chip = document.createElement('div');

      chip.className = 'd-flex align-items-center gap-2 border rounded-pill px-3 py-2';

      const hiddenInput = document.createElement('input');

      hiddenInput.type = 'hidden';

      hiddenInput.name = 'new_services[]';

      hiddenInput.value = serviceName;

      const serviceText = document.createElement('span');

      serviceText.textContent = serviceName;

      const removeBtn = document.createElement('button');

      removeBtn.type = 'button';

      removeBtn.className = 'btn btn-sm p-0 border-0';

      removeBtn.innerHTML = '<i class="bi bi-x-lg"></i>';

      removeBtn.addEventListener('click', function() {

        chip.remove();

      });

      chip.append(hiddenInput, serviceText, removeBtn);

      servicesList.appendChild(chip);

      serviceInput.value = '';

    });

  });

</script>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>

  document.addEventListener('DOMContentLoaded', function() {

    const el = id => document.getElementById(id);

    const mapEl = el('locationMap');

    const latEl = el('fLat');

    const lngEl = el('fLng');

    const searchEl = el('locationSearch');

    const button = el('searchLocationBtn');

    const results = el('locationResults');

    const cityEl = el('fCity');

    const areaEl = el('fArea');

    if (!mapEl || !latEl || !lngEl || typeof L === 'undefined') {

      console.error('Location map: missing required elements or Leaflet.');

      return;

    }

    const savedLat = Number(latEl.value);

    const savedLng = Number(lngEl.value);

    const saved = latEl.value.trim() !== '' && lngEl.value.trim() !== '' &&

      Number.isFinite(savedLat) && Number.isFinite(savedLng) &&

      Math.abs(savedLat) <= 90 && Math.abs(savedLng) <= 180;

    const map = L.map(mapEl).setView(saved ? [savedLat, savedLng] : [31.9539, 35.9106], saved ? 16 : 12);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {

      maxZoom: 19,

      attribution: '&copy; OpenStreetMap contributors'

    }).addTo(map);

    let marker = null;

    function setLocation(lat, lng, name = 'Property Location') {

      latEl.value = lat.toFixed(7);

      lngEl.value = lng.toFixed(7);

      // Notify Listing Readiness when the property location changes.*

      latEl.dispatchEvent(new Event('input', {

        bubbles: true

      }));

      lngEl.dispatchEvent(new Event('input', {

        bubbles: true

      }));

      if (!marker) {

        marker = L.marker([lat, lng], {

          draggable: true

        }).addTo(map);

        marker.on('dragend', function() {

          const pos = marker.getLatLng();

          setLocation(pos.lat, pos.lng, 'Selected Property Location');

        });

      } else {

        marker.setLatLng([lat, lng]);

      }

      marker.unbindTooltip();

      marker.bindTooltip(name, {

        permanent: true,

        direction: 'top',

        offset: [0, -10]

      }).openTooltip();

    }

    if (saved) {

      setLocation(savedLat, savedLng, [areaEl?.value, cityEl?.value].filter(Boolean).join(', ') || 'Property Location');

    }

    map.on('click', event => setLocation(event.latlng.lat, event.latlng.lng, 'Selected Property Location'));

    async function searchLocation() {

      const query = searchEl.value.trim();

      if (query.length < 3) {

        results.textContent = 'Please enter at least 3 characters.';

        return;

      }

      button.disabled = true;

      results.textContent = 'Searching...';

      try {

        const params = new URLSearchParams({

          q: query + ', Jordan',

          limit: '8',

          lang: 'en',

          lat: '31.9539',

          lon: '35.9106'

        });

        const response = await fetch('https://photon.komoot.io/api/?' + params.toString());

        if (!response.ok) throw new Error('Geocoding HTTP ' + response.status);

        const data = await response.json();

        const places = (data.features || []).filter(place => {

          const country = (place.properties?.country || '').toLowerCase();

          return country === 'jordan' || country === 'الأردن';

        });

        results.replaceChildren();

        if (!places.length) {

          results.textContent = 'No results in Jordan. Try the city name or select the place manually on the map.';

          return;

        }

        places.forEach(place => {

          const p = place.properties || {};

          const [lng, lat] = place.geometry.coordinates;

          const name = [...new Set([p.name, p.district, p.city, p.state].filter(Boolean))].join(', ');

          const option = document.createElement('button');

          option.type = 'button';

          option.className = 'list-group-item list-group-item-action';

          option.textContent = name || 'Location result';

          option.addEventListener('click', () => {

            map.setView([lat, lng], 16);

            setLocation(lat, lng, name || 'Property Location');

            if (cityEl) cityEl.value = p.city || p.state || '';

            if (areaEl) areaEl.value = p.district || p.suburb || p.locality || p.name || '';

            searchEl.value = name;

            results.replaceChildren();

          });

          results.appendChild(option);

        });

      } catch (error) {

        console.error('Location search failed:', error);

        results.textContent = 'Search service unavailable. Click the map to select the property location manually.';

      } finally {

        button.disabled = false;

      }

    }

    if (searchEl && button && results) {

      button.addEventListener('click', searchLocation);

      searchEl.addEventListener('keydown', event => {

        if (event.key === 'Enter') {

          event.preventDefault();

          searchLocation();

        }

      });

    }

    setTimeout(() => map.invalidateSize(), 250);

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

        description: filled('fDescEn') || filled('fDescAr'),

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
