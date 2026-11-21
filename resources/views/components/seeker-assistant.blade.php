@props(['cities', 'types', 'bookings'])

<details class="panel mb-4">
    <summary class="fw-semibold"><i class="bi bi-chat-dots me-2" aria-hidden="true"></i>Makan Assistant</summary>
    <p class="small text-muted my-2">How can I help?</p>
    <div class="d-flex flex-wrap gap-2">
        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse"
                data-bs-target="#assistant-cities" aria-controls="assistant-cities" aria-expanded="false">Find properties by city</button>
        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse"
                data-bs-target="#assistant-types" aria-controls="assistant-types" aria-expanded="false">Browse by property type</button>
        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse"
                data-bs-target="#assistant-bookings" aria-controls="assistant-bookings" aria-expanded="false">Check my bookings</button>
    </div>
    <div id="assistant-replies">
        <div class="collapse" id="assistant-cities" data-bs-parent="#assistant-replies">
            <div class="d-flex flex-wrap gap-2 pt-3">
                @forelse ($cities as $city)
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('seeker.search', ['city' => $city]) }}">{{ $city }}</a>
                @empty
                    <p class="text-muted mb-0">There are no available properties to browse right now.</p>
                @endforelse
            </div>
        </div>
        <div class="collapse" id="assistant-types" data-bs-parent="#assistant-replies">
            <div class="d-flex flex-wrap gap-2 pt-3">
                @forelse ($types as $type)
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('seeker.search', ['category' => $type]) }}">{{ \App\Models\Property::CATEGORIES[$type] }}</a>
                @empty
                    <p class="text-muted mb-0">There are no available property types right now.</p>
                @endforelse
            </div>
        </div>
        <div class="collapse" id="assistant-bookings" data-bs-parent="#assistant-replies">
            <ul class="small mt-3 mb-2">
                @forelse ($bookings as $booking)
                    <li>{{ $booking->property?->title ?? 'Property' }} — {{ ucfirst($booking->status) }}</li>
                @empty
                    <li>You have no booking requests yet.</li>
                @endforelse
            </ul>
            <a href="{{ route('seeker.bookings') }}">Open My Bookings</a>
        </div>
    </div>
</details>
