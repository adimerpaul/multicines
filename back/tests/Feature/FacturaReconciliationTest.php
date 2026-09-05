<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FacturaReconciliationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();
            foreach (['cuf', 'fecha', 'nFactura', 'nit', 'nombre', 'importe', 'estado'] as $column) $table->string($column)->nullable();
            $table->softDeletes();
        });
        foreach (['sales', 'sale_candies', 'rentals'] as $name) {
            Schema::create($name, function (Blueprint $table) {
                $table->id();
                foreach (['cuf', 'fechaEmision', 'numeroFactura', 'montoTotal', 'tipo', 'venta'] as $column) $table->string($column)->nullable();
                $table->boolean('siatAnulado')->default(false);
                $table->boolean('siatEnviado')->default(true);
                $table->softDeletes();
            });
        }
        $this->withoutMiddleware();
    }

    private function invoice(string $cuf, array $values = []): void
    {
        DB::table('facturas')->insert($values + ['cuf' => $cuf, 'fecha' => '2026-08-15', 'importe' => 100, 'estado' => 'VALIDA', 'nFactura' => 1]);
    }

    private function sale(string $cuf, array $values = [], string $table = 'sales'): void
    {
        DB::table($table)->insert($values + ['cuf' => $cuf, 'fechaEmision' => '2026-08-15 12:00:00', 'montoTotal' => 100, 'numeroFactura' => 1, 'tipo' => 'BOLETERIA', 'venta' => 'F']);
    }

    private function report(array $filters = [])
    {
        return $this->postJson('/api/facturasConciliacion', $filters + ['anio' => 2026, 'mes' => 8]);
    }

    public function test_both_sides_origins_amounts_states_and_summaries(): void
    {
        $this->invoice('TICKET'); $this->sale('TICKET');
        $this->invoice('CANDY', ['estado' => 'ANULADA']);
        $this->sale('CANDY', ['tipo' => 'CANDY', 'montoTotal' => 105]);
        $this->invoice('RENT'); $this->sale('RENT', [], 'rentals');
        $this->invoice('ONLY-SIAT'); $this->sale('ONLY-LOCAL', ['siatAnulado' => true]);
        $this->sale('', ['venta' => 'R', 'montoTotal' => 999]);
        $response = $this->report()->assertOk()->assertJsonPath('total', 5)
            ->assertJsonPath('resumen.vinculada', 3)->assertJsonPath('resumen.solo_siat', 1)->assertJsonPath('resumen.falta_siat', 1)
            ->assertJsonPath('resumen.diferenciaMonto', 1)->assertJsonPath('resumen.diferenciaEstado', 1)
            ->assertJsonPath('resumen.siat.cantidad', 4)->assertJsonPath('resumen.local.cantidad', 4)
            ->assertJsonPath('resumen.local.monto', 405)->assertJsonPath('resumen.siat.montoAnulado', 100)
            ->assertJsonPath('resumen.local.anuladas', 1)->assertJsonPath('resumen.origenes.CANDY.cantidad', 1)
            ->assertJsonPath('resumen.origenes.ALQUILER.cantidad', 1);
        $row = collect($response->json('data'))->firstWhere('cuf', 'CANDY');
        $this->assertEquals(5, $row['diferencia']);
        $this->assertSame('CANDY', $row['ventas'][0]['origen']);
        $this->assertSame('CANDY', $row['siat'][0]['cuf']);
        $this->report(['vinculo' => 'solo_siat'])->assertJsonPath('total', 1)->assertJsonPath('data.0.cuf', 'ONLY-SIAT');
        $this->report(['vinculo' => 'falta_siat'])->assertJsonPath('total', 1)->assertJsonPath('data.0.cuf', 'ONLY-LOCAL');
        $this->report(['origen' => 'ALQUILER'])->assertJsonPath('total', 1);
        $this->report(['diferencia' => 'diferenciaMonto'])->assertJsonPath('total', 1)->assertJsonPath('resumen.local.cantidad', 4);
        $this->report(['anuladas' => true])->assertJsonPath('total', 2);
        $this->report(['filter' => 'ticket'])->assertJsonPath('total', 1);
        $this->report(['per_page' => 1, 'page' => 2])->assertJsonCount(1, 'data')->assertJsonPath('total', 5);
    }

    public function test_cross_month_matches_duplicates_and_missing_cuf(): void
    {
        $this->invoice('PREVIOUS-SIAT', ['fecha' => '2026-07-31']); $this->sale('PREVIOUS-SIAT');
        $this->invoice('PREVIOUS-LOCAL'); $this->sale('PREVIOUS-LOCAL', ['fechaEmision' => '2026-07-31 10:00:00']);
        $this->invoice('DUPLICATE'); $this->sale('DUPLICATE'); $this->sale('DUPLICATE', ['tipo' => 'CANDY']);
        $this->sale(''); $this->sale('');
        $this->report()->assertOk()->assertJsonPath('total', 5)->assertJsonPath('resumen.diferenciaFecha', 2)
            ->assertJsonPath('resumen.duplicado', 1)->assertJsonPath('resumen.falta_siat', 2)
            ->assertJsonPath('resumen.siat.cantidad', 2)->assertJsonPath('resumen.local.cantidad', 5);
        $this->report(['diferencia' => 'duplicado'])->assertJsonPath('total', 1)
            ->assertJsonCount(2, 'data.0.ventas')->assertJsonPath('data.0.diferencia', null);
    }

    public function test_parking_rule_is_visible_but_does_not_invent_local_link_or_double_count(): void
    {
        $this->invoice('PARK', ['nombre' => ' SIN NOMBRE ', 'importe' => 10]);
        $this->invoice('PARK-CANCELLED', ['nombre' => 'SIN NOMBRE', 'importe' => 10, 'estado' => 'ANULADA']);
        $this->invoice('CANDY10', ['nombre' => 'SIN NOMBRE', 'importe' => 10]);
        $this->sale('CANDY10', ['tipo' => 'CANDY', 'montoTotal' => 10]);
        $this->invoice('OTHER10', ['nombre' => 'CLIENTE', 'importe' => 10]);
        $this->report(['origen' => 'PARQUEO'])->assertOk()->assertJsonPath('total', 2)
            ->assertJsonPath('resumen.parqueoSiat.cantidad', 2)->assertJsonPath('resumen.parqueoSiat.montoNoAnulado', 10)
            ->assertJsonPath('resumen.siat.montoNoAnulado', 30)->assertJsonPath('resumen.local.montoNoAnulado', 10)
            ->assertJsonPath('data.0.vinculo', 'solo_siat')->assertJsonCount(0, 'data.0.ventas');
    }
    public function test_cent_precision_pending_and_deleted_counterpart_are_visible(): void
    {
        $this->invoice('CENT', ['importe' => '0.30']); $this->sale('CENT', ['montoTotal' => 0.1 + 0.2]);
        $this->invoice('DELETED'); $this->sale('DELETED', ['deleted_at' => '2026-08-16', 'siatEnviado' => false]);
        $response = $this->report()->assertOk()->assertJsonPath('resumen.diferenciaMonto', 0)->assertJsonPath('resumen.vinculada', 2);
        $row = collect($response->json('data'))->firstWhere('cuf', 'DELETED');
        $this->assertStringContainsString('Venta eliminada', $row['observaciones']);
        $this->assertStringContainsString('Envío pendiente', $row['observaciones']);
        $this->report(['mes' => 0])->assertStatus(422);
    }
}