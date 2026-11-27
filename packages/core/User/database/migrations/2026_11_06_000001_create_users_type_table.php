<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Ugm\User\Database\Seeders\UsersTypeSeeder;

return new class extends Migration
{
    /**
     * Crea el catálogo de tipos de usuario de la plataforma.
     *
     * @throws RuntimeException Si la tabla ya existe.
     */
    public function up(): void
    {
        $prefix = (string) config('custom.db_table_fix', '');
        $table = $prefix.'users_type';

        if (Schema::hasTable($table)) {
            throw new RuntimeException("La tabla {$table} ya existe. Revise el esquema antes de continuar; no se sobrescribirán tablas existentes.");
        }

        Schema::create($table, function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid');
            $table->string('type', 50);
            $table->boolean('visible');
            $table->integer('order');
            $table->timestamps();
        });

        if (app()->isLocal()) {
            require_once __DIR__.'/../seeders/UsersTypeSeeder.php';
            (new UsersTypeSeeder)->run();
        }
    }

    /**
     * Elimina el catálogo de tipos de usuario.
     */
    public function down(): void
    {
        $prefix = (string) config('custom.db_table_fix', '');

        Schema::dropIfExists($prefix.'users_type');
    }
};
