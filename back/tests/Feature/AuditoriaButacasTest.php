<?php

namespace Tests\Feature;

use App\Models\Momentaneo;
use App\Models\Sale;
use App\Services\AuditoriaButacas;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AuditoriaButacasTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('audits', function (Blueprint $table) {
            $table->id();
            $table->string('user_type')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('event');
            $table->string('auditable_type');
            $table->unsignedBigInteger('auditable_id');
            $table->text('old_values')->nullable();
            $table->text('new_values')->nullable();
            $table->text('url')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('tags')->nullable();
            $table->timestamps();
        });
    }

    private function auditoria(): AuditoriaButacas
    {
        return new AuditoriaButacas();
    }

    private function registros(string $evento): array
    {
        return DB::table('audits')->where('event', $evento)->get()
            ->map(fn ($fila) => json_decode($fila->new_values, true) + ['auditable_id' => $fila->auditable_id])
            ->all();
    }

    public function test_registra_la_butaca_seleccionada_con_la_funcion(): void
    {
        $this->auditoria()->seleccionada(73347, ['fila' => 10, 'columna' => 18, 'letra' => 'J', 'pelicula' => 'COYOTE VS ACME 2D']);

        $registros = $this->registros('butaca_seleccionada');
        $this->assertCount(1, $registros);
        $this->assertSame('J-18', $registros[0]['butaca']);
        $this->assertEquals(73347, $registros[0]['auditable_id']);
        $this->assertSame('butacas', DB::table('audits')->value('tags'));
    }

    public function test_registra_el_clic_sobre_una_butaca_ya_tomada(): void
    {
        $this->auditoria()->ocupada(73347, ['fila' => 10, 'columna' => 19, 'letra' => 'J']);

        $registros = $this->registros('butaca_ocupada');
        $this->assertCount(1, $registros);
        $this->assertSame('J-19', $registros[0]['butaca']);
    }

    public function test_agrupa_las_butacas_liberadas_por_funcion(): void
    {
        $this->auditoria()->liberadas([
            (object) ['programa_id' => 73347, 'columna' => 18, 'letra' => 'J'],
            (object) ['programa_id' => 73347, 'columna' => 20, 'letra' => 'J'],
            (object) ['programa_id' => 73333, 'columna' => 2, 'letra' => 'A'],
        ], 'clic del cajero');

        $registros = collect($this->registros('butaca_liberada'))->keyBy('auditable_id');
        $this->assertCount(2, $registros);
        $this->assertSame(['J-18', 'J-20'], $registros[73347]['butacas']);
        $this->assertSame(2, $registros[73347]['cantidad']);
        $this->assertSame('clic del cajero', $registros[73333]['motivo']);
    }

    public function test_lee_los_campos_de_los_modelos_de_momentaneo(): void
    {
        // El controlador entrega modelos Eloquent, no stdClass: si se leen con
        // un cast a array salen las propiedades internas y la butaca queda "?-?".
        $this->auditoria()->liberadas([
            new Momentaneo(['programa_id' => 73347, 'fila' => 10, 'columna' => 18, 'letra' => 'J']),
        ], 'clic del cajero');

        $registros = $this->registros('butaca_liberada');
        $this->assertSame(['J-18'], $registros[0]['butacas']);
        $this->assertEquals(73347, $registros[0]['auditable_id']);
    }

    public function test_compara_butacas_reservadas_contra_boletos_impresos(): void
    {
        $sale = new Sale();
        $sale->id = 571542;

        $this->auditoria()->boletos(
            $sale,
            [(object) ['programa_id' => 73347, 'columna' => 18, 'letra' => 'J'], (object) ['programa_id' => 73347, 'columna' => 20, 'letra' => 'J']],
            [(object) ['programa_id' => 73347, 'columna' => 18, 'letra' => 'J']],
            [['butaca' => 'J-20', 'programa_id' => 73347, 'motivo' => 'Ya existe un boleto vendido para esa butaca']]
        );

        $registros = $this->registros('boletos_generados');
        $this->assertCount(1, $registros);
        $this->assertSame(['J-18 (funcion 73347)', 'J-20 (funcion 73347)'], $registros[0]['seleccionadas']);
        $this->assertSame(['J-18 (funcion 73347)'], $registros[0]['impresas']);
        $this->assertFalse($registros[0]['coinciden']);
        $this->assertEquals(571542, $registros[0]['auditable_id']);
    }

    public function test_una_venta_sin_descartes_queda_marcada_como_coincidente(): void
    {
        $sale = new Sale();
        $sale->id = 1;

        $this->auditoria()->boletos($sale, [(object) ['programa_id' => 1, 'columna' => 1, 'letra' => 'A']], [(object) ['programa_id' => 1, 'columna' => 1, 'letra' => 'A']], []);

        $this->assertTrue($this->registros('boletos_generados')[0]['coinciden']);
    }
}
