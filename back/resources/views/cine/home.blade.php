@extends('cine.layout')

@section('content')
    {{-- Carrusel --}}
    <section id="inicio" class="hero" data-hero>
        @forelse($hero as $i => $s)
            <div class="slide {{ $i === 0 ? 'on' : '' }}" data-slide>
                <img src="{{ $s['backdrop'] ?: $s['poster'] }}" alt="" class="slide-bg {{ $s['backdrop'] ? '' : 'blur' }}">
                <div class="slide-shade-1"></div>
                <div class="slide-shade-2"></div>
                <div class="wrap slide-body">
                    <div class="slide-text">
                        <div class="slide-tags">
                            <span class="tag-grad">{{ $s['estreno'] ? 'ESTRENO' : 'EN CARTELERA' }}</span>
                            <span class="slide-meta">{{ collect([$s['genero'], $s['duracion'], $s['clasificacion']])->filter()->join(' · ') }}</span>
                        </div>
                        <h1>{{ $s['titulo'] }}</h1>
                        <p>{{ \Illuminate\Support\Str::limit($s['sinopsis'] ?: $s['tagline'], 220) }}</p>
                        <div class="slide-actions">
                            <a href="{{ route('cine.comprar', $s['id']) }}" class="btn btn-gold btn-lg">Comprar entradas</a>
                            @if($s['trailer'])
                                <button type="button" class="btn btn-ghost btn-lg" data-trailer="{{ $s['trailer'] }}" data-titulo="{{ $s['titulo'] }}"><span class="play"></span>Ver tráiler</button>
                            @endif
                        </div>
                    </div>
                    <div class="slide-poster"><img src="{{ $s['poster'] }}" alt="{{ $s['titulo'] }}"></div>
                </div>
            </div>
        @empty
            <div class="wrap hero-empty">
                <div>
                    <div class="eyebrow">MULTICINES PLAZA</div>
                    <h1 class="h2">PRONTO NUEVA CARTELERA</h1>
                </div>
            </div>
        @endforelse

        @if($hero->count() > 1)
            <div class="hero-ctrl">
                <div class="wrap">
                    <div class="dots">
                        @foreach($hero as $i => $s)
                            <button type="button" class="dot {{ $i === 0 ? 'on' : '' }}" data-dot="{{ $i }}" aria-label="Ir a {{ $s['titulo'] }}"></button>
                        @endforeach
                    </div>
                    <button type="button" class="round" data-prev aria-label="Anterior">‹</button>
                    <button type="button" class="round" data-next aria-label="Siguiente">›</button>
                </div>
            </div>
        @endif
    </section>

    {{-- Cartelera --}}
    <section id="cartelera" class="wrap section" data-cartelera>
        <div class="section-head">
            <div>
                <div class="eyebrow">EN CARTELERA</div>
                <h2 class="h2">CARTELERA DE LA SEMANA</h2>
            </div>
            <div class="chips">
                @foreach(['Todos', 'Estrenos', '2D', '3D'] as $f)
                    <button type="button" class="chip {{ $loop->first ? 'on' : '' }}" data-filtro="{{ $f }}">{{ $f }}</button>
                @endforeach
            </div>
        </div>

        <div class="days">
            @foreach($dias as $d)
                <button type="button" class="day {{ $loop->first ? 'on' : '' }}" data-dia="{{ $d['fecha'] }}">
                    <span class="day-dow">{{ $d['dow'] }}</span>
                    <span class="day-num">{{ $d['num'] }}</span>
                    <span class="day-mes">{{ $d['mes'] }}</span>
                </button>
            @endforeach
        </div>

        @if($peliculas->isEmpty())
            <div class="vacio"><strong>SIN PELÍCULAS EN CARTELERA</strong>Vuelve pronto para ver los nuevos estrenos.</div>
        @else
            <div class="grid-movies">
                @foreach($peliculas as $m)
                    @php
                        $formatos = collect($m['funciones'])->pluck('formato')->unique()->values();
                        $precios = collect($m['funciones'])->pluck('precio')->unique()->sort()->map(fn($p) => rtrim(rtrim(number_format($p, 2, '.', ''), '0'), '.'));
                    @endphp
                    <article class="movie" data-movie data-estreno="{{ $m['estreno'] ? 1 : 0 }}" data-formatos="{{ $formatos->join(',') }}">
                        <div class="poster">
                            <a href="{{ route('cine.comprar', $m['id']) }}" class="poster-link" data-poster-link aria-label="Comprar {{ $m['titulo'] }}"></a>
                            <img src="{{ $m['poster'] }}" alt="{{ $m['titulo'] }}" loading="lazy">
                            <div class="poster-shade"></div>
                            @if($m['estreno'])<span class="badge-estreno">ESTRENO</span>@endif
                            <span class="badge-clasif">{{ $m['clasificacion'] }}</span>
                            <div class="poster-foot">
                                <span class="precio">{{ $precios->isNotEmpty() ? 'Bs. ' . $precios->join(' / ') : '' }}</span>
                                @if($m['trailer'])
                                    <button type="button" class="btn-trailer" data-trailer="{{ $m['trailer'] }}" data-titulo="{{ $m['titulo'] }}"><span class="play play-sm"></span>Tráiler</button>
                                @endif
                            </div>
                        </div>
                        <div>
                            <h3>{{ $m['titulo'] }}</h3>
                            <div class="movie-meta">{{ collect([$m['genero'], $m['duracion']])->filter()->join(' · ') }}</div>
                        </div>
                        <div class="horas">
                            @foreach($m['funciones'] as $f)
                                <a href="{{ route('cine.comprar', $m['id']) }}?funcion={{ $f['id'] }}" class="hora" data-fecha="{{ $f['fecha'] }}">{{ $f['hora'] }}<small>{{ $f['formato'] }}</small></a>
                            @endforeach
                            <span class="sin-funciones" data-sin-funciones hidden>Sin funciones este día</span>
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="vacio" data-sin-resultados hidden><strong>NO HAY PELÍCULAS CON ESE FILTRO</strong>Prueba con otro día o formato.</div>
        @endif
    </section>

    {{-- Proximos estrenos --}}
    @if($proximos->isNotEmpty())
        <section id="proximos" class="wrap section">
            <div class="eyebrow">MUY PRONTO</div>
            <h2 class="h2">PRÓXIMOS ESTRENOS</h2>
            <div class="grid-prox">
                @foreach($proximos as $p)
                    <article class="prox">
                        <div class="prox-img">
                            <img src="{{ $p['backdrop'] ?: $p['poster'] }}" alt="" loading="lazy">
                            @if($p['trailer'])
                                <button type="button" class="prox-play" data-trailer="{{ $p['trailer'] }}" data-titulo="{{ $p['titulo'] }}" aria-label="Ver tráiler"><span class="play"></span></button>
                            @endif
                            <span class="prox-fecha">{{ $p['fechaEstreno'] }}</span>
                        </div>
                        <div class="prox-body">
                            <h3>{{ $p['titulo'] }}</h3>
                            <div class="movie-meta">{{ collect([$p['genero'], $p['duracion']])->filter()->join(' · ') }}</div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @else
        <span id="proximos"></span>
    @endif

    {{-- Como comprar --}}
    <section id="comprar" class="wrap section">
        <div class="comprar-box">
            <div class="comprar-head">
                <h2>COMPRA ONLINE Y PAGA CON QR</h2>
                <p>Sin filas en boletería. Elige tu función y tus asientos, paga escaneando el QR con la app de tu banco y recibe tu entrada con factura al instante.</p>
            </div>
            <div class="pasos">
                <div class="paso"><div class="paso-num">01</div><div class="paso-t">Elige la función</div><div class="paso-d">Película, día y horario.</div></div>
                <div class="paso"><div class="paso-num">02</div><div class="paso-t">Escoge tus asientos</div><div class="paso-d">Mapa de la sala con los asientos libres.</div></div>
                <div class="paso"><div class="paso-num">03</div><div class="paso-t">Paga con QR</div><div class="paso-d">Desde cualquier banco del país.</div></div>
                <div class="paso"><div class="paso-num">04</div><div class="paso-t">Muestra tu entrada</div><div class="paso-d">Código QR en tu celular.</div></div>
            </div>
        </div>
    </section>
@endsection

@section('footer')
    @include('cine.partials.footer')
@endsection

@push('scripts')
    <script src="{{ asset('cine/home.js') }}?v={{ filemtime(public_path('cine/home.js')) }}"></script>
@endpush
