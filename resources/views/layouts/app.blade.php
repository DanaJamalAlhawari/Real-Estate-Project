<!doctype html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'Makan')</title>
    @stack('styles')
    @stack('scripts')
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/icons/bootstrap-icons.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}" />
    @hasSection('roleStyle')
      <link rel="stylesheet" href="{{ asset('assets/css/pages/' . trim($__env->yieldContent('roleStyle')) . '.css') }}" />
    @endif
    <link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}" />
  </head>
  <body @yield('bodyAttributes')>
    @yield('content')
    <script type="application/json" id="makanHomes">@json($homes ?? [])</script>
    <script src="{{ asset('assets/vendor/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/models/homes.js') }}" defer></script>
    <script src="{{ asset('assets/js/views/bayti.js') }}" defer></script>
    <script src="{{ asset('assets/js/controllers/bayti.js') }}" defer></script>
  </body>
</html>
