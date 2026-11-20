@extends('layouts.app')

@section('title', 'My earnings')
@section('roleStyle', 'owner')

@section('content')
<main class="container py-4">
    <h1>My earnings</h1>
    <p class="text-muted">Earnings from successful mock payments.</p>
    <x-logout-form buttonClass="btn btn-outline-danger btn-sm mb-3" />

    <div class="card mb-4">
        <div class="card-body">
            <span class="text-muted">Total agent earnings</span>
            <div class="fs-3 fw-bold">{{ number_format((float) $totalEarnings, 2) }}</div>
        </div>
    </div>

    @if ($commissions->isEmpty())
        <p class="alert alert-info">You do not have earnings yet.</p>
    @else
        <div class="table-responsive">
            <table class="table table-striped">
                <thead><tr><th>Payment</th><th>Rate</th><th>Commission</th><th>Your net amount</th><th>Date</th></tr></thead>
                <tbody>
                    @foreach ($commissions as $commission)
                        <tr>
                            <td>{{ $commission->payment_id }}</td>
                            <td>{{ $commission->commission_rate }}%</td>
                            <td>{{ number_format((float) $commission->commission_amount, 2) }}</td>
                            <td>{{ number_format((float) $commission->agent_net_amount, 2) }}</td>
                            <td>{{ $commission->created_at?->format('Y-m-d') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $commissions->links() }}
    @endif
</main>
@endsection
