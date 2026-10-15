<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="cine-token" content="{{ $cineToken }}">
    <meta name="robots" content="index,follow">
    <title>@yield('title', 'MultiCines Plaza · Oruro')</title>
    <link rel="icon" href="{{ asset('cine/img/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Manrope:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('cine/cine.css') }}?v={{ filemtime(public_path('cine/cine.css')) }}">
</head>
<body>
<div class="page">
    @include('cine.partials.header', ['activo' => $activo ?? ''])

    <main>
        @yield('content')
    </main>

    @yield('footer')
</div>

<div class="modal" id="trailer-modal" hidden>
    <div class="modal-box">
        <div class="modal-head">
            <h3 id="trailer-titulo"></h3>
            <button type="button" class="round-x" data-cerrar-trailer aria-label="Cerrar">✕</button>
        </div>
        <div class="video" id="trailer-video"></div>
    </div>
</div>

<script src="{{ asset('cine/cine.js') }}?v={{ filemtime(public_path('cine/cine.js')) }}"></script>
@stack('scripts')
</body>
</html>
