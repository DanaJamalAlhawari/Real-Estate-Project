@extends('layouts.app')
@section('title', 'Makan — My favorites')
@section('roleStyle', 'seeker')

@section('content')
<x-seeker-navbar />

<main class="app-page container">
    <div class="page-head">
        <div>
            <h1>My favorites</h1>
            <p>{{ $favorites->count() }} saved {{ Str::plural('home', $favorites->count()) }}</p>
        </div>
        <a href="{{ route('seeker.search') }}" class="btn btn-outline-primary">Browse properties</a>
    </div>

    <div class="row g-4">
        @forelse ($favorites as $property)
            <x-database-property-card :property="$property" :saved="true" />
        @empty
            <div class="col-12">
                <section class="panel text-center py-5">
                    <h2 class="h5">No favorites yet</h2>
                    <p class="text-muted mb-0">Tap the heart on a listing to save it here.</p>
                </section>
            </div>
        @endforelse
    </div>
</main>
@endsection
