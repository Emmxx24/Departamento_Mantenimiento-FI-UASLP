<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Departamento de Mantenimiento')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="icon" type="image/png" href="{{ asset('Images/favicon.png') }}">
    <link href="{{ asset('Content/OwnStyles/Site.css') }}" rel="stylesheet">
    <link href="{{ asset('Content/OwnStyles/fuentes.css') }}" rel="stylesheet">
    <link href="{{ asset('Content/OwnStyles/components.css') }}" rel="stylesheet">
    <link href="{{ asset('Content/OwnStyles/animations.css') }}" rel="stylesheet">
    @stack('css')
</head>

<body class="d-flex flex-column min-vh-100">
    <x-header-inside></x-header-inside>
    <x-navbar></x-navbar>

    <main class="flex-fill">
        @yield('content')
    </main>

    <x-footer></x-footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/prevent-double-submit.js') }}"></script>
    @stack('scripts')
</body>

</html>
