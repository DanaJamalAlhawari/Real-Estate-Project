@extends('layouts.app')
@section('title', $payment ? 'Makan — Payment receipt' : 'Makan — Checkout')
@section('roleStyle', 'seeker')

@section('content')
<x-seeker-navbar />

<main class="app-page container checkout-page">
    <div class="checkout-heading">
        <a href="{{ route('seeker.bookings') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> My bookings</a>
        <h1>{{ $payment ? 'Payment receipt' : 'Checkout' }}</h1>
        <p>{{ $payment ? 'Your demo payment details.' : 'Mock payment for an approved booking. No money is charged.' }}</p>
    </div>

    @error('payment')
        <p class="alert alert-danger" role="alert">{{ $message }}</p>
    @enderror

    <div class="checkout-grid">
        <section class="panel checkout-panel">
            <h2>Booking details</h2>
            <div class="checkout-item">
                <span class="checkout-item__icon"><i class="bi bi-house-door" aria-hidden="true"></i></span>
                <div>
                    <h3>{{ $booking->property->title }}</h3>
                    <p>{{ $booking->start_date->format('j M Y') }} – {{ $booking->end_date->format('j M Y') }}</p>
                </div>
            </div>
            <div class="checkout-total">
                <span>{{ $payment ? 'Amount paid' : 'Total due' }}</span>
                <strong>{{ number_format((float) ($payment?->amount ?? $booking->total_price), 2) }} JOD</strong>
            </div>
        </section>

        @if ($payment)
            <section class="panel checkout-panel">
                <h2>Transaction details</h2>
                <p class="alert alert-success" role="status"><i class="bi bi-check-circle" aria-hidden="true"></i> Payment recorded successfully.</p>
                <dl class="receipt-details">
                    <dt>Payment method</dt>
                    <dd>{{ $methods[$payment->payment_method] ?? $payment->payment_method }}</dd>
                    @if ($payment->paid_at)
                        <dt>Date paid</dt><dd>{{ $payment->paid_at->format('j M Y') }}</dd>
                    @endif
                    @if ($booking->commission?->payment_id === $payment->id)
                        <dt>Commission ({{ $booking->commission->commission_rate }}%)</dt>
                        <dd>{{ number_format((float) $booking->commission->commission_amount, 2) }} JOD</dd>
                        <dt>Agent net allocation</dt>
                        <dd>{{ number_format((float) $booking->commission->agent_net_amount, 2) }} JOD</dd>
                    @endif
                    @if ($payment->transaction_id)
                        <dt>Reference</dt><dd class="text-break">{{ $payment->transaction_id }}</dd>
                    @endif
                </dl>
            </section>
        @else
            <section class="panel checkout-panel">
                <h2>Card details</h2>
                <form method="post" action="{{ route('payments.mock.pay', $booking) }}" autocomplete="off">
                    @csrf
                    @include('partials.demo-card-fields')
                    <button class="btn btn-primary w-100" type="submit"><i class="bi bi-lock" aria-hidden="true"></i> Pay {{ number_format((float) $booking->total_price, 2) }} JOD (demo)</button>
                </form>
            </section>
        @endif
    </div>
</main>
@endsection
