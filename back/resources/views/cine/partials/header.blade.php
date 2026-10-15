<header class="header">
    <div class="wrap">
        <a href="{{ route('cine.home') }}#inicio" class="brand">
            <img src="{{ asset('cine/img/logo.png') }}" alt="MultiCines Plaza">
            <div class="brand-text">
                <span class="brand-name">MULTICINES <span>PLAZA</span></span>
                <span class="brand-sub">ORURO · BOLIVIA</span>
            </div>
        </a>
        <nav class="nav">
            <a href="{{ route('cine.home') }}#cartelera">Cartelera</a>
            <a href="{{ route('cine.home') }}#proximos">Próximos estrenos</a>
            <a href="{{ route('cine.home') }}#comprar">Cómo comprar</a>
            <a href="{{ route('cine.home') }}#contacto">Contacto</a>
            <a href="{{ route('cine.compras') }}" class="{{ ($activo ?? '') === 'compras' ? 'active' : '' }}">Mis compras</a>
        </nav>
        <div class="header-acciones">
            <a href="{{ route('cine.home') }}#cartelera" class="btn btn-gold">Comprar entradas</a>
            <div class="cuenta" data-cuenta @if(!$cliente) hidden @endif>
                <span class="cuenta-avatar" data-cuenta-avatar>
                    @if($cliente && $cliente->foto)
                        <img src="{{ $cliente->foto }}" alt="" referrerpolicy="no-referrer">
                    @else
                        {{ $cliente ? mb_strtoupper(mb_substr($cliente->nombre ?: $cliente->email, 0, 1)) : '' }}
                    @endif
                </span>
                <span class="cuenta-nombre" data-cuenta-nombre>{{ $cliente ? \Illuminate\Support\Str::before($cliente->nombre ?: $cliente->email, ' ') : '' }}</span>
                <button type="button" class="cuenta-salir" data-logout>Salir</button>
            </div>
            <button type="button" class="btn btn-ghost btn-ingresar" data-abrir-login @if($cliente) hidden @endif>Ingresar</button>
        </div>
    </div>
</header>
