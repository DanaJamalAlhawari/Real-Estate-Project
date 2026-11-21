@extends('layouts.app')
@section('title', 'Makan — My profile')
@section('roleStyle', 'seeker')
@section('bodyAttributes') data-role="seeker" data-page="profile"@endsection
@section('content')
<x-seeker-navbar />
<main class="app-page container">
    <div class="page-head"><h1>My profile</h1></div>
    @include('partials.account-profile-form')
</main>
@endsection
