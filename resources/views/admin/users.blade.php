@extends('layouts.app')

@section('title', __('Users'))
@section('roleStyle', 'admin')

@section('content')
@include('partials.admin-topbar')
<div class="app-shell">
@include('partials.admin-sidebar')
<main class="app-main">
    <h1>{{ __('Users') }}</h1>
    <p class="text-muted">{{ __('Registered customers and agents.') }}</p>

    <form method="GET" action="{{ route('admin.users') }}" class="mb-3">
        <label for="q" class="form-label">{{ __('Search by email') }}</label>
        <div class="input-group">
            <input
                id="q"
                name="q"
                type="search"
                class="form-control"
                value="{{ request('q') }}"
                placeholder="name@example.com"
            >
            <button class="btn btn-primary" type="submit">{{ __('Search') }}</button>
        </div>
    </form>

    @if ($users->isEmpty())
        <div class="alert alert-info">{{ __('No customers or agents found.') }}</div>
    @else
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Email') }}</th>
                        <th>{{ __('Role') }}</th>
                        <th>{{ __('Phone') }}</th>
                        <th>{{ __('Joined') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        @php
                            $profile = $user->role === 'agent'
                                ? $user->agentProfile
                                : $user->tenantProfile;
                        @endphp
                        <tr>
                            <td>
                                {{ trim(($profile->first_name ?? '') . ' ' . ($profile->last_name ?? '')) ?: __('Name not provided') }}
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->role === 'agent' ? __('Agent') : __('Customer') }}</td>
                            <td>{{ $profile->phone ?? '—' }}</td>
                            <td>{{ $user->created_at?->format('Y-m-d') ?? '—' }}</td>
                            <td>
                                <a href="{{ route('admin.users.show', $user->id) }}">{{ __('Details') }}</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <x-pagination :items="$users" />
    @endif
</main>
</div>
@endsection
