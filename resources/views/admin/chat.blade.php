@extends('layouts.app')
@section('title', __('Makan — Admin messages'))
@section('roleStyle', 'admin')
@section('content')
@include('partials.admin-topbar')
<div class="app-shell">
    @include('partials.admin-sidebar')
    <main class="app-main">
        <div class="page-head"><h1>{{ __('Messages') }}</h1></div>
        @if ($contacts->isNotEmpty())
            <form action="{{ route('admin.chat.start') }}" method="post" class="panel mb-3">
                @csrf
                <label class="form-label" for="supportContact">{{ __('Start a support conversation') }}</label>
                <div class="input-group">
                    <select id="supportContact" class="form-select" name="contact_id" required>
                        <option value="">{{ __('Choose a tenant or agent') }}</option>
                        @foreach ($contacts as $contact)
                            <option value="{{ $contact->id }}">{{ $contact->email }} ({{ __($contact->role) }})</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary">{{ __('Open conversation') }}</button>
                </div>
            </form>
        @endif
        @include('partials.owner-chat-panels')
    </main>
</div>
@endsection
