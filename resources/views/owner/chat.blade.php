@extends('layouts.app')

@section('title', 'Makan — Messages')
@section('roleStyle', 'owner')
@section('bodyAttributes') data-role="owner" data-page="chat"@endsection

@section('content')
    <!-- Desktop: collapses the sidebar to an icon rail (CSS only) -->
    <input type="checkbox" class="sidebar-toggle" id="sidebarToggle" aria-label="Collapse the menu" />

    <!-- ===== Topbar ===== -->
    <x-owner-topbar />

    <div class="app-shell">
        <!-- ===== Sidebar ===== -->
        <aside class="app-sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="appSidebar">
            <div class="offcanvas-header border-bottom">
                <a class="brand" href="{{ route('home') }}"><img class="brand-mark" src="{{ asset('assets/images/makan-mark.svg') }}" alt="" width="36" height="36"><span>Makan</span></a>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#appSidebar"
                    aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <div class="side-label">Property owner</div>
                <a class="side-link" href="{{ route('owner.dashboard') }}" title="Dashboard"><i
                        class="bi bi-grid-1x2"></i><span class="flex-grow-1">Dashboard</span></a>
                <a class="side-link" href="{{ route('owner.properties') }}" title="My properties"><i
                        class="bi bi-house-door"></i><span class="flex-grow-1">My properties</span></a>
                <a class="side-link" href="{{ route('owner.property-form') }}" title="Add property"><i
                        class="bi bi-plus-square"></i><span class="flex-grow-1">Add property</span></a>
                <a class="side-link" href="{{ route('owner.requests') }}" title="Booking requests"><i
                        class="bi bi-inbox"></i><span class="flex-grow-1">Booking requests</span></a>
                <a class="side-link active" href="{{ route('owner.chat') }}" aria-current="page" title="Messages"><i
                        class="bi bi-chat-dots"></i><span class="flex-grow-1">Messages</span></a>
                <a class="side-link" href="{{ route('owner.account') }}" title="My account"><i
                        class="bi bi-person-badge"></i><span class="flex-grow-1">My account</span></a>
            </div>
        </aside>

        <!-- ===== Content ===== -->
        <main class="app-main">
            <!-- 1. Page header -->
            <h1 class="h3 mb-3">Messages</h1>

            <!-- 2. Chat -->
            @include('partials.owner-chat-panels')
        </main>
    </div>
@endsection
