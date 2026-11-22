<!doctype html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'Makan')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/images/makan-mark.svg') }}" />
    @stack('styles')
    @stack('scripts')
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/icons/bootstrap-icons.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}" />
    @hasSection('roleStyle')
        <link rel="stylesheet"
            href="{{ asset('assets/css/pages/' . trim($__env->yieldContent('roleStyle')) . '.css') }}" />
    @endif
    <link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/makan.css') }}" />
    @if (auth()->user() instanceof \App\Models\RealEstateUser)
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="makan-user-id" content="{{ auth()->id() }}">
        @vite('resources/js/app.js')
    @endif
</head>

<body @yield('bodyAttributes')>
    @yield('content')
    @include('partials.site-footer')
    @if (auth()->user() instanceof \App\Models\RealEstateUser)
        <x-seeker-assistant />
        <script src="{{ asset('assets/js/controllers/makan-assistant.js') }}" defer></script>
    @endif
    <script src="{{ asset('assets/vendor/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/controllers/bayti.js') }}" defer></script>

</body>

</html>
