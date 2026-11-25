@extends('layouts.app')

@section('title', __('Commissions'))
@section('roleStyle', 'admin')

@section('content')
@include('partials.admin-topbar')
<div class="app-shell">
@include('partials.admin-sidebar')
<main class="app-main">
    <h1>{{ __('Commission management') }}</h1>
    <p class="text-muted">{{ __('Review commissions from successful payments. Mock checkout uses the rate approved for the property.') }}</p>

    @if (session('success'))
        <div class="alert alert-success">{{ __(session('success')) }}</div>
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
            <h2 class="h4">{{ __('Add a rate') }}</h2>
            <form method="POST" action="{{ route('admin.commissions.rates.store') }}" class="row g-2">
                @csrf
                <div class="col-auto">
                    <label for="new-agent" class="form-label">{{ __('Agent') }}</label>
                    <select id="new-agent" name="agent_id" required class="form-select">
                        <option value="">{{ __('Choose an agent') }}</option>
                        @foreach ($agents as $agent)
                            <option value="{{ $agent->id }}">
                                {{ $agent->agentProfile?->first_name }} {{ $agent->agentProfile?->last_name }} ({{ $agent->email }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <label for="new-percentage" class="form-label">{{ __('Percentage') }}</label>
                    <input id="new-percentage" name="percentage" type="number" min="0" max="100" step="0.01" required class="form-control">
                </div>
                <div class="col-auto align-self-end">
                    <label class="form-check mb-2">
                        <input class="form-check-input" name="active" type="checkbox" value="1">
                        <span class="form-check-label">{{ __('Make active') }}</span>
                    </label>
                    <button class="btn btn-primary" type="submit">{{ __('Add rate') }}</button>
                </div>
            </form>
        </div>
    </section>

    <h2 class="h4">{{ __('Commission rates') }}</h2>
    @forelse ($rates as $rate)
        <form method="POST" action="{{ route('admin.commissions.rates.update', $rate->id) }}" class="card card-body mb-2">
            @csrf
            @method('PUT')
            <div class="row g-2 align-items-end">
                <div class="col-auto">
                    <span class="form-label d-block">{{ __('Agent') }}</span>
                    <span>{{ $rate->agent?->agentProfile?->first_name }} {{ $rate->agent?->agentProfile?->last_name }} ({{ $rate->agent?->email }})</span>
                </div>
                <div class="col-auto">
                    <label class="form-label">{{ __('Percentage') }}</label>
                    <input name="percentage" type="number" min="0" max="100" step="0.01" value="{{ $rate->percentage }}" required class="form-control">
                </div>
                <div class="col-auto">
                    <label class="form-check mb-2">
                        <input class="form-check-input" name="active" type="checkbox" value="1" @checked($rate->active)>
                        <span class="form-check-label">{{ __('Active') }}</span>
                    </label>
                </div>
                <div class="col-auto"><button class="btn btn-outline-primary">{{ __('Save') }}</button></div>
                <div class="col-auto">
                    <button class="btn btn-outline-danger" type="submit" form="delete-rate-{{ $rate->id }}">{{ __('Delete') }}</button>
                </div>
            </div>
        </form>
        <form id="delete-rate-{{ $rate->id }}" method="POST" action="{{ route('admin.commissions.rates.destroy', $rate->id) }}" class="d-none">
            @csrf
            @method('DELETE')
        </form>
    @empty
        <p class="alert alert-info">{{ __('No per-agent rates have been set.') }}</p>
    @endforelse
        <x-pagination :items="$rates" />

    <h2 class="h4 mt-4">{{ __('Commission records') }}</h2>
    @if ($commissions->isEmpty())
        <p class="alert alert-info">{{ __('No commissions have been created yet.') }}</p>
    @else
        <div class="table-responsive">
            <table class="table table-striped">
                <thead><tr><th>{{ __('Payment') }}</th><th>{{ __('Payment status') }}</th><th>{{ __('Agent') }}</th><th>{{ __('Rate') }}</th><th>{{ __('Commission') }}</th><th>{{ __('Agent net') }}</th></tr></thead>
                <tbody>
                    @foreach ($commissions as $commission)
                        <tr>
                            <td>{{ $commission->payment_id ?? __('Older record') }}</td>
                            <td>
                                {{ $commission->payment ? __(\Illuminate\Support\Str::headline($commission->payment->status)) : __('Older record') }}
                                @if (str_starts_with($commission->payment?->transaction_id ?? '', 'mock-'))
                                    <span class="badge text-bg-secondary">{{ __('Mock') }}</span>
                                @endif
                            </td>
                            <td>{{ $commission->agent?->email ?? '—' }}</td>
                            <td>{{ $commission->commission_rate }}%</td>
                            <td>{{ number_format((float) $commission->commission_amount, 2) }}</td>
                            <td>{{ $commission->agent_net_amount === null ? '—' : number_format((float) $commission->agent_net_amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <x-pagination :items="$commissions" />
    @endif
</main>
</div>
@endsection
