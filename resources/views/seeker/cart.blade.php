@extends('layouts.app')
@section('title', __('Makan — Payment cart'))
@section('roleStyle', 'seeker')

@section('content')
<x-seeker-navbar />

<main class="app-page container checkout-page">
    <div class="checkout-heading">
        <a href="{{ route('seeker.bookings') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> {{ __('My bookings') }}</a>
        <h1>{{ __('Payment cart') }}</h1>
        <p>{{ __('Approved bookings appear here when they’re ready to pay.') }}</p>
    </div>

    @if ($errors->hasAny(['payment', 'bookings', 'bookings.*']))
        <p class="alert alert-danger" role="alert">{{ $errors->first('payment') ?: __('Review the bookings in your cart and try again.') }}</p>
    @endif

    @if ($checkoutBookingIds->isEmpty())
        <section class="panel checkout-empty">
            <i class="bi bi-bag" aria-hidden="true"></i>
            <h2>{{ __('Your cart is empty') }}</h2>
            <a class="btn btn-primary" href="{{ route('seeker.bookings') }}">{{ __('View my bookings') }}</a>
        </section>
    @else
        <div class="checkout-grid">
            <section class="panel checkout-panel">
                <h2>{{ __('Bookings ready to pay') }}</h2>
                <ul class="checkout-items list-unstyled">
                    @foreach ($bookings as $booking)
                        <li class="checkout-item">
                            <span class="checkout-item__icon"><i class="bi bi-house-door" aria-hidden="true"></i></span>
                            <div>
                                <h3>{{ $booking->property?->localized('title') }}</h3>
                                <p>{{ $booking->start_date->translatedFormat('j M Y') }} – {{ $booking->end_date->translatedFormat('j M Y') }}</p>
                                <a href="{{ route('payments.mock.show', $booking) }}">{{ __('Pay individually') }}</a>
                            </div>
                            <strong>{{ number_format((float) $booking->total_price, 2) }} {{ __('JOD') }}</strong>
                        </li>
                    @endforeach
                </ul>
                <x-pagination :items="$bookings" />
            </section>
            <section class="panel checkout-panel">
                <h2>{{ __('Order summary') }}</h2>
                <div class="checkout-total"><span>{{ __('Total due') }}</span><strong>{{ number_format($total, 2) }} {{ __('JOD') }}</strong></div>
                <form method="post" action="{{ route('payments.mock.cart.pay') }}" autocomplete="off">
                    @csrf
                    @foreach ($checkoutBookingIds as $bookingId)
                        <input type="hidden" name="bookings[]" value="{{ $bookingId }}">
                    @endforeach
                    @include('partials.demo-card-fields')
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-lock" aria-hidden="true"></i> {{ __('Pay :total JOD (demo)', ['total' => number_format($total, 2)]) }}</button>
                </form>
            </section>
        </div>
    @endif
</main>
@endsection
