<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Ugm\User\Database\Seeders\DocumentsTypeSeeder;

return new class extends Migration
{
    /**
     * Crea el catálogo de tipos de documento de identidad.
     *
     * @throws RuntimeException Si la tabla ya existe.
     */
    public function up(): void
    {
        $prefix = (string) config('custom.db_table_fix', '');
        $table = $prefix.'documents_type';

        if (Schema::hasTable($table)) {
            throw new RuntimeException("La tabla {$table} ya existe. Revise el esquema antes de continuar; no se sobrescribirán tablas existentes.");
        }

        Schema::create($table, function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid');
            $table->string('document');
            $table->string('short_description', 20);
            $table->integer('length');
            $table->boolean('visible');
            $table->integer('order');
            $table->timestamps();
        });

        if (app()->isLocal()) {
            require_once __DIR__.'/../seeders/DocumentsTypeSeeder.php';
            (new DocumentsTypeSeeder)->run();
        }
    }

    /**
     * Elimina el catálogo de tipos de documento.
     */
    public function down(): void
    {
        $prefix = (string) config('custom.db_table_fix', '');

        Schema::dropIfExists($prefix.'documents_type');
    }
};
