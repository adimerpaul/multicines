<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Detail;
use App\Models\Momentaneo;
use App\Models\Programa;
use App\Models\Sale;
use App\Models\Ticket;
use App\Models\User;
use App\Models\WebCliente;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * Venta de entradas desde la web publica.
 *
 * 1. reservar(): retiene los asientos en `momentaneos` (igual que la boleteria,
 *    asi la boleteria los ve como RESERVADO y no los puede vender) con un token
 *    de compra y un vencimiento.
 * 2. confirmar(): cuando el pago se confirma crea la venta en `sales`, sus
 *    boletos en `tickets` y el detalle en `details`, a nombre del usuario
 *    vendedor de la web (config services.cine.vendedor_email), como si el
 *    hubiera hecho la venta. Por ahora es recibo (sin factura SIAT).
 */
class VentaWeb
{
    const MAX_ASIENTOS = 10;
    const ALFABETO_CODIGO = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public function vendedor(): User
    {
        $email = (string) config('services.cine.vendedor_email');
        $user = User::where('email', $email)->first();
        if (!$user) {
            throw new RuntimeException("No existe el usuario vendedor de la web ($email)");
        }

        return $user;
    }

    public function minutosReserva(): int
    {
        return max(1, (int) config('services.cine.reserva_minutos', 10));
    }

    /**
     * Funcion vigente y habilitada para la venta online, o excepcion.
     */
    public function funcionVendible(int $programaId): Programa
    {
        $programa = Programa::with('sala', 'price', 'movie')
            ->where('id', $programaId)
            ->where('activo', 'ACTIVO')
            ->where('horaInicio', '>=', now())
            ->whereHas('webMovies', fn($q) => $q->where('estado', 'ACTIVO'))
            ->first();
        if (!$programa || !$programa->sala || !$programa->price) {
            throw new RuntimeException('La función ya no está disponible');
        }

        $soloBoleteria = $programa->webMovies()
            ->where(fn($q) => $q->where('web_movies.venta_online', false)
                ->orWhere('web_movie_programa.venta_online', false))
            ->exists();
        if ($soloBoleteria) {
            throw new RuntimeException('Esta función se vende solo en boletería');
        }

        return $programa;
    }

    /** Suelta las reservas web vencidas (cliente que no pago o cerro la pagina). */
    public function liberarVencidas(): void
    {
        Momentaneo::whereNotNull('web_compra')
            ->where('created_at', '<', now()->subMinutes($this->minutosReserva()))
            ->delete();
    }

    public function liberar(?string $token): void
    {
        if ($token) {
            Momentaneo::where('web_compra', $token)->delete();
        }
    }

