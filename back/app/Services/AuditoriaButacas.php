<?php

namespace App\Services;

use App\Models\Programa;
use App\Models\Sale;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Models\Audit;

/**
 * Deja rastro en `audits` de que butacas toco cada cajero.
 *
 * Los momentaneos se crean con insertOrIgnore y se borran con delete() del
 * query builder, y los boletos se insertan con Ticket::insert(): ninguno pasa
 * por Eloquent, asi que el paquete de auditoria nunca los ve. Sin esto la
 * unica huella de una venta son los boletos ya impresos y no se puede
 * responder al reclamo "yo seleccione tal asiento y me imprimio otro".
 *
 * Cada registro se ancla a la funcion (Programa) o a la venta (Sale) para que
 * la revision sea por funcion o por venta, y va con tag `butacas` para poder
 * separarlo de la auditoria de facturas.
 */
class AuditoriaButacas
{
    /**
     * Butaca reservada por el cajero (clic en la sala).
     */
    public function seleccionada(int $programaId, array $butaca): void
    {
        $this->registrar('butaca_seleccionada', Programa::class, $programaId, [
            'butaca' => $this->etiqueta($butaca),
            'fila' => $butaca['fila'] ?? null,
            'columna' => $butaca['columna'] ?? null,
            'letra' => $butaca['letra'] ?? null,
            'pelicula' => $butaca['pelicula'] ?? null,
        ]);
    }

    /**
     * El clic no reservo nada porque la butaca ya estaba tomada por otra caja.
     * Es el caso en que el cajero jura haber marcado el asiento y no salio.
     */
    public function ocupada(int $programaId, array $butaca): void
    {
        $this->registrar('butaca_ocupada', Programa::class, $programaId, [
            'butaca' => $this->etiqueta($butaca),
            'detalle' => 'La butaca ya estaba reservada o vendida: no se creo la reserva',
        ]);
    }

    /**
     * Butacas soltadas: por clic, al cambiar de funcion o al cancelar la venta.
     *
     * @param  iterable  $momentaneos  filas de momentaneos ya leidas antes del delete
     */
    public function liberadas(iterable $momentaneos, string $motivo): void
    {
        foreach ($this->agrupar($momentaneos) as $programaId => $butacas) {
            $this->registrar('butaca_liberada', Programa::class, (int) $programaId, [
                'butacas' => $butacas,
                'cantidad' => count($butacas),
                'motivo' => $motivo,
            ]);
        }
    }

    /**
     * Cierre de la venta: que se tenia reservado, que se imprimio y que se
     * quedo afuera. Es la comparacion que responde el reclamo del cliente.
     *
     * @param  iterable  $seleccionadas  momentaneos al momento de facturar
     * @param  iterable  $impresas       boletos realmente insertados
     * @param  array     $descartadas    ['butaca' => ..., 'motivo' => ...]
     */
    public function boletos(Sale $sale, iterable $seleccionadas, iterable $impresas, array $descartadas): void
    {
        $this->registrar('boletos_generados', Sale::class, $sale->id, [
            'seleccionadas' => $this->listar($seleccionadas),
            'impresas' => $this->listar($impresas),
            'descartadas' => $descartadas,
            'coinciden' => count($descartadas) === 0,
        ]);
    }

    /**
     * @param  iterable  $butacas  objetos o arrays con programa_id/fila/columna/letra
     * @return array<int|string, string[]> programa_id => butacas
     */
    private function agrupar(iterable $butacas): array
    {
        $porPrograma = [];
        foreach ($butacas as $butaca) {
            $datos = $this->datos($butaca);
            $porPrograma[$datos['programa_id'] ?? 0][] = $this->etiqueta($datos);
        }

        return $porPrograma;
    }

    /**
     * @return string[] etiquetas "J-18 (funcion 73333)"
     */
    private function listar(iterable $butacas): array
    {
        $lista = [];
        foreach ($butacas as $butaca) {
            $datos = $this->datos($butaca);
            $lista[] = $this->etiqueta($datos) . ' (funcion ' . ($datos['programa_id'] ?? '?') . ')';
        }

        return $lista;
    }

    private function datos($butaca): array
    {
        if (is_array($butaca)) {
            return $butaca;
        }

        // (array) sobre un modelo Eloquent devuelve las propiedades internas
        // con claves mangleadas, no los campos: hay que pedir los atributos.
        if ($butaca instanceof Model) {
            return $butaca->getAttributes();
        }

        if ($butaca instanceof Arrayable) {
            return $butaca->toArray();
        }

        return (array) $butaca; // stdClass de las consultas crudas
    }

    private function etiqueta(array $butaca): string
    {
        return ($butaca['letra'] ?? '?') . '-' . ($butaca['columna'] ?? '?');
    }

    private function registrar(string $evento, string $tipo, int $id, array $valores): void
    {
        if (!config('audit.enabled', true)) {
            return;
        }

        // La auditoria nunca debe tumbar una venta: si algo falla al registrar
        // se anota en el log y la operacion continua.
        try {
            $user = auth()->user();
            $request = request();

            Audit::create([
                'user_type' => $user ? get_class($user) : null,
                'user_id' => $user ? $user->id : null,
                'event' => $evento,
                'auditable_type' => $tipo,
                'auditable_id' => $id,
                'old_values' => [],
                'new_values' => $valores,
                'url' => $request ? $request->fullUrl() : null,
                'ip_address' => $request ? $request->ip() : null,
                'user_agent' => $request ? substr((string) $request->userAgent(), 0, 1023) : null,
                'tags' => 'butacas',
            ]);
        } catch (\Throwable $e) {
            error_log('auditoria butacas: ' . $e->getMessage());
        }
    }
}
