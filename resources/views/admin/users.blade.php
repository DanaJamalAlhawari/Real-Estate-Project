@extends('layouts.app')

@section('title', 'Users')
@section('roleStyle', 'admin')

@section('content')
<main class="container py-4">
    <h1>Users</h1>
    <p class="text-muted">Registered customers and agents.</p>

    <form method="GET" action="{{ route('admin.users') }}" class="mb-3">
        <label for="q" class="form-label">Search by email</label>
        <div class="input-group">
            <input
                id="q"
                name="q"
                type="search"
                class="form-control"
                value="{{ request('q') }}"
                placeholder="name@example.com"
            >
            <button class="btn btn-primary" type="submit">Search</button>
        </div>
    </form>

    @if ($users->isEmpty())
        <div class="alert alert-info">No customers or agents found.</div>
    @else
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Phone</th>
                        <th>Joined</th>
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
                                {{ trim(($profile->first_name ?? '') . ' ' . ($profile->last_name ?? '')) ?: 'Name not provided' }}
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->role === 'agent' ? 'Agent' : 'Customer' }}</td>
                            <td>{{ $profile->phone ?? '—' }}</td>
                            <td>{{ $user->created_at?->format('Y-m-d') ?? '—' }}</td>
                            <td>
                                <a href="{{ route('admin.users.show', $user->id) }}">Details</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $users->withQueryString()->links() }}
    @endif
</main>
@endsection