    /**
     * Retiene los asientos ('A16', 'B3', ...) de la funcion. Devuelve los datos
     * de la compra que se guardan en la sesion del cliente.
     */
    public function reservar(WebCliente $cliente, int $programaId, array $codigos, array $datos): array
    {
        $codigos = array_values(array_unique(array_map('strtoupper', $codigos)));
        if (!count($codigos) || count($codigos) > self::MAX_ASIENTOS) {
            throw new RuntimeException('Elige entre 1 y ' . self::MAX_ASIENTOS . ' asientos');
        }

        $programa = $this->funcionVendible($programaId);
        $vendedor = $this->vendedor();
        $this->liberarVencidas();

        // Los asientos deben existir y estar activos en la sala de la funcion
        $asientos = [];
        foreach ($codigos as $codigo) {
            if (!preg_match('/^([A-Z]{1,2})(\d{1,3})$/', $codigo, $m)) {
                throw new RuntimeException("Asiento invalido: $codigo");
            }
            $seat = DB::table('seats')
                ->where('sala_id', $programa->sala_id)
                ->where('letra', $m[1])
                ->where('columna', (int) $m[2])
                ->where('activo', 'ACTIVO')
                ->whereNull('deleted_at')
                ->first(['fila', 'columna', 'letra']);
            if (!$seat) {
                throw new RuntimeException("El asiento $codigo no existe en la sala");
            }
            $asientos[] = ['f' => (int) $seat->fila, 'c' => (int) $seat->columna, 'l' => $seat->letra];
        }

        $vendidos = $this->asientosVendidos($programa->id, $asientos);
        if ($vendidos) {
            throw new RuntimeException('Ya se vendieron: ' . implode(', ', $vendidos) . '. Elige otros asientos.');
        }

        // Reserva atomica: el indice unico momentaneos_butaca_unique impide que
        // dos personas (web o boleteria) tomen la misma butaca.
        $token = Str::random(32);
        $tomados = [];
        foreach ($asientos as $a) {
            $ok = Momentaneo::insertOrIgnore([
                'user_id' => $vendedor->id,
                'programa_id' => $programa->id,
                'fila' => $a['f'],
                'columna' => $a['c'],
                'letra' => $a['l'],
                'fecha' => now(),
                'precio' => $programa->price->precio,
                'pelicula' => optional($programa->movie)->nombre,
                'pelicula_id' => $programa->movie_id,
                'promo' => 0,
                'web_compra' => $token,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            if (!$ok) {
                $tomados[] = $a['l'] . $a['c'];
            }
        }
        if ($tomados) {
            $this->liberar($token);
            throw new RuntimeException('Alguien acaba de tomar: ' . implode(', ', $tomados) . '. Elige otros asientos.');
        }

        $precio = (float) $programa->price->precio;

        return [
            'token' => $token,
            'web_cliente_id' => $cliente->id,
            'programa_id' => $programa->id,
            'asientos' => $asientos,
            'precio' => $precio,
            'total' => round($precio * count($asientos), 2),
            'expira' => now()->addMinutes($this->minutosReserva())->timestamp,
            'datos' => $datos,
        ];
    }

    /**
     * Pago confirmado: registra la venta, los boletos y el detalle.
     * $pago = ['qrId' => ..., 'qrTransactionId' => ...]
     */
    public function confirmar(array $compra, WebCliente $cliente, array $pago): Sale
    {
        if ((int) $compra['web_cliente_id'] !== (int) $cliente->id) {
            throw new RuntimeException('La compra no pertenece a esta cuenta');
        }

        $reservados = Momentaneo::where('web_compra', $compra['token'])->count();
        if ($reservados !== count($compra['asientos']) || time() > (int) $compra['expira']) {
            $this->liberar($compra['token']);
            throw new RuntimeException('La reserva de tus asientos venció. Vuelve a elegirlos.');
        }

        $programa = Programa::with('sala', 'price', 'movie')->findOrFail($compra['programa_id']);
        $vendedor = $this->vendedor();

        // Mismo lock que la boleteria para numerar boletos sin choques
        $lock = 'multisalas_boletos';
        $tomado = DB::selectOne('SELECT GET_LOCK(?, 10) AS tomado', [$lock]);
        if (!$tomado || (int) $tomado->tomado !== 1) {
            throw new RuntimeException('El sistema está ocupado, vuelve a intentar en unos segundos');
        }

        try {
            return DB::transaction(function () use ($compra, $cliente, $pago, $programa, $vendedor) {
                $vendidos = $this->asientosVendidos($programa->id, $compra['asientos']);
                if ($vendidos) {
                    throw new RuntimeException('Ya se vendieron: ' . implode(', ', $vendidos));
                }

                $client = $this->registrarCliente($compra['datos'], $cliente);
                $total = round((float) $compra['precio'] * count($compra['asientos']), 2);

                $sale = new Sale();
                $sale->numeroFactura = 0;
                $sale->cuf = '';
                $sale->cufd = '';
                $sale->cui = '';
                $sale->codigoSucursal = 0;
                $sale->codigoPuntoVenta = 0;
                $sale->fechaEmision = now();
                $sale->montoTotal = $total;
                $sale->usuario = $vendedor->name;
                $sale->codigoRecepcion = '';
                $sale->siatEnviado = true;
                $sale->tipo = 'BOLETERIA';
                $sale->codigoDocumentoSector = 0;
                $sale->leyenda = '';
                $sale->vip = 'NO';
                $sale->cortesia = 'NO';
                $sale->credito = 'NO';
                $sale->venta = 'R'; // recibo: sin factura SIAT por ahora
                $sale->pagoQr = true;
                $sale->qrId = $pago['qrId'] ?? null;
                $sale->qrTransactionId = $pago['qrTransactionId'] ?? null;
                $sale->qrPagadoAt = now();
                $sale->user_id = $vendedor->id;
                $sale->cufd_id = null;
                $sale->client_id = $client->id;
                $sale->web_cliente_id = $cliente->id;
                $sale->canal = 'WEB';
                $sale->codigo_entrada = $this->nuevoCodigo();
                $sale->verificado = false;
                $sale->save();

                $this->crearBoletos($sale, $programa, $client, $vendedor, $compra['asientos']);

                Detail::insert([
                    'actividadEconomica' => '590000',
                    'codigoProductoSin' => '99100',
                    'cantidad' => count($compra['asientos']),
                    'precioUnitario' => $compra['precio'],
                    'subTotal' => $total,
                    'sale_id' => $sale->id,
                    'programa_id' => $programa->id,
                    'pelicula_id' => $programa->movie_id,
                    'descripcion' => optional($programa->movie)->nombre,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->liberar($compra['token']);

                return $sale;
            });
        } finally {
            DB::selectOne('SELECT RELEASE_LOCK(?) AS liberado', [$lock]);
        }
    }

    /** QR de la entrada como data URI (SVG), listo para un <img src>. */
    public function qrDataUri(string $codigo): string
    {
        $svg = (string) QrCode::format('svg')->size(260)->margin(1)->errorCorrection('M')->generate($codigo);

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /**
     * Cliente de facturacion (tabla clients) por NIT/CI + complemento.
     * Si no existe se registra; si existe se actualiza nombre y correo.
     */
    private function registrarCliente(array $datos, WebCliente $webCliente): Client
    {
        $nit = trim((string) ($datos['nit'] ?? ''));
        $complemento = strtoupper(trim((string) ($datos['complemento'] ?? '')));
        $nombre = trim((string) ($datos['nombre'] ?? ''));
        $email = trim((string) ($datos['email'] ?? '')) ?: (string) $webCliente->email;

        if ($nit === '' || $nit === '0') {
            $client = Client::where('numeroDocumento', '0')->where('complemento', '')->first();
            if ($client) {
                return $client;
            }
            $nit = '0';
            $complemento = '';
            $nombre = 'S/N';
        }

        $client = Client::where('numeroDocumento', $nit)->where('complemento', $complemento)->first() ?: new Client();
        $client->numeroDocumento = $nit;
        $client->complemento = $complemento;
        $client->nombreRazonSocial = mb_strtoupper($nombre ?: 'S/N');
        $client->codigoTipoDocumentoIdentidad = $client->codigoTipoDocumentoIdentidad ?: 1;
        if ($email !== '') {
            $client->email = $email;
        }
        $client->save();

        return $client;
    }

    private function crearBoletos(Sale $sale, Programa $programa, Client $client, User $vendedor, array $asientos): void
    {
        $ultimo = Ticket::where('programa_id', $programa->id)->count();
        $data = [];
        foreach ($asientos as $a) {
            $numero = ++$ultimo;
            $data[] = [
                'numboc' => $programa->sala->nro . $programa->sala->id . date('Ymd', strtotime($programa->fecha)) . $programa->nroFuncion . $programa->price->serie . '-' . $numero,
                'numero' => $numero,
                'fecha' => now(),
                'numeroFuncion' => $programa->nroFuncion,
                'nombreSala' => $programa->sala->nombre,
                'serieTarifa' => $programa->price->serie,
                'fechaFuncion' => $programa->fecha,
                'horaFuncion' => $programa->horaInicio,
                'fila' => $a['f'],
                'columna' => $a['c'],
                'letra' => $a['l'],
                'costo' => $programa->price->precio,
                'titulo' => optional($programa->movie)->nombre,
                'devuelto' => '0',
                'idCupon' => '',
                'tarjeta' => '',
                'credito' => '',
                'promo' => 0,
                'client_id' => $client->id,
                'programa_id' => $programa->id,
                'pelicula_id' => $programa->movie_id,
                'sale_id' => $sale->id,
                'price_id' => $programa->price->id,
                'sala_id' => $programa->sala->id,
                'user_id' => $vendedor->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        Ticket::insert($data);
    }

    /** Asientos (de la lista) que ya tienen boleto vendido en la funcion. */
    private function asientosVendidos(int $programaId, array $asientos): array
    {
        $vendidos = [];
        foreach ($asientos as $a) {
            $existe = Ticket::where('programa_id', $programaId)
                ->where('fila', $a['f'])
                ->where('columna', $a['c'])
                ->where('letra', $a['l'])
                ->where('devuelto', 0)
                ->exists();
            if ($existe) {
                $vendidos[] = $a['l'] . $a['c'];
            }
        }

        return $vendidos;
    }

    private function nuevoCodigo(): string
    {
        do {
            $codigo = 'MP-';
            for ($i = 0; $i < 8; $i++) {
                $codigo .= self::ALFABETO_CODIGO[random_int(0, strlen(self::ALFABETO_CODIGO) - 1)];
            }
        } while (Sale::withTrashed()->where('codigo_entrada', $codigo)->exists());

        return $codigo;
    }
}
