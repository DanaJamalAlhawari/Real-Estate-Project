@extends('layouts.app')

@section('title', __('Makan — My properties'))
@section('roleStyle', 'owner')
@section('bodyAttributes') data-role="owner" data-page="properties"@endsection

@section('content')
    <!-- Desktop: collapses the sidebar to an icon rail (CSS only) -->
    <input type="checkbox" class="sidebar-toggle" id="sidebarToggle" aria-label="{{ __('Collapse the menu') }}" />

    <!-- ===== Topbar ===== -->
    <x-owner-topbar />

    <div class="app-shell">
        <!-- ===== Sidebar ===== -->
        <aside class="app-sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="appSidebar">
            <div class="offcanvas-header border-bottom">
                <a class="brand" href="{{ route('home') }}"><img class="brand-mark"
                        src="{{ asset('assets/images/makan-mark.svg') }}" alt="" width="36"
                        height="36"><span>Makan</span></a>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#appSidebar"
                    aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="offcanvas-body">
                <div class="side-label">{{ __('Property owner') }}</div>
                <a class="side-link" href="{{ route('owner.dashboard') }}" title="{{ __('Dashboard') }}"><i
                        class="bi bi-grid-1x2"></i><span class="flex-grow-1">{{ __('Dashboard') }}</span></a>
                <a class="side-link active" href="{{ route('owner.properties') }}" aria-current="page"
                    title="{{ __('My properties') }}"><i class="bi bi-house-door"></i><span
                        class="flex-grow-1">{{ __('My properties') }}</span></a>
                <a class="side-link" href="{{ route('owner.property-form') }}" title="{{ __('Add property') }}"><i
                        class="bi bi-plus-square"></i><span class="flex-grow-1">{{ __('Add property') }}</span></a>
                <a class="side-link" href="{{ route('owner.requests') }}" title="{{ __('Booking requests') }}"><i
                        class="bi bi-inbox"></i><span class="flex-grow-1">{{ __('Booking requests') }}</span></a>
                <a class="side-link" href="{{ route('owner.chat') }}" title="{{ __('Messages') }}"><i
                        class="bi bi-chat-dots"></i><span class="flex-grow-1">{{ __('Messages') }}</span></a>
                <a class="side-link" href="{{ route('owner.account') }}" title="{{ __('My account') }}"><i
                        class="bi bi-person-badge"></i><span class="flex-grow-1">{{ __('My account') }}</span></a>
            </div>
        </aside>

        <!-- ===== Content ===== -->
        <main class="app-main">
            <!-- 1. Page header -->
            <div class="page-head">
                <div>
                    <h1>{{ __('My properties') }}</h1>
                    @if (isset($ownedProperties) || isset($properties))
                        @php $listForCount = $ownedProperties ?? $properties; @endphp
                        <p>{{ __(':count properties', ['count' => $listForCount->total()]) }}</p>
                    @endif
                </div>
                <a href="{{ route('owner.property-form') }}" class="btn btn-accent">
                    <i class="bi bi-plus-lg"></i>
                    <span>{{ __('Add property') }}</span>
                </a>
            </div>

            @if (session('success'))
                <p class="alert alert-success" role="status">{{ __(session('success')) }}</p>
            @endif

            <x-property-search :action="route('owner.properties')" />

            <!-- 2. Property list -->
            <div class="d-flex flex-column gap-3" id="ownerProperties">
                @if (isset($ownedProperties) || isset($properties))
                    @php
                        $displayProperties = $ownedProperties ?? $properties;
                    @endphp
                    @forelse($displayProperties as $property)
                        <article class="panel prop-row" data-property="{{ $property->id }}">
                            <x-property-image-carousel :property="$property" :compact="true" />
                            <div class="flex-main">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                    <h2 class="prop-row__title">{{ $property->localized('title') }}</h2>
                                    @if ($property->verification_status !== 'verified')
                                        <span
                                            class="status status--pending">{{ __(\Illuminate\Support\Str::headline($property->verification_status)) }}
                                            {{ __('verification') }}</span>
                                    @elseif($property->status == 'available')
                                        <span class="status status--success">{{ __('Active') }}</span>
                                    @elseif($property->status == 'hidden')
                                        <span class="status status--pending">{{ __('Hidden') }}</span>
                                    @else
                                        <span class="status status--pending">
                                            {{ __(\Illuminate\Support\Str::headline($property->status)) }}
                                        </span>
                                    @endif
                                    @if ($property->is_featured ?? false)
                                        <span class="badge-featured mt-0"><i
                                                class="bi bi-star-fill"></i>{{ __('Featured') }}</span>
                                    @endif
                                </div>
                                <div class="small text-muted">
                                    {{ number_format($property->price, 2) }} {{ __('JOD') }}
                                </div>
                                <p class="small text-muted mb-0">{{ __('Verification:') }}
                                    {{ __(\Illuminate\Support\Str::headline($property->verification_status)) }}</p>
                                <div class="fresh-note">
                                    <i class="bi bi-arrow-repeat"></i>
                                    {{ __('Last updated:') }}
                                    {{ $property->updated_at?->diffForHumans() ?? __('Not available') }}
                                </div>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('owner.properties.edit', $property) }}"
                                    class="btn btn-outline-primary btn-sm"
                                    aria-label="Edit {{ $property->localized('title') }}">
                                    <i class="bi bi-pencil" aria-hidden="true"></i> {{ __('Edit') }}
                                </a>
                                @if (in_array($property->status, ['available', 'hidden'], true))
                                    <form action="{{ route('properties.visibility', $property->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-outline-secondary btn-sm"
                                            aria-label="{{ $property->status == 'hidden' ? __('Show') : __('Hide') }} {{ $property->localized('title') }}">
                                            <i class="bi {{ $property->status == 'hidden' ? 'bi-eye' : 'bi-eye-slash' }}"
                                                aria-hidden="true"></i>
                                            {{ $property->status == 'hidden' ? __('Show') : __('Hide') }}
                                        </button>
                                    </form>
                                @endif
                                <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#deleteModal{{ $property->id }}"
                                    aria-label="Delete {{ $property->localized('title') }}">
                                    <i class="bi bi-trash" aria-hidden="true"></i> {{ __('Delete') }}
                                </button>
                                <div class="modal fade" id="deleteModal{{ $property->id }}" tabindex="-1"
                                    aria-labelledby="deleteModalTitle{{ $property->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content rounded-4">
                                            <div class="modal-header border-0">
                                                <h5 class="modal-title fw-bold" id="deleteModalTitle{{ $property->id }}">
                                                    {{ __('Confirm Deletion') }}
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="{{ __('Close') }}"></button>
                                            </div>
                                            <div class="modal-body text-center">
                                                <i class="bi bi-exclamation-triangle text-danger"
                                                    style="font-size: 3rem;"></i>
                                                <h5 class="mt-3">{{ __('Delete this property?') }}</h5>
                                                <p class="text-muted">
                                                    {{ __('Are you sure you want to delete') }}
                                                    <strong>{{ $property->localized('title') }}</strong>{{ __('? It will be removed from active listings and its pending requests will be cancelled.') }}
                                                </p>
                                            </div>
                                            <div class="modal-footer border-0 justify-content-center">
                                                <button type="button" class="btn btn-outline-secondary"
                                                    data-bs-dismiss="modal">
                                                    {{ __('Cancel') }}
                                                </button>
                                                <form action="{{ route('properties.destroy', $property->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">
                                                        <i class="bi bi-trash"></i>
                                                        {{ __('Delete property') }}
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @empty
                        <p class="text-muted">{{ __('You have no properties yet.') }}</p>
                    @endforelse
                    <x-pagination :items="$displayProperties" />
                @endif
            </div>
        </main>
    </div>
@endsection
