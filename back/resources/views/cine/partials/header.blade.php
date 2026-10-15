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
        </nav>
        <a href="{{ route('cine.home') }}#cartelera" class="btn btn-gold">Comprar entradas</a>
    </div>
</header>
