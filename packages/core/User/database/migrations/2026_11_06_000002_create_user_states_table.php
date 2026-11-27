<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Ugm\User\Database\Seeders\UserStatesSeeder;

return new class extends Migration
{
    /**
     * Crea el catálogo de estados de cuenta de usuario.
     *
     * @throws RuntimeException Si la tabla ya existe.
     */
    public function up(): void
    {
        $prefix = (string) config('custom.db_table_fix', '');
        $table = $prefix.'user_states';

        if (Schema::hasTable($table)) {
            throw new RuntimeException("La tabla {$table} ya existe. Revise el esquema antes de continuar; no se sobrescribirán tablas existentes.");
        }

        Schema::create($table, function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('state', 50);
            $table->string('description')->nullable();
            $table->integer('order');
            $table->boolean('visible')->default(true);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
        });

        if (app()->isLocal()) {
            require_once __DIR__.'/../seeders/UserStatesSeeder.php';
            (new UserStatesSeeder)->run();
        }
    }

    /**
     * Elimina el catálogo de estados de usuario.
     */
    public function down(): void
    {
        $prefix = (string) config('custom.db_table_fix', '');

        Schema::dropIfExists($prefix.'user_states');
    }
};
