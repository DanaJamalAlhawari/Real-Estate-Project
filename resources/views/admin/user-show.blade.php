@extends('layouts.app')

@section('title', __('User details'))
@section('roleStyle', 'admin')

@section('content')
@include('partials.admin-topbar')
<div class="app-shell">
@include('partials.admin-sidebar')
<main class="app-main">
    <a href="{{ route('admin.users') }}">{{ __('&larr; Back to users') }}</a>
    <h1 class="mt-3">{{ __('User details') }}</h1>

    @php
        $profile = $user->role === 'agent'
            ? $user->agentProfile
            : $user->tenantProfile;
    @endphp

    <div class="card mt-3">
        <div class="card-body">
            <p><strong>{{ __('Name:') }}</strong>
                {{ trim(($profile->first_name ?? '') . ' ' . ($profile->last_name ?? '')) ?: __('Name not provided') }}
            </p>
            <p><strong>{{ __('Email:') }}</strong> {{ $user->email }}</p>
            <p><strong>{{ __('Role:') }}</strong> {{ $user->role === 'agent' ? __('Agent') : __('Customer') }}</p>
            <p><strong>{{ __('Phone:') }}</strong> {{ $profile->phone ?? '—' }}</p>
            <p><strong>{{ __('Account created:') }}</strong> {{ $user->created_at?->format('Y-m-d H:i') ?? '—' }}</p>

            @if ($user->role === 'agent')
                <p><strong>{{ __('Agency:') }}</strong> {{ $profile?->agency_name ?: '—' }}</p>
                <p><strong>{{ __('License number:') }}</strong> {{ $profile?->license_number ?: '—' }}</p>
                <p><strong>{{ __('Verification:') }}</strong> {{ __(\Illuminate\Support\Str::headline($profile?->verification_status ?? 'unverified')) }}</p>
            @else
                <p><strong>{{ __('Student ID:') }}</strong> {{ $profile?->student_id ?: '—' }}</p>
            @endif
        </div>
    </div>
</main>
</div>
@endsection
