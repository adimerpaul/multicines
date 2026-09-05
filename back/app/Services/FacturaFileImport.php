<?php

namespace App\Services;

use App\Models\Factura;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use XMLReader;
use ZipArchive;

class FacturaFileImport
{
    private const COLUMNS = [
        'n' => 'no', 'fecha' => 'fecha_de_la_factura', 'nFactura' => 'no_de_la_factura',
        'cuf' => 'codigo_de_autorizacion', 'nit' => 'nit_ci_cliente', 'complemento' => 'complemento',
        'nombre' => 'nombre_o_razon_social', 'importe' => 'importe_total_de_la_venta',
        'ice' => 'importe_ice', 'iehd' => 'importe_iehd', 'ipj' => 'importe_ipj', 'tasas' => 'tasas',
        'noSujeto' => 'otros_no_sujetos_al_iva', 'exentas' => 'exportaciones_y_operaciones_exentas',
        'tasaCero' => 'ventas_gravadas_a_tasa_cero', 'subTotal' => 'subtotal',
        'rebajas' => 'descuentos_bonificaciones_y_rebajas_sujetas_al_iva', 'card' => 'importe_gift_card',
        'importeBase' => 'importe_base_para_debito_fiscal', 'iva' => 'debito_fiscal',
        'estado' => 'estado', 'codigoControl' => 'codigo_de_control', 'tipoVenta' => 'tipo_de_venta',
        'derecho' => 'con_derecho_a_credito_fiscal', 'consolidado' => 'estado_consolidacion',
    ];

    private function invalid(string $message): void
    {
        throw ValidationException::withMessages(['archivo' => $message]);
    }

