<?php

namespace Tests\Feature;

use App\Models\Factura;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;
use ZipArchive;

class FacturaImportTest extends TestCase
{
    private array $files = [];

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();
            foreach ((new Factura)->getFillable() as $column) {
                $table->string($column)->nullable();
            }
            $table->timestamps();
            $table->softDeletes();
        });
        $this->withoutMiddleware();
    }

    protected function tearDown(): void
    {
        foreach ($this->files as $file) {
            unlink($file);
        }
        parent::tearDown();
    }

    private function excel(array $rows, bool $validHeaders = true): string
    {
        $map = (new \ReflectionClass(\App\Services\FacturaFileImport::class))->getConstant('COLUMNS');
        $sheet = new Spreadsheet;
        $sheet->getActiveSheet()->fromArray([$validHeaders ? array_values($map) : ['incorrecto'], ...$rows]);
        $path = tempnam(sys_get_temp_dir(), 'test_factura_');
        $this->files[] = $path;
        (new Xlsx($sheet))->save($path);
        $sheet->disconnectWorksheets();
        return $path;
    }

    private function row(string $cuf = 'CUF-1', string $date = '31/08/2026'): array
    {
        return [1, $date, 82, $cuf, '001234', '', 'CLIENTE', 35, 0, 0, 0, 0, 0, 0, 0, 35, 0, 0, 35, 4.55, 'VALIDA', '', 'OTROS', 'SI', 'PENDIENTE'];
    }

    private function upload(string $path, string $name = 'ventas.xlsx')
    {
        return $this->postJson('/api/import', ['archivo' => new UploadedFile($path, $name, null, null, true)]);
    }

    public function test_excel_and_zip_import_are_repeatable_and_preserve_history(): void
    {
        Factura::create(['cuf' => 'JULIO', 'nFactura' => 1, 'fecha' => '2026-07-01']);
        $path = $this->excel([$this->row()]);
        $this->upload($path)->assertOk()->assertJson(['insertadas' => 1, 'total' => 1, 'meses' => ['2026-08']]);
        Factura::where('cuf', 'CUF-1')->update(['impuesto' => 'SI']);
        $zipPath = tempnam(sys_get_temp_dir(), 'test_zip_');
        $this->files[] = $zipPath;
        $zip = new ZipArchive;
        $zip->open($zipPath, ZipArchive::OVERWRITE);
        $zip->addFile($path, 'carpeta/archivoVentas.xlsx');
        $zip->close();
        $this->upload($zipPath, 'ventas.zip')->assertOk()->assertJson(['insertadas' => 0, 'omitidas' => 1]);
        $this->assertSame(2, Factura::count());
        $this->assertDatabaseHas('facturas', ['cuf' => 'CUF-1', 'nit' => '001234', 'impuesto' => 'SI']);
        $this->assertDatabaseHas('facturas', ['cuf' => 'JULIO']);
    }

    public function test_existing_cuf_is_skipped_even_with_a_different_invoice_number(): void
    {
        Factura::create(['cuf' => 'CUF-1', 'nFactura' => 999, 'fecha' => '2026-07-01', 'importe' => '80', 'estado' => 'ANULADA']);
        $this->upload($this->excel([$this->row()]))->assertOk()->assertJson(['insertadas' => 0, 'omitidas' => 1]);
        $this->assertSame(1, Factura::count());
        $this->assertDatabaseHas('facturas', ['cuf' => 'CUF-1', 'nFactura' => 999, 'importe' => '80', 'estado' => 'ANULADA']);
        Factura::where('cuf', 'CUF-1')->delete();
        $this->upload($this->excel([$this->row()]))->assertOk()->assertJson(['insertadas' => 0, 'omitidas' => 1]);
        $this->assertSame(1, Factura::withTrashed()->count());
    }

    public function test_repeated_cuf_inside_excel_is_inserted_only_once(): void
    {
        $second = $this->row();
        $second[2] = 999;
        $this->upload($this->excel([$this->row(), $second]))->assertOk()->assertJson(['insertadas' => 1]);
        $this->assertSame(1, Factura::count());
        $this->assertDatabaseHas('facturas', ['cuf' => 'CUF-1', 'nFactura' => 82]);
    }
    public function test_invalid_file_does_not_write_partial_rows(): void
    {
        $this->upload($this->excel([$this->row(), $this->row('BAD', '31/02/2026')]))->assertStatus(422);
        $this->assertSame(0, Factura::count());
        $this->upload($this->excel([], false))->assertStatus(422);
        $this->postJson('/api/import')->assertStatus(422);
    }

    public function test_month_filter_search_and_pagination(): void
    {
        Factura::create(['cuf' => 'JULIO', 'fecha' => '2026-07-31']);
        Factura::create(['cuf' => 'AGOSTO1', 'fecha' => '2026-08-01', 'nombre' => 'ANA', 'impuesto' => 'SI']);
        Factura::create(['cuf' => 'AGOSTO2', 'fecha' => '2026-08-31', 'nombre' => 'JUAN']);
        Factura::create(['cuf' => 'SEPTIEMBRE', 'fecha' => '2026-09-01']);
        $this->postJson('/api/getYearMonthFacturas', ['anio' => 2026, 'mes' => 8, 'per_page' => 1])
            ->assertOk()->assertJsonPath('total', 2)->assertJsonCount(1, 'data')->assertJsonPath('data.0.cuf', 'AGOSTO2');
        $this->postJson('/api/getYearMonthFacturas', ['anio' => 2026, 'mes' => 8, 'filter' => 'ANA'])
            ->assertOk()->assertJsonPath('total', 1)->assertJsonPath('data.0.cuf', 'AGOSTO1');
        $this->getJson('/api/buscarFacturas?anio=2026&mes=7')->assertOk()->assertJsonPath('total', 1);
        $this->postJson('/api/getYearMonthFacturas', ['anio' => 2026, 'mes' => 0])->assertStatus(422);
    }
}
