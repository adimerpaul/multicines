<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega deleted_at a las tablas de negocio que aun no lo tenian.
 *
 * Queda fuera a proposito:
 *  - momentaneos: las reservas se borran de verdad. Con soft delete la fila
 *    seguiria ocupando el indice unico de la butaca y el mapa de sala (SQL
 *    crudo) la contaria como RESERVADA para siempre.
 *  - tablas del framework (migrations, failed_jobs, password_resets,
 *    personal_access_tokens, audits) y la pivote permiso_user.
 */
return new class extends Migration
{
    /**
     * Tablas que reciben deleted_at.
     */
    private array $tablas = [
        'actions',
        'activities',
        'casts',
        'cortesias',
        'detail_candies',
        'details',
        'documents',
        'documentsectors',
        'evento_significativos',
        'eventos',
        'events',
        'facturas',
        'leyendas',
        'medidas',
        'messages',
        'motivos',
        'pago_vincular_logs',
        'permisos',
        'prevaloradas',
        'proximos',
        'rentals',
        'sectors',
        'servicios',
        'tipopagos',
        'tokens',
        'users',
        'vehiculos',
        'web_movie_actors',
        'web_movies',
        'web_studios',
    ];

    public function up()
    {
        foreach ($this->tablas as $tabla) {
            if (!Schema::hasTable($tabla) || Schema::hasColumn($tabla, 'deleted_at')) {
                continue;
            }

            Schema::table($tabla, function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down()
    {
        foreach ($this->tablas as $tabla) {
            if (!Schema::hasTable($tabla) || !Schema::hasColumn($tabla, 'deleted_at')) {
                continue;
            }

            Schema::table($tabla, function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
