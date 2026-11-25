@extends('layouts.app')
@section('title', __('Makan — My favorites'))
@section('roleStyle', 'seeker')

@section('content')
<x-seeker-navbar />

<main class="app-page container">
    <div class="page-head">
        <div>
            <h1>{{ __('My favorites') }}</h1>
            <p>{{ __(':count saved homes', ['count' => $favorites->total()]) }}</p>
        </div>
        <a href="{{ route('seeker.search') }}" class="btn btn-outline-primary">{{ __('Browse properties') }}</a>
    </div>

    <x-property-search :action="route('seeker.favorites')" />

    <div class="row g-4">
        @forelse ($favorites as $property)
            <x-database-property-card :property="$property" :saved="true" />
        @empty
            <div class="col-12">
                <section class="panel text-center py-5">
                    <h2 class="h5">{{ __('No favorites yet') }}</h2>
                    <p class="text-muted mb-0">{{ __('Tap the heart on a listing to save it here.') }}</p>
                </section>
            </div>
        @endforelse
    </div>
        <x-pagination :items="$favorites" />
</main>
@endsection
