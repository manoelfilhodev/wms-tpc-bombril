<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8" />
    <title>@yield('title', 'WMS 4.0')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Sistema WMS" name="description" />
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">
    <link href="{{ asset('assets/css/app-creative.min.css') }}" rel="stylesheet" type="text/css" id="light-style" />
    <link href="{{ asset('assets/css/app-creative-dark.min.css') }}" rel="stylesheet" type="text/css" id="dark-style" />
    <link href="{{ asset('assets/css/wms-ui.css') }}" rel="stylesheet" type="text/css" />
    @yield('head')
</head>
<body class="authentication-bg" data-layout-config='{"darkMode":false}'>
    @yield('content')
    <script src="{{ asset('assets/js/vendor.min.js') }}"></script>
    <script src="{{ asset('assets/js/app.min.js') }}"></script>
    @yield('scripts')
</body>
</html>