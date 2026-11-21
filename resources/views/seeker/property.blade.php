@extends('layouts.app')
@section('title', 'Makan — '.$property->title)
@section('roleStyle', 'seeker')
@section('bodyAttributes') data-role="seeker" data-page="property"@endsection
@section('content')
<x-seeker-navbar />

<main class="app-page container">
    <a href="{{ route('seeker.search') }}" class="back-link"><i class="bi bi-arrow-left"></i>Back to results</a>

    <div class="gallery mt-2 mb-4" aria-label="Property photos">
        @forelse ($property->images as $image)
            <a @class(['g-main' => $loop->first]) href="{{ $image->public_url }}" target="_blank" rel="noopener">
                <img src="{{ $image->public_url }}" alt="{{ $property->title }} — photo {{ $loop->iteration }}"
                     class="w-100 h-100 object-fit-cover" @unless($loop->first) loading="lazy" @endunless>
            </a>
        @empty
            <div class="g-main ph"><i class="bi bi-house-door"></i> No property photos yet.</div>
        @endforelse
    </div>

    <div class="row g-4 align-items-start">
        <div class="col-lg-8 d-flex flex-column gap-3">
            <div class="prop-head">
                <h1>{{ $property->title }}</h1>
                <p class="text-muted">{{ implode(', ', array_filter([$property->area, $property->city])) }}</p>
                <div class="facts">
                    <span class="fact">{{ \App\Models\Property::CATEGORIES[$property->category] ?? $property->category }}</span>
                    <span class="fact">{{ $property->bedrooms }} bedrooms</span>
                    <span class="fact">{{ $property->bathrooms }} bathrooms</span>
                    <span class="status status--info"><i class="bi bi-patch-check-fill"></i> Verified</span>
                </div>
            </div>
            <section class="panel">
                <h2 class="h5">About the home</h2>
                <p>{{ $property->description }}</p>
                <ul class="amenities list-unstyled mb-0">
                    @forelse ($property->services as $service)
                        <li class="amenity">{{ $service->name }}</li>
                    @empty
                        <li class="text-muted">No amenities listed.</li>
                    @endforelse
                </ul>
                <a class="btn btn-outline-primary mt-3" href="{{ route('seeker.reviews.index', $property) }}">Reviews</a>
            </section>
            <section class="panel">
                <h2 class="h5">Property owner</h2>
                <p>{{ $property->agent?->display_name ?? 'Name not listed' }}</p>
                @if ($property->agent?->agentProfile?->agency_name)
                    <p class="text-muted">{{ $property->agent->agentProfile->agency_name }}</p>
                @endif
                <form method="post" action="{{ route('seeker.property.conversation', $property) }}">
                    @csrf
                    <button class="btn btn-outline-primary" type="submit">Message owner</button>
                </form>
            </section>
        </div>

        <div class="col-lg-4">
            <aside class="panel sticky-box" aria-label="Booking">
                @if ($property->price !== null)
                    <p><b class="fs-3">{{ number_format((float) $property->price, 2) }} JOD</b> per month</p>
                    @if ($errors->any())
                        <p class="text-danger" role="alert">{{ $errors->first() }}</p>
                    @endif
                    <form method="post" action="{{ route('seeker.bookings.store', $property) }}">
                        @csrf
                        <div class="mb-3">
                            <label for="moveIn" class="form-label">Move-in date</label>
                            <input type="date" id="moveIn" name="moveIn" class="form-control"
                                   min="{{ today()->toDateString() }}" value="{{ old('moveIn') }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="duration" class="form-label">Duration</label>
                            <select class="form-select" id="duration" name="duration">
                                @foreach ([3, 6, 12] as $months)
                                    <option value="{{ $months }}" @selected(old('duration', 6) == $months)>{{ $months }} months</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-accent btn-lg w-100">Request booking</button>
                    </form>
                @else
                    <p>Contact the owner for a price before booking.</p>
                @endif
                <div class="d-flex gap-2 mt-3">
                    <form action="{{ route($saved ? 'seeker.favorites.destroy' : 'seeker.favorites.store', $property) }}" method="post">
                        @csrf
                        @if ($saved) @method('DELETE') @endif
                        <button class="btn btn-outline-secondary" type="submit">{{ $saved ? 'Unsave' : 'Save' }}</button>
                    </form>
                    <button type="button" id="sharePropertyBtn" class="btn btn-outline-secondary"
                            data-share-url="{{ route('seeker.property.details', ['property_id' => $property->id]) }}"
                            data-share-title="{{ $property->title }}">Share</button>
                </div>
                <p id="sharePropertyStatus" class="small mt-2" aria-live="polite"></p>
                <input id="sharePropertyLink" class="form-control" aria-label="Property link" readonly hidden>
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
                        status.textContent = "Link copied.";
                    } catch {
                        link.value = url;
                        link.hidden = false;
                        link.focus();
                        link.select();
                        status.textContent = "Copy the property link below.";
                    }
                });
            })();
        </script>
@endsection
