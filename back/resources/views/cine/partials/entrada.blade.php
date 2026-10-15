{{-- Entrada (boleto con QR para la puerta) + recibo.
     Con $e (Mis compras) se llena en el servidor; sin $e (compra) la llena comprar.js por data-e. --}}
<div class="docs" data-entrada>
    <div class="ticket">
        <div class="ticket-head">
            <img src="{{ $e['poster'] ?? '' }}" alt="" data-e-img="poster">
            <div>
                <div class="ticket-t" data-e="titulo">{{ $e['titulo'] ?? '' }}</div>
                <div class="ticket-s">MULTICINES PLAZA · ORURO</div>
            </div>
        </div>
        <div class="ticket-grid">
            <div><small>Fecha</small><b data-e="fecha">{{ $e['fecha'] ?? '' }}</b></div>
            <div><small>Hora</small><b data-e="hora">{{ $e['hora'] ?? '' }}</b></div>
            <div><small>Sala</small><b data-e="sala">{{ $e['sala'] ?? '' }}</b></div>
            <div><small>Asientos</small><b data-e="asientos">{{ $e['asientos'] ?? '' }}</b></div>
        </div>
        <div class="ticket-cut"></div>
        <div class="ticket-qr">
            <img src="{{ $e['qr'] ?? '' }}" alt="Código QR de la entrada" data-e-img="qr">
            <span class="mono" data-e="codigo">{{ $e['codigo'] ?? '' }}</span>
            <small>Muestra este código en la puerta de la sala</small>
        </div>
    </div>

    <div class="factura">
        <h4>RECIBO DE VENTA</h4>
        <div class="c">MULTICINES PLAZA · Oruro</div>
        <div class="c">Nro. <span data-e="numero">{{ $e['numero'] ?? '' }}</span></div>
        <hr>
        <table>
            <tr><td>NIT/CI</td><td class="r" data-e="nit">{{ $e['nit'] ?? '' }}</td></tr>
            <tr><td>Nombre</td><td class="r" data-e="nombre">{{ $e['nombre'] ?? '' }}</td></tr>
            <tr><td>Fecha</td><td class="r" data-e="emision">{{ $e['emision'] ?? '' }}</td></tr>
        </table>
        <hr>
        <table>
            <tr>
                <td><span data-e="cantidad">{{ $e['cantidad'] ?? '' }}</span> x Entrada · <span data-e="titulo">{{ $e['titulo'] ?? '' }}</span></td>
                <td class="r" data-e="subtotal">{{ isset($e) ? number_format($e['total'], 2) : '' }}</td>
            </tr>
        </table>
        <hr>
        <table>
            <tr class="tot"><td>TOTAL Bs</td><td class="r" data-e="total">{{ isset($e) ? number_format($e['total'], 2) : '' }}</td></tr>
            <tr><td>Pago</td><td class="r">QR</td></tr>
        </table>
        <hr>
        <div class="ley">Venta en línea. La factura electrónica se emitirá cuando se active la facturación web.</div>
    </div>
</div>
