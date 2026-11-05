<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Login - Departamento de Mantenimiento')</title>

    <!-- jQuery (dejado por si las dudas como pediste) -->
    <script src="{{ asset('Scripts/jquery-3.6.0.min.js') }}"></script>

    <!-- Bootstrap (Usando el CDN igual que en app.blade.php para no usar el local pesado) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Font Awesome -->
    <link href="{{ asset('css/font-awesome.min.css') }}" rel="stylesheet" />

    <!-- Estilos Propios -->
    <link href="{{ asset('Content/OwnStyles/fuentes.css') }}" rel="stylesheet" />
    <link href="{{ asset('Content/OwnStyles/Site.css') }}" rel="stylesheet" />
    <link href="{{ asset('Content/OwnStyles/components.css') }}" rel="stylesheet" />

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('Images/favicon.png') }}">

    @stack('css')
</head>
<body class="d-flex flex-column min-vh-100">
    
    @yield('content')

    <script src="{{ asset('js/prevent-double-submit.js') }}"></script>
    @stack('scripts')
</body>
</html>
