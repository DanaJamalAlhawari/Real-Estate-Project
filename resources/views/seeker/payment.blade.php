@extends('layouts.app')
@section('title', 'Makan — Mock payment')
@section('roleStyle', 'seeker')
@section('content')
<x-seeker-navbar />

<main class="app-page container">
    <div class="page-head">
        <div>
            <h1>Mock payment</h1>
            <p>This is a demo. No money is charged or transferred.</p>
        </div>
        <a class="btn btn-outline-secondary" href="{{ route('seeker.bookings') }}">My bookings</a>
    </div>

    <section class="panel p-4">
        <h2 class="h5">{{ $booking->property->title }}</h2>
        <p>{{ $booking->start_date->format('j M Y') }} – {{ $booking->end_date->format('j M Y') }}</p>
        @if ($errors->any())
            <p class="alert alert-danger" role="alert">{{ $errors->first() }}</p>
        @endif

        @if ($payment)
            <p class="alert alert-success" role="status">Payment recorded successfully.</p>
            <dl class="row mb-0">
                <dt class="col-sm-5">Amount paid</dt>
                <dd class="col-sm-7">{{ number_format((float) $payment->amount, 2) }} JOD</dd>
                @if ($booking->commission?->payment_id === $payment->id)
                    <dt class="col-sm-5">Commission ({{ $booking->commission->commission_rate }}%)</dt>
                    <dd class="col-sm-7">{{ number_format((float) $booking->commission->commission_amount, 2) }} JOD</dd>
                    <dt class="col-sm-5">Agent net allocation</dt>
                    <dd class="col-sm-7">{{ number_format((float) $booking->commission->agent_net_amount, 2) }} JOD</dd>
                @endif
                @if ($payment->transaction_id)
                    <dt class="col-sm-5">Reference</dt>
                    <dd class="col-sm-7 text-break">{{ $payment->transaction_id }}</dd>
                @endif
            </dl>
        @else
            <p class="fw-semibold">Total due: {{ number_format((float) $booking->total_price, 2) }} JOD</p>
            <form method="post" action="{{ route('payments.mock.pay', $booking) }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="payment-method">Demo payment method</label>
                    <select class="form-select" id="payment-method" name="payment_method" required>
                        @foreach ($methods as $value => $label)
                            <option value="{{ $value }}" @selected(old('payment_method') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="btn btn-accent" type="submit" name="outcome" value="success">Simulate success</button>
                <button class="btn btn-outline-secondary" type="submit" name="outcome" value="failure">Simulate failure</button>
            </form>
        @endif
    </section>
</main>
@endsection
