@extends('layouts.app')
@section('title', $payment ? __('Makan — Payment receipt') : __('Makan — Checkout'))
@section('roleStyle', 'seeker')

@section('content')
<x-seeker-navbar />

<main class="app-page container checkout-page">
    <div class="checkout-heading">
        <a href="{{ route('seeker.bookings') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> {{ __('My bookings') }}</a>
        <h1>{{ $payment ? __('Payment receipt') : __('Checkout') }}</h1>
        <p>{{ $payment ? __('Your demo payment details.') : __('Mock payment for an approved booking. No money is charged.') }}</p>
    </div>

    @error('payment')
        <p class="alert alert-danger" role="alert">{{ $message }}</p>
    @enderror

    <div class="checkout-grid">
        <section class="panel checkout-panel">
            <h2>{{ __('Booking details') }}</h2>
            <div class="checkout-item">
                <span class="checkout-item__icon"><i class="bi bi-house-door" aria-hidden="true"></i></span>
                <div>
                    <h3>{{ $booking->property?->localized('title') }}</h3>
                    <p>{{ $booking->start_date->translatedFormat('j M Y') }} – {{ $booking->end_date->translatedFormat('j M Y') }}</p>
                </div>
            </div>
            <div class="checkout-total">
                <span>{{ $payment ? __('Amount paid') : __('Total due') }}</span>
                <strong>{{ number_format((float) ($payment?->amount ?? $booking->total_price), 2) }} {{ __('JOD') }}</strong>
            </div>
        </section>

        @if ($payment)
            <section class="panel checkout-panel">
                <h2>{{ __('Transaction details') }}</h2>
                <p class="alert alert-success" role="status"><i class="bi bi-check-circle" aria-hidden="true"></i> {{ __('Payment recorded successfully.') }}</p>
                <dl class="receipt-details">
                    <dt>{{ __('Payment method') }}</dt>
                    <dd>{{ __($methods[$payment->payment_method] ?? $payment->payment_method) }}</dd>
                    @if ($payment->paid_at)
                        <dt>{{ __('Date paid') }}</dt><dd>{{ $payment->paid_at->translatedFormat('j M Y') }}</dd>
                    @endif
                    @if ($booking->commission?->payment_id === $payment->id)
                        <dt>{{ __('Commission (') }}{{ $booking->commission->commission_rate }}%)</dt>
                        <dd>{{ number_format((float) $booking->commission->commission_amount, 2) }} {{ __('JOD') }}</dd>
                        <dt>{{ __('Agent net allocation') }}</dt>
                        <dd>{{ number_format((float) $booking->commission->agent_net_amount, 2) }} {{ __('JOD') }}</dd>
                    @endif
                    @if ($payment->transaction_id)
                        <dt>{{ __('Reference') }}</dt><dd class="text-break">{{ $payment->transaction_id }}</dd>
                    @endif
                </dl>
            </section>
        @else
            <section class="panel checkout-panel">
                <h2>{{ __('Card details') }}</h2>
                <form method="post" action="{{ route('payments.mock.pay', $booking) }}" autocomplete="off">
                    @csrf
                    @include('partials.demo-card-fields')
                    <button class="btn btn-primary w-100" type="submit"><i class="bi bi-lock" aria-hidden="true"></i> {{ __('Pay :total JOD (demo)', ['total' => number_format((float) $booking->total_price, 2)]) }}</button>
                </form>
            </section>
        @endif
    </div>
</main>
@endsection
