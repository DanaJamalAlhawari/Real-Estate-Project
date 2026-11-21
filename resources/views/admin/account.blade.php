@extends('layouts.app')
@section('title', 'Makan — Admin account')
@section('roleStyle', 'admin')
@section('content')
@include('partials.admin-topbar')
<div class="app-shell">
    @include('partials.admin-sidebar')
    <main class="app-main">
        <div class="page-head"><h1>My account</h1></div>
        @include('partials.account-profile-form')
    </main>
</div>
@endsection
