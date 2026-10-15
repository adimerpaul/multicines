@extends('cine.layout')

@section('title', 'Mis compras · MultiCines Plaza')

@section('content')
    <div class="compras-main">
        @if(!$cliente)
            <div class="login-card">
                <div>
                    <img src="{{ asset('cine/img/logo.png') }}" alt="">
                    <div>
                        <h1 class="h2">MIS COMPRAS</h1>
                        <p class="nota">Ingresa con tu cuenta de Google para ver tus compras y los QR de tus entradas.</p>
                    </div>
                    <button type="button" class="btn btn-gold" data-abrir-login>Ingresar con Google</button>
                </div>
            </div>
        @else
            <div class="compras-head">
                <div class="compras-user">
                    <span class="compras-avatar">
                        @if($cliente->foto)
                            <img src="{{ $cliente->foto }}" alt="" referrerpolicy="no-referrer">
                        @else
                            {{ mb_strtoupper(mb_substr($cliente->nombre ?: $cliente->email, 0, 1)) }}
                        @endif
                    </span>
                    <div>
                        <div class="eyebrow">HOLA, {{ mb_strtoupper(\Illuminate\Support\Str::before($cliente->nombre ?: $cliente->email, ' ')) }}</div>
                        <h1 class="h2">MIS COMPRAS</h1>
                    </div>
                </div>
                <span class="nota">{{ $cliente->email }}</span>
            </div>

            @if($compras->isEmpty())
                <div class="vacio">
                    <strong>AÚN NO TIENES COMPRAS</strong>
                    Cuando compres entradas, aquí verás tus QR para entrar a la sala.
                    <div><a href="{{ route('cine.home') }}#cartelera" class="btn btn-gold">Ver cartelera</a></div>
                </div>
            @else
                <div class="grid-compras">
                    @foreach($compras as $c)
                        <article class="compra">
                            <div class="compra-top">
                                <img src="{{ $c['poster'] }}" alt="">
                                <div class="compra-info">
                                    <span class="estado {{ strtolower($c['estado']) }}">{{ $c['estado'] }}</span>
                                    <h3>{{ $c['titulo'] }}</h3>
                                    <span class="l1">{{ $c['fecha'] }} · {{ $c['hora'] }}</span>
                                    <span class="l2">{{ $c['sala'] }} · Asientos {{ $c['asientos'] }}</span>
                                    @if($c['verificadoAt'])
                                        <span class="l2">Ingreso verificado {{ $c['verificadoAt'] }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="compra-cut"></div>
                            <div class="compra-foot">
                                <div>
                                    <span class="mono">{{ $c['codigo'] }}</span>
                                    <small>Bs. {{ number_format($c['total'], 2) }} · Pagado con QR</small>
                                </div>
                                <button type="button" class="btn-grad" data-ver-entrada="{{ $c['id'] }}">Ver QR</button>
                            </div>
                        </article>

                        <div class="modal" data-modal-entrada="{{ $c['id'] }}" hidden>
                            <div class="modal-entrada">
                                <div class="modal-head">
                                    <span class="estado {{ strtolower($c['estado']) }}">{{ $c['estado'] }}</span>
                                    <button type="button" class="round-x" data-cerrar-entrada aria-label="Cerrar">✕</button>
                                </div>
                                @include('cine.partials.entrada', ['e' => $c])
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif
    </div>
@endsection

@section('footer')
    @include('cine.partials.footer')
@endsection

@push('scripts')
    <script src="{{ asset('cine/mis-compras.js') }}?v={{ filemtime(public_path('cine/mis-compras.js')) }}"></script>
@endpush
