@extends('layouts.app')

@section('title', 'User details')
@section('roleStyle', 'admin')

@section('content')
<main class="container py-4">
    <a href="{{ route('admin.users') }}">&larr; Back to users</a>
    <h1 class="mt-3">User details</h1>

    @php
        $profile = $user->role === 'agent'
            ? $user->agentProfile
            : $user->tenantProfile;
    @endphp

    <div class="card mt-3">
        <div class="card-body">
            <p><strong>Name:</strong>
                {{ trim(($profile->first_name ?? '') . ' ' . ($profile->last_name ?? '')) ?: 'Name not provided' }}
            </p>
            <p><strong>Email:</strong> {{ $user->email }}</p>
            <p><strong>Role:</strong> {{ $user->role === 'agent' ? 'Agent' : 'Customer' }}</p>
            <p><strong>Phone:</strong> {{ $profile->phone ?? '—' }}</p>
            <p><strong>Account created:</strong> {{ $user->created_at?->format('Y-m-d H:i') ?? '—' }}</p>

            @if ($user->role === 'agent')
                <p><strong>Agency:</strong> {{ $profile?->agency_name ?: '—' }}</p>
                <p><strong>License number:</strong> {{ $profile?->license_number ?: '—' }}</p>
                <p><strong>Verification:</strong> {{ $profile?->verification_status ?? 'unverified' }}</p>
            @else
                <p><strong>Student ID:</strong> {{ $profile?->student_id ?: '—' }}</p>
            @endif
        </div>
    </div>
</main>
@endsection