    public function import(string $path, string $extension): array
    {
        $started = microtime(true);
        $temporary = null;
        try {
            if (strtolower($extension) === 'zip') {
                $zip = new ZipArchive;
                if ($zip->open($path) !== true) {
                    $this->invalid('El ZIP no es válido.');
                }
                try {
                    $files = [];
                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        if (strtolower(pathinfo($zip->getNameIndex($i), PATHINFO_EXTENSION)) === 'xlsx') {
                            $files[] = $i;
                        }
                    }
                    if (count($files) !== 1) {
                        $this->invalid('El ZIP debe contener exactamente un archivo Excel .xlsx.');
                    }
                    $temporary = $this->extractEntry($zip, $zip->getNameIndex($files[0]));
                    $path = $temporary;
                } finally {
                    $zip->close();
                }
            } elseif (strtolower($extension) !== 'xlsx') {
                $this->invalid('Seleccione un archivo .xlsx o .zip.');
            }
            $rows = $this->readRows($path);
            if (!$rows) {
                $this->invalid('El archivo no contiene facturas.');
            }
            $months = array_values(array_unique(array_map(fn ($row) => substr($row['fecha'], 0, 7), $rows)));
            sort($months);
            $result = DB::transaction(function () use ($rows) {
                $inserted = 0;
                $skipped = 0;
                $timestamp = now();
                foreach (array_chunk($rows, 500) as $batch) {
                    // Include soft-deleted invoices: the CUF still identifies that invoice.
                    $existing = Factura::withTrashed()->whereIn('cuf', array_column($batch, 'cuf'))
                        ->pluck('cuf')->flip();
                    $newRows = [];
                    foreach ($batch as $row) {
                        if ($existing->has($row['cuf'])) {
                            $skipped++;
                            continue;
                        }
                        $row['impuesto'] = 'NO';
                        $row['created_at'] = $timestamp;
                        $row['updated_at'] = $timestamp;
                        $newRows[] = $row;
                    }
                    if ($newRows) {
                        DB::table('facturas')->insert($newRows);
                        $inserted += count($newRows);
                    }
                }
                return ['insertadas' => $inserted, 'omitidas' => $skipped];
            });
            return $result + ['total' => count($rows), 'meses' => $months, 'segundos' => round(microtime(true) - $started, 2)];
        } finally {
            if ($temporary !== null) {
                unlink($temporary);
            }
        }
    }

    private function extractEntry(ZipArchive $zip, string $name): string
    {
        $stat = $zip->statName($name);
        if (!$stat || $stat['size'] > 100 * 1024 * 1024) {
            $this->invalid('El contenido del archivo supera el límite de 100 MB.');
        }
        $source = $zip->getStream($name);
        if (!$source) {
            $this->invalid('No se pudo leer el contenido del archivo.');
        }
        $path = tempnam(sys_get_temp_dir(), 'facturas_');
        $target = fopen($path, 'wb');
        try {
            stream_copy_to_stream($source, $target);
        } finally {
            fclose($source);
            fclose($target);
        }
        return $path;
    }

    private function readRows(string $path): array
    {
        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            $this->invalid('El Excel no es válido.');
        }
        $temporary = [];
        $reader = new XMLReader;
        $previousErrors = libxml_use_internal_errors(true);
        libxml_clear_errors();
        try {
            $strings = [];
            if ($zip->locateName('xl/sharedStrings.xml') !== false) {
                $temporary[] = $shared = $this->extractEntry($zip, 'xl/sharedStrings.xml');
                $reader->open($shared, null, LIBXML_NONET);
                while ($reader->read()) {
                    if ($reader->nodeType === XMLReader::ELEMENT && $reader->localName === 'si') {
                        $xml = simplexml_load_string($reader->readOuterXml(), 'SimpleXMLElement', LIBXML_NONET);
                        $strings[] = implode('', array_map('strval', $xml->xpath('//*[local-name()="t"]')));
                    }
                }
                $reader->close();
            }
            $temporary[] = $sheet = $this->extractEntry($zip, 'xl/worksheets/sheet1.xml');
            $reader->open($sheet, null, LIBXML_NONET);
            $headers = null;
            $rows = [];
            while ($reader->read()) {
                if ($reader->nodeType !== XMLReader::ELEMENT || $reader->localName !== 'row') {
                    continue;
                }
                $xml = simplexml_load_string($reader->readOuterXml(), 'SimpleXMLElement', LIBXML_NONET);
                $values = [];
                foreach ($xml->c as $cell) {
                    $column = preg_replace('/[0-9]/', '', (string) $cell['r']);
                    $value = (string) $cell->v;
                    if ((string) $cell['t'] === 's') {
                        $value = $strings[(int) $value] ?? '';
                    } elseif ((string) $cell['t'] === 'inlineStr') {
                        $value = implode('', array_map('strval', $cell->xpath('.//*[local-name()="t"]')));
                    }
                    $values[$column] = trim($value);
                }
                if (!array_filter($values, fn ($value) => $value !== '')) {
                    continue;
                }
                if ($headers === null) {
                    $headers = array_map(fn ($value) => Str::slug(str_replace('º', 'o', $value), '_'), $values);
                    if (array_diff(array_values(self::COLUMNS), $headers)) {
                        $this->invalid('Las columnas no corresponden al reporte de ventas.');
                    }
                    $headers = array_flip($headers);
                    continue;
                }
                $row = [];
                foreach (self::COLUMNS as $field => $heading) {
                    $value = $values[$headers[$heading]] ?? '';
                    $row[$field] = $value === '' ? null : $value;
                }
                $line = (string) $xml['r'];
                if (!$row['cuf'] || !is_numeric($row['nFactura']) || !is_numeric($row['n'])) {
                    $this->invalid("Factura incompleta en la fila {$line}.");
                }
                $row['nFactura'] = (int) $row['nFactura'];
                $row['n'] = (int) $row['n'];
                $row['fecha'] = $this->date($row['fecha'], $line);
                $rows[$row['cuf']] ??= $row;
            }
            if (array_filter(libxml_get_errors(), fn ($error) => $error->level >= LIBXML_ERR_ERROR)) {
                $this->invalid('El contenido XML del Excel no es válido.');
            }
            return array_values($rows);
        } finally {
            $reader->close();
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrors);
            $zip->close();
            foreach ($temporary as $file) {
                unlink($file);
            }
        }
    }

    private function date(?string $value, string $line): string
    {
        if (is_numeric($value) && (float) $value > 0) {
            return Date::excelToDateTimeObject((float) $value)->format('Y-m-d');
        }
        foreach (['!d/m/Y', '!Y-m-d', '!d-m-Y'] as $format) {
            $date = \DateTimeImmutable::createFromFormat($format, $value ?? '');
            if ($date && $date->format(substr($format, 1)) === $value) {
                return $date->format('Y-m-d');
            }
        }
        $this->invalid("Fecha inválida en la fila {$line}.");
    }
}
