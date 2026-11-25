@extends('layouts.app')
@section('title', __('Makan — My bookings'))
@section('roleStyle', 'seeker')
@section('bodyAttributes') data-role="seeker" data-page="bookings"@endsection
@section('content')
<x-seeker-navbar />

<main class="app-page container">
    <div class="page-head">
        <div>
            <h1>{{ __('My bookings') }}</h1>
            <p>{{ __('Track requests, complete checkout, and find your receipts.') }}</p>
        </div>
        <a class="btn btn-primary booking-button" href="{{ route('payments.mock.cart') }}"><i class="bi bi-bag" aria-hidden="true"></i> {{ __('Payment cart') }}</a>
    </div>
    @if (session('success'))
        <p class="alert alert-success" role="status">{{ __(session('success')) }}</p>
    @endif
    @if (session('error'))
        <p class="alert alert-danger" role="alert">{{ __(session('error')) }}</p>
    @endif
    <div class="bk-list" id="bookingsList">
        @forelse ($bookings as $booking)
            @php
                $paid = $booking->payments->contains('status', 'successful');
                $refunded = $booking->payments->contains('status', 'refunded');
                $canCancel = in_array($booking->status, ['pending', 'confirmed'], true) && ! $paid && ! $refunded;
                $canViewProperty = $booking->property && ! $booking->property->trashed()
                    && $booking->property->verification_status === 'verified'
                    && in_array($booking->property->status, ['available', 'partially_rented'], true);
            @endphp
            <article class="panel booking-card">
                <div class="booking-card__summary">
                    <div>
                        <h2 class="h5">{{ $booking->property?->localized('title') ?? __('Property unavailable') }}</h2>
                        <p class="booking-card__dates">{{ $booking->start_date->translatedFormat('j M Y') }} – {{ $booking->end_date->translatedFormat('j M Y') }}</p>
                    </div>
                    <div class="booking-card__total">
                        <span class="status status--{{ $paid || $booking->status === 'confirmed' ? 'success' : 'pending' }}">{{ $paid ? __('Paid') : ($refunded ? __('Refunded') : __(\Illuminate\Support\Str::headline($booking->status))) }}</span>
                        <strong>{{ number_format((float) $booking->total_price, 2) }} {{ __('JOD') }}</strong>
                    </div>
                </div>
                @if ($paid || $canViewProperty || $canCancel)
                    <div class="booking-card__actions">
                        @if ($paid)
                            <a class="btn btn-outline-primary booking-button" href="{{ route('payments.mock.show', $booking) }}"><i class="bi bi-receipt" aria-hidden="true"></i> {{ __('View receipt') }}</a>
                        @elseif ($booking->status === 'confirmed' && ! $refunded)
                            <a class="btn btn-primary booking-button" href="{{ route('payments.mock.show', $booking) }}"><i class="bi bi-credit-card" aria-hidden="true"></i> {{ __('Pay booking') }}</a>
                        @endif
                        @if ($canViewProperty)
                            <a class="btn btn-outline-primary booking-button" href="{{ route('seeker.property.details', ['property_id' => $booking->property_id]) }}"><i class="bi bi-house-door" aria-hidden="true"></i> {{ __('View property') }}</a>
                        @endif
                        @if ($canCancel)
                            <button type="button" class="btn btn-outline-danger booking-button booking-card__cancel" data-bs-toggle="modal" data-bs-target="#cancel-booking-{{ $booking->id }}">
                                <i class="bi bi-x-lg" aria-hidden="true"></i> {{ __('Cancel request') }}
                            </button>
                        @endif
                    </div>
                @endif
            </article>
            @if ($canCancel)
                <div class="modal fade" id="cancel-booking-{{ $booking->id }}" tabindex="-1" aria-labelledby="cancel-booking-title-{{ $booking->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content rounded-4">
                            <div class="modal-header">
                                <h2 class="modal-title h5" id="cancel-booking-title-{{ $booking->id }}">{{ __('Cancel this request?') }}</h2>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                            </div>
                            <div class="modal-body">
                                <p class="mb-0">{{ __('Your booking request for') }} <strong>{{ $booking->property?->localized('title') ?? __('this property') }}</strong> {{ __('from') }} {{ $booking->start_date->translatedFormat('j M Y') }} {{ __('to') }} {{ $booking->end_date->translatedFormat('j M Y') }} {{ __('will be cancelled.') }}</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary booking-button" data-bs-dismiss="modal">{{ __('Keep booking') }}</button>
                                <form method="post" action="{{ route('seeker.bookings.cancel', $booking) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-danger booking-button">{{ __('Cancel booking') }}</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @empty
            <p class="panel text-muted">{{ __('You have no booking requests yet.') }}</p>
        @endforelse
    </div>
        <x-pagination :items="$bookings" />
</main>
@endsection
