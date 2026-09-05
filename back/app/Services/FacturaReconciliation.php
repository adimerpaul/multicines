<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FacturaReconciliation
{
    private const SOURCES = ['sales' => null, 'sale_candies' => 'CANDY', 'rentals' => 'ALQUILER'];

    public function report(array $params): array
    {
        $start = Carbon::create($params['anio'], $params['mes'], 1)->startOfDay()->toDateString();
        $end = Carbon::parse($start)->addMonth()->toDateString();
        $month = substr($start, 0, 7);
        $invoices = DB::table('facturas')->whereNull('deleted_at')->where('fecha', '>=', $start)->where('fecha', '<', $end)
            ->get(['id', 'fecha', 'cuf', 'nFactura', 'nit', 'nombre', 'importe', 'estado'])->keyBy('id');
        $locals = collect();
        foreach (self::SOURCES as $table => $source) {
            $records = $this->localQuery($table, $source)->whereNull('deleted_at')
                ->where('fechaEmision', '>=', $start)->where('fechaEmision', '<', $end)->get();
            foreach ($records as $record) {
                $locals->put($table.':'.$record->id, $record);
            }
        }
        $monthlyInvoices = $invoices->values();
        $monthlyLocals = $locals->values();
        // Look up counterparts across all dates: a date discrepancy is not a missing CUF.
        foreach (array_chunk($monthlyLocals->pluck('cuf')->filter()->unique()->values()->all(), 1000) as $cufs) {
            foreach (DB::table('facturas')->whereNull('deleted_at')->whereIn('cuf', $cufs)
                ->get(['id', 'fecha', 'cuf', 'nFactura', 'nit', 'nombre', 'importe', 'estado']) as $invoice) {
                $invoices->put($invoice->id, $invoice);
            }
        }
        foreach (self::SOURCES as $table => $source) {
            foreach (array_chunk($invoices->pluck('cuf')->filter()->unique()->values()->all(), 1000) as $cufs) {
                foreach ($this->localQuery($table, $source)->whereIn('cuf', $cufs)->get() as $record) {
                    $locals->put($table.':'.$record->id, $record);
                }
            }
        }
        $groups = [];
        foreach ($invoices as $invoice) {
            $key = trim($invoice->cuf ?? '') ?: 'siat:'.$invoice->id;
            $groups[$key]['siat'][] = $invoice;
        }
        foreach ($locals as $id => $local) {
            $key = trim($local->cuf ?? '') ?: 'local:'.$id;
            $groups[$key]['local'][] = $local;
        }
        $rows = [];
        $parkingIds = [];
        foreach ($groups as $key => $group) {
            $siat = $group['siat'] ?? [];
            $local = $group['local'] ?? [];
            $linked = count($siat) > 0 && count($local) > 0;
            // User-provided classification; this does not create a local CUF match.
            $parking = !$local && $siat && count(array_filter($siat, fn ($r) =>
                preg_replace('/\s+/u', ' ', mb_strtoupper(trim($r->nombre ?? ''))) === 'SIN NOMBRE'
                && $this->cents($r->importe) === 1000
            )) === count($siat);
            if ($parking) foreach ($siat as $invoice) $parkingIds[$invoice->id] = true;
            $ambiguous = count($siat) > 1 || count($local) > 1;
            $siatAmount = $siat ? array_sum(array_map(fn ($r) => $this->cents($r->importe), $siat)) : null;
            $localAmount = $local ? array_sum(array_map(fn ($r) => $this->cents($r->montoTotal), $local)) : null;
            $amountDifference = $linked && !$ambiguous && $siatAmount !== $localAmount;
            $stateDifference = $linked && !$ambiguous && $this->state($siat[0]->estado) !== $this->localState($local[0]);
            $dateDifference = $linked && !$ambiguous && substr($siat[0]->fecha, 0, 10) !== substr($local[0]->fechaEmision, 0, 10);
            $issues = [];
            if ($parking) $issues[] = 'Parqueo: regla SIN NOMBRE / Bs 10; sin vínculo local';
            if ($amountDifference) $issues[] = 'Monto diferente';
            if ($stateDifference) $issues[] = 'Estado diferente';
            if ($dateDifference) $issues[] = 'Fecha diferente';
            if ($ambiguous) $issues[] = 'CUF repetido: revisar';
            foreach ($local as $record) {
                if (!$record->siatEnviado) $issues[] = 'Envío pendiente';
                if ($record->deleted_at) $issues[] = 'Venta eliminada';
            }
            $rows[] = [
                'id' => $key, 'cuf' => $siat[0]->cuf ?? $local[0]->cuf ?? null,
                'fecha' => $siat[0]->fecha ?? substr($local[0]->fechaEmision, 0, 10),
                'nFactura' => $siat[0]->nFactura ?? $local[0]->numeroFactura ?? null,
                'nit' => $siat[0]->nit ?? null, 'nombre' => $siat[0]->nombre ?? null,
                'importe' => $siatAmount === null ? null : $siatAmount / 100,
                'montoLocal' => $localAmount === null ? null : $localAmount / 100,
                'diferencia' => $linked && !$ambiguous ? ($localAmount - $siatAmount) / 100 : null,
                'estado' => $siat ? implode(', ', array_unique(array_column($siat, 'estado'))) : null,
                'estadoLocal' => $local ? implode(', ', array_unique(array_map(fn ($r) => $this->localState($r), $local))) : null,
                'origen' => $local ? implode(', ', array_unique(array_column($local, 'origen'))) : ($parking ? 'PARQUEO' : 'SIN ORIGEN'),
                'vinculo' => $linked ? 'vinculada' : ($siat ? 'solo_siat' : 'falta_siat'),
                'diferenciaMonto' => $amountDifference, 'diferenciaEstado' => $stateDifference,
                'diferenciaFecha' => $dateDifference, 'duplicado' => $ambiguous,
                'observaciones' => implode(' · ', array_unique($issues)),
                'siat' => $siat, 'ventas' => $local,
            ];
        }
        $summary = [
            'siat' => $this->totals($monthlyInvoices->all(), 'importe', fn ($r) => $this->state($r->estado) === 'ANULADA'),
            'local' => $this->totals($monthlyLocals->all(), 'montoTotal', fn ($r) => (bool) $r->siatAnulado),
            'origenes' => [],
            'parqueoSiat' => $this->totals($monthlyInvoices->filter(fn ($r) => isset($parkingIds[$r->id]))->all(), 'importe', fn ($r) => $this->state($r->estado) === 'ANULADA'),
        ];
        foreach (['BOLETERIA', 'CANDY', 'ALQUILER'] as $origin) {
            $summary['origenes'][$origin] = $this->totals($monthlyLocals->where('origen', $origin)->all(), 'montoTotal', fn ($r) => (bool) $r->siatAnulado);
        }
        foreach (['vinculada', 'solo_siat', 'falta_siat'] as $status) {
            $summary[$status] = count(array_filter($rows, fn ($r) => $r['vinculo'] === $status));
        }
        foreach (['diferenciaMonto', 'diferenciaEstado', 'diferenciaFecha', 'duplicado'] as $flag) {
            $summary[$flag] = count(array_filter($rows, fn ($r) => $r[$flag]));
        }
        $filtered = array_values(array_filter($rows, function ($row) use ($params) {
            if (!empty($params['origen']) && !in_array($params['origen'], explode(', ', $row['origen']))) return false;
            $status = $params['vinculo'] ?? '';
            if ($status && $row['vinculo'] !== $status) return false;
            $issue = $params['diferencia'] ?? '';
            if ($issue && empty($row[$issue])) return false;
            if (!empty($params['anuladas']) && !str_contains($this->state($row['estado'] ?? ''), 'ANULADA') && !str_contains($row['estadoLocal'] ?? '', 'ANULADA')) return false;
            $search = trim($params['filter'] ?? '');
            if ($search !== '') {
                $haystack = implode(' ', [$row['cuf'], $row['nFactura'], $row['nit'], $row['nombre'], $row['estado'], $row['estadoLocal'], $row['origen']]);
                if (mb_stripos($haystack, $search) === false) return false;
            }
            return true;
        }));
        usort($filtered, fn ($a, $b) => strcmp($b['fecha'], $a['fecha']) ?: strcmp($a['id'], $b['id']));
        $perPage = $params['per_page'] ?? 50;
        return ['data' => array_slice($filtered, (($params['page'] ?? 1) - 1) * $perPage, $perPage),
            'total' => count($filtered), 'resumen' => $summary, 'mes' => $month];
    }

    private function localQuery(string $table, ?string $source)
    {
        $query = DB::table($table)->select(['id', 'cuf', 'fechaEmision', 'numeroFactura', 'montoTotal', 'siatAnulado', 'siatEnviado', 'deleted_at'])
            ->selectRaw($source === null ? 'tipo AS origen' : "'{$source}' AS origen")->selectRaw("'{$table}' AS tabla");
        if ($table === 'sales') $query->where('venta', 'F');
        return $query;
    }

    private function cents($value): int
    {
        return (int) round((float) $value * 100);
    }

    private function state(?string $value): string
    {
        return str_replace(['Á', 'ANULADO', 'VALIDO'], ['A', 'ANULADA', 'VALIDA'], mb_strtoupper(trim($value ?? '')));
    }

    private function localState(object $row): string
    {
        return $row->siatAnulado ? 'ANULADA' : 'VALIDA';
    }

    private function totals(array $records, string $amount, callable $cancelled): array
    {
        $sum = 0;
        $cancelledSum = 0;
        $cancelledCount = 0;
        foreach ($records as $record) {
            $sum += $this->cents($record->$amount);
            if ($cancelled($record)) {
                $cancelledCount++;
                $cancelledSum += $this->cents($record->$amount);
            }
        }
        return ['cantidad' => count($records), 'monto' => $sum / 100, 'anuladas' => $cancelledCount,
            'montoAnulado' => $cancelledSum / 100, 'montoNoAnulado' => ($sum - $cancelledSum) / 100];
    }
}