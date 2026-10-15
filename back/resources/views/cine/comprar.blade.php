@extends('cine.layout')

@section('title', 'Comprar entradas · ' . $pelicula['titulo'] . ' · MultiCines Plaza')

@section('content')
    {{-- Ficha de la pelicula --}}
    <section class="ficha">
        <img src="{{ $pelicula['backdrop'] ?: $pelicula['poster'] }}" alt="" class="ficha-bg{{ $pelicula['backdrop'] ? '' : ' ficha-bg-blur' }}">
        <div class="ficha-shade"></div>
        <div class="ficha-wrap">
            <div class="crumbs">
                <a href="{{ route('cine.home') }}#cartelera">← Cartelera</a><span>/</span><span>{{ $pelicula['titulo'] }}</span>
            </div>
            <div class="ficha-main">
                <img src="{{ $pelicula['poster'] }}" alt="{{ $pelicula['titulo'] }}" class="ficha-poster">
                <div class="ficha-info">
                    @if($pelicula['estreno'])
                        <span class="ficha-badge">ESTRENO</span>
                    @endif
                    <h1 class="ficha-titulo">{{ $pelicula['titulo'] }}</h1>
                    @if($pelicula['tagline'])
                        <p class="ficha-tagline">“{{ $pelicula['tagline'] }}”</p>
                    @endif

                    <div class="ficha-chips">
                        @foreach(array_filter([$pelicula['anio'], $pelicula['duracion'], $pelicula['clasificacion']]) as $chip)
                            <span class="chip">{{ $chip }}</span>
                        @endforeach
                        @foreach(array_filter(array_map('trim', explode(',', (string) $pelicula['genero']))) as $g)
                            <span class="chip chip-gen">{{ $g }}</span>
                        @endforeach
                    </div>

                    @if(count($pelicula['puntajes']))
                        <div class="ficha-scores">
                            @foreach($pelicula['puntajes'] as $p)
                                <div class="score{{ $p['principal'] ? ' score-main' : '' }}">
                                    <div class="score-val">@if($p['principal'])<span class="estrella">★</span>@endif{{ number_format($p['valor'], 1) }}<small>{{ $p['escala'] }}</small></div>
                                    <div class="score-lbl">{{ $p['nombre'] }}@if($p['principal'] && $pelicula['votos']) · {{ $pelicula['votos'] }} votos @endif</div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if($pelicula['sinopsis'])
                        <p class="ficha-sinopsis">{{ $pelicula['sinopsis'] }}</p>
                    @endif

                    <div class="ficha-acciones">
                        @if($pelicula['trailer'])
                            <button type="button" class="btn btn-gold btn-lg" data-trailer="{{ $pelicula['trailer'] }}" data-titulo="{{ $pelicula['titulo'] }}"><span class="play play-dark"></span>Ver tráiler</button>
                        @endif
                        <a href="#comprar" class="btn btn-ghost btn-lg">{{ $pelicula['ventaOnline'] ? 'Comprar entradas' : 'Ver horarios' }}</a>
                        @if($pelicula['urlOficial'])
                            <a href="{{ $pelicula['urlOficial'] }}" class="btn btn-ghost btn-lg" target="_blank" rel="noopener noreferrer">Sitio oficial ↗</a>
                        @endif
                    </div>
                </div>
            </div>

            @if(count($pelicula['detalles']) || count($pelicula['reparto']))
                <div class="ficha-extra">
                    @if(count($pelicula['detalles']))
                        <dl class="ficha-detalles">
                            @foreach($pelicula['detalles'] as $k => $v)
                                <div><dt>{{ $k }}</dt><dd>{{ $v }}</dd></div>
                            @endforeach
                        </dl>
                    @endif
                    @if(count($pelicula['reparto']))
                        <div class="ficha-reparto">
                            <div class="label-sec">REPARTO</div>
                            <div class="reparto-lista">
                                @foreach($pelicula['reparto'] as $a)
                                    <div class="actor">
                                        @if($a['foto'])
                                            <img src="{{ $a['foto'] }}" alt="" loading="lazy">
                                        @else
                                            <span class="actor-ini">{{ mb_substr($a['nombre'], 0, 1) }}</span>
                                        @endif
                                        <span>{{ $a['nombre'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </section>

    <div class="booking-main" id="comprar">

        <div class="booking">
            {{-- Resumen --}}
            <aside class="resumen">
                <img src="{{ $pelicula['poster'] }}" alt="" class="resumen-bg">
                <div class="resumen-shade"></div>
                <div class="resumen-body">
                    <img src="{{ $pelicula['poster'] }}" alt="{{ $pelicula['titulo'] }}" class="resumen-poster">
                    <div>
                        <h3>{{ $pelicula['titulo'] }}</h3>
                        <div class="resumen-meta">{{ collect([$pelicula['genero'], $pelicula['duracion'], $pelicula['clasificacion']])->filter()->join(' · ') }}</div>
                    </div>
                    <div class="resumen-list">
                        <div><span>Fecha</span><strong data-sum="fecha">—</strong></div>
                        <div><span>Hora</span><strong data-sum="hora">—</strong></div>
                        <div><span>Sala</span><strong data-sum="sala">—</strong></div>
                        <div><span>Asientos</span><strong data-sum="asientos">—</strong></div>
                        <div class="resumen-total"><span>Total</span><strong data-sum="total">Bs. 0</strong></div>
                    </div>
                </div>
            </aside>

            {{-- Pasos --}}
            <div class="pasos-col">
                <div class="pasos-top">
                    <div class="tabs">
                        @foreach(['Función', 'Asientos', 'Datos', 'Pago QR', 'Entrada'] as $i => $t)
                            <button type="button" class="tab" data-tab="{{ $i }}">{{ $i + 1 }}. {{ $t }}</button>
                        @endforeach
                    </div>
                    <a href="{{ route('cine.home') }}#cartelera" class="cancelar">Cancelar</a>
                </div>

                @if(!$pelicula['ventaOnline'])
                    <div class="aviso">Esta película se vende solo en boletería. Puedes ver los horarios y los asientos libres.</div>
                @endif
                <div class="aviso" data-aviso-funcion hidden>Esta función se vende solo en boletería. Puedes ver los asientos libres y comprar tu entrada en ventanilla.</div>

                {{-- 1. Funcion --}}
                <div class="stack" data-paso="0">
                    @if($dias->isEmpty())
                        <div class="vacio"><strong>SIN FUNCIONES DISPONIBLES</strong>Esta película no tiene funciones en los próximos días.</div>
                    @else
                        <div>
                            <div class="label-sec">DÍA</div>
                            <div class="row-wrap">
                                @foreach($dias as $d)
                                    <button type="button" class="day day-sm" data-dia="{{ $d['fecha'] }}" data-largo="{{ $d['largo'] }}">
                                        <span class="day-dow">{{ $d['dow'] }}</span>
                                        <span class="day-num">{{ $d['num'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <div class="label-sec">HORARIO</div>
                            <div class="row-wrap" data-horarios></div>
                        </div>
                    @endif
                </div>

                {{-- 2. Asientos --}}
                <div class="sala" data-paso="1" hidden>
                    <div class="pantalla">PANTALLA</div>
                    <div class="aviso aviso-err" data-sala-error hidden></div>
                    <div class="cargando" data-sala-cargando>Cargando sala…</div>
                    <div class="butacas"><div class="butacas-grid" data-butacas></div></div>
                    <div class="leyenda">
                        <span><i></i>Disponible</span>
                        <span><i class="sel"></i>Seleccionado</span>
                        <span><i class="res"></i>Reservado</span>
                        <span><i class="ocu"></i>Ocupado</span>
                    </div>
                </div>

                {{-- 3. Datos de facturacion (solo diseno) --}}
                <form class="form" data-paso="2" data-form-factura hidden autocomplete="on" novalidate>
                    <div class="form-head">
                        <div class="label-sec">DATOS DE FACTURACIÓN</div>
                        <button type="button" class="btn btn-outline btn-sm" data-sin-nombre>Sin nombre (S/N)</button>
                    </div>
                    <div class="form-grid">
                        <label class="field">NIT / CI
                            <input name="nit" inputmode="numeric" maxlength="20" placeholder="1234567" autocomplete="off">
                        </label>
                        <label class="field">Complemento
                            <input name="complemento" maxlength="5" placeholder="1A" autocomplete="off">
                        </label>
                    </div>
                    <label class="field">Nombre o razón social
                        <input name="nombre" maxlength="120" placeholder="Juan Pérez" autocomplete="name">
                    </label>
                    <label class="field">Correo para recibir la entrada y factura
                        <input name="email" type="email" maxlength="120" placeholder="correo@ejemplo.com" autocomplete="email">
                    </label>
                </form>

                {{-- 4. Pago QR: los asientos quedan retenidos mientras corre el tiempo --}}
                <div class="qr-pay" data-paso="3" hidden>
                    <div class="qr-box"><div class="qr qr-lg" data-qr-pago></div></div>
                    <div class="qr-info">
                        <div class="label-sec">PAGO CON QR</div>
                        <div class="qr-total" data-sum="total">Bs. 0</div>
                        <p>Abre la app de tu banco, elige “Pagar con QR” y escanea el código. La confirmación es automática.</p>
                        <div class="esperando"><i></i>Tus asientos están reservados · expira en <span data-timer>10:00</span></div>
                        <div class="aviso aviso-err" data-pago-error hidden></div>
                        @if($cineData['pagoDemo'])
                            <button type="button" class="btn-demo" data-simular-pago>Simular pago confirmado (prueba)</button>
                        @else
                            <div class="aviso">El pago QR en línea se activará pronto. Por ahora compra en boletería.</div>
                        @endif
                    </div>
                </div>

                {{-- 5. Entrada (QR real para la puerta) + recibo --}}
                <div class="entrada-wrap" data-paso="4" hidden>
                    <div class="ok">¡COMPRA REGISTRADA!</div>
                    @include('cine.partials.entrada', ['e' => null])
                    <p class="nota">Muestra este QR en la entrada de la sala. También lo tienes en <a href="{{ route('cine.compras') }}">Mis compras</a>.</p>
                </div>


                <div class="pie-pasos">
                    <button type="button" class="btn btn-outline invisible" data-atras>Atrás</button>
                    <span class="hint" data-hint></span>
                    <button type="button" class="btn btn-gold" data-siguiente>Continuar</button>
                </div>
            </div>
        </div>
    </div>

    <script type="application/json" id="cine-data">@json($cineData)</script>
@endsection

@section('footer')
    <footer class="footer">
        <div class="wrap footer-bottom">
            <span>MultiCines Plaza · Av. Tacna, Jaén y Tomás Frías, Oruro · 2 5281290</span>
            <span>Pago seguro con QR</span>
        </div>
    </footer>
@endsection

@push('scripts')
    <script src="{{ asset('cine/comprar.js') }}?v={{ filemtime(public_path('cine/comprar.js')) }}"></script>
@endpush
