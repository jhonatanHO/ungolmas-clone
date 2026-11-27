<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Ugm\Admin\Database\Seeders\AdminUserSeeder;

return new class extends Migration
{
    /**
     * Crea las cuentas administrativas sin dependencias de los usuarios públicos.
     */
    public function up(): void
    {
        $prefix = (string) config('custom.db_table_fix', '');

        Schema::create($prefix.'admin_users', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('user', 15)->unique();
            $table->string('email', 150)->unique();
            $table->string('name');
            $table->string('first_lastname');
            $table->string('second_lastname')->nullable();
            $table->string('password');
            $table->timestamps();
        });

        // NOTE: En entorno local inserta automáticamente las cuentas de prueba tras crear la tabla.
        if (app()->isLocal()) {
            require_once __DIR__.'/../seeders/AdminUserSeeder.php';
            (new AdminUserSeeder)->run();
        }
    }

    /**
     * Elimina únicamente la tabla propiedad del paquete Admin.
     */
    public function down(): void
    {
        $prefix = (string) config('custom.db_table_fix', '');

        Schema::dropIfExists($prefix.'admin_users');
    }
};
