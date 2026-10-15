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
    @include('cine.partials.header', ['activo' => $activo ?? '', 'cliente' => $cineCliente])

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

<div class="modal" id="login-modal" hidden>
    <div class="login-box" role="dialog" aria-modal="true" aria-labelledby="login-titulo">
        <button type="button" class="round-x login-x" data-cerrar-login aria-label="Cerrar">✕</button>
        <img src="{{ asset('cine/img/logo.png') }}" alt="" class="login-logo">
        <h3 id="login-titulo">INICIA SESIÓN</h3>
        <p>Para elegir tu función y tus asientos ingresa con tu cuenta de Google. Así guardamos tus compras y tus datos de facturación.</p>
        <button type="button" class="btn-google" data-login-google>
            <svg viewBox="0 0 48 48" width="20" height="20" aria-hidden="true"><path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3C33.7 32.7 29.2 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.2 7.9 3.1l5.7-5.7C34 6.1 29.3 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.3-.1-2.4-.4-3.5z"/><path fill="#FF3D00" d="m6.3 14.7 6.6 4.8C14.7 15.1 19 12 24 12c3.1 0 5.8 1.2 7.9 3.1l5.7-5.7C34 6.1 29.3 4 24 4 16.3 4 9.7 8.3 6.3 14.7z"/><path fill="#4CAF50" d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2C29.2 35.1 26.7 36 24 36c-5.2 0-9.6-3.3-11.3-8l-6.5 5C9.5 39.6 16.2 44 24 44z"/><path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-.8 2.2-2.2 4.2-4.1 5.6l6.2 5.2C37 39.2 44 34 44 24c0-1.3-.1-2.4-.4-3.5z"/></svg>
            <span>Continuar con Google</span>
        </button>
        <div class="aviso aviso-err" data-login-error hidden></div>
        <small class="login-nota">Solo usamos tu nombre y correo para tus entradas y facturas.</small>
    </div>
</div>

<script type="application/json" id="cine-auth">@json($cineAuth)</script>
<script src="{{ asset('cine/cine.js') }}?v={{ filemtime(public_path('cine/cine.js')) }}"></script>
<script type="module" src="{{ asset('cine/auth.js') }}?v={{ filemtime(public_path('cine/auth.js')) }}"></script>
@stack('scripts')
</body>
</html>
