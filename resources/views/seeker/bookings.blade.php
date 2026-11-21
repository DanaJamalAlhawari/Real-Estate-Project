@extends('layouts.app')
@section('title', 'Makan — My bookings')
@section('roleStyle', 'seeker')
@section('bodyAttributes') data-role="seeker" data-page="bookings"@endsection
@section('content')
<x-seeker-navbar />

<main class="app-page container">
    <div class="page-head"><h1>My bookings</h1></div>
    @if (session('success'))
        <p class="alert alert-success" role="status">{{ session('success') }}</p>
    @endif
    <div class="bk-list" id="bookingsList">
        @forelse ($bookings as $booking)
            <article class="bk-card panel">
                <div class="d-flex flex-wrap justify-content-between gap-2">
                    <h2 class="h5">{{ $booking->property?->title ?? 'Property unavailable' }}</h2>
                    <span class="status status--{{ $booking->status === 'confirmed' ? 'success' : 'pending' }}">{{ ucfirst($booking->status) }}</span>
                </div>
                <p class="mb-2">{{ $booking->start_date->format('j M Y') }} – {{ $booking->end_date->format('j M Y') }}</p>
                <p class="mb-2">{{ number_format((float) $booking->total_price, 2) }} JOD</p>
                @if ($booking->property?->verification_status === 'verified' && in_array($booking->property->status, ['available', 'partially_rented'], true))
                    <a href="{{ route('seeker.property.details', ['property_id' => $booking->property_id]) }}">View property</a>
                @endif
                @if ($booking->payments->contains('status', 'successful'))
                    <a class="btn btn-outline-primary ms-2" href="{{ route('payments.mock.show', $booking) }}">View payment receipt</a>
                @elseif ($booking->status === 'confirmed')
                    <a class="btn btn-accent ms-2" href="{{ route('payments.mock.show', $booking) }}">Pay</a>
                @endif
            </article>
        @empty
            <p class="panel text-muted">You have no booking requests yet.</p>
        @endforelse
    </div>
</main>
@endsection
