@extends('layouts.app')

@section('title', 'Commissions')
@section('roleStyle', 'admin')

@section('content')
<main class="container py-4">
    <h1>Commission management</h1>
    <p class="text-muted">Set a separate active rate for each agent and review commissions from successful payments.</p>
    <x-logout-form buttonClass="btn btn-outline-danger btn-sm mb-3" />

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <section class="card mb-4">
        <div class="card-body">
            <h2 class="h4">Add a rate</h2>
            <form method="POST" action="{{ route('admin.commissions.rates.store') }}" class="row g-2">
                @csrf
                <div class="col-auto">
                    <label for="new-agent" class="form-label">Agent</label>
                    <select id="new-agent" name="agent_id" required class="form-select">
                        <option value="">Choose an agent</option>
                        @foreach ($agents as $agent)
                            <option value="{{ $agent->id }}">
                                {{ $agent->agentProfile?->first_name }} {{ $agent->agentProfile?->last_name }} ({{ $agent->email }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <label for="new-percentage" class="form-label">Percentage</label>
                    <input id="new-percentage" name="percentage" type="number" min="0" max="100" step="0.01" required class="form-control">
                </div>
                <div class="col-auto align-self-end">
                    <label class="form-check mb-2">
                        <input class="form-check-input" name="active" type="checkbox" value="1">
                        <span class="form-check-label">Make active</span>
                    </label>
                    <button class="btn btn-primary" type="submit">Add rate</button>
                </div>
            </form>
        </div>
    </section>

    <h2 class="h4">Commission rates</h2>
    @forelse ($rates as $rate)
        <form method="POST" action="{{ route('admin.commissions.rates.update', $rate->id) }}" class="card card-body mb-2">
            @csrf
            @method('PUT')
            <div class="row g-2 align-items-end">
                <div class="col-auto">
                    <span class="form-label d-block">Agent</span>
                    <span>{{ $rate->agent?->agentProfile?->first_name }} {{ $rate->agent?->agentProfile?->last_name }} ({{ $rate->agent?->email }})</span>
                </div>
                <div class="col-auto">
                    <label class="form-label">Percentage</label>
                    <input name="percentage" type="number" min="0" max="100" step="0.01" value="{{ $rate->percentage }}" required class="form-control">
                </div>
                <div class="col-auto">
                    <label class="form-check mb-2">
                        <input class="form-check-input" name="active" type="checkbox" value="1" @checked($rate->active)>
                        <span class="form-check-label">Active</span>
                    </label>
                </div>
                <div class="col-auto"><button class="btn btn-outline-primary">Save</button></div>
                <div class="col-auto">
                    <button class="btn btn-outline-danger" type="submit" form="delete-rate-{{ $rate->id }}">Delete</button>
                </div>
            </div>
        </form>
        <form id="delete-rate-{{ $rate->id }}" method="POST" action="{{ route('admin.commissions.rates.destroy', $rate->id) }}" class="d-none">
            @csrf
            @method('DELETE')
        </form>
    @empty
        <p class="alert alert-info">No rates yet. Add an active rate for each agent to enable commission calculation.</p>
    @endforelse

    <h2 class="h4 mt-4">Commission records</h2>
    @if ($commissions->isEmpty())
        <p class="alert alert-info">No commissions have been created yet.</p>
    @else
        <div class="table-responsive">
            <table class="table table-striped">
                <thead><tr><th>Payment</th><th>Payment status</th><th>Agent</th><th>Rate</th><th>Commission</th><th>Agent net</th></tr></thead>
                <tbody>
                    @foreach ($commissions as $commission)
                        <tr>
                            <td>{{ $commission->payment_id ?? 'Older record' }}</td>
                            <td>{{ $commission->payment?->status ?? 'Older record' }}</td>
                            <td>{{ $commission->agent?->email ?? '—' }}</td>
                            <td>{{ $commission->commission_rate }}%</td>
                            <td>{{ number_format((float) $commission->commission_amount, 2) }}</td>
                            <td>{{ $commission->agent_net_amount === null ? '—' : number_format((float) $commission->agent_net_amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $commissions->links() }}
    @endif
</main>
@endsection
