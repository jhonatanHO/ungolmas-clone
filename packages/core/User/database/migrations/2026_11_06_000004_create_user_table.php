<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adapta la estructura inicial de Laravel al esquema de usuarios de UNGOLMAS.
     *
     * @throws RuntimeException Si la BD requiere una migración de datos previa o faltan catálogos.
     */
    public function up(): void
    {
        $prefix = (string) config('custom.db_table_fix', '');
        $usersTable = $prefix.'users';

        // NOTE: No se importan cuentas ni se inventan UUID, tipos o documentos para registros existentes.
        if (! Schema::hasTable($usersTable) || Schema::hasColumn($usersTable, 'uuid') || DB::table($usersTable)->exists()) {
            throw new RuntimeException("Esta migración requiere la tabla {$usersTable} inicial y vacía. Una BD existente necesita un plan de migración de datos independiente.");
        }

        foreach ([$prefix.'users_type', $prefix.'user_states', $prefix.'documents_type'] as $tableName) {
            if (! Schema::hasTable($tableName)) {
                throw new RuntimeException("La tabla {$tableName} es requerida antes de extender {$usersTable}.");
            }
        }

        Schema::table($usersTable, function (Blueprint $table) use ($prefix): void {
            $table->string('name')->nullable()->change();
            // NOTE: Se conserva el índice único de email de la migración inicial de Core.
            $table->string('email', 150)->change();
            $table->uuid('uuid');
            $table->foreignId('user_type_id')->constrained($prefix.'users_type')->restrictOnDelete()->restrictOnUpdate();
            $table->foreignId('document_type_id')->constrained($prefix.'documents_type')->restrictOnDelete()->restrictOnUpdate();
            $table->foreignId('user_state_id')->default(1)->constrained($prefix.'user_states');
            $table->string('user', 15);
            $table->string('first_lastname')->nullable();
            $table->string('second_lastname')->nullable();
            $table->string('cell_phone')->nullable();
            $table->string('cell_phone_code', 4)->nullable();
            $table->string('address')->nullable();
            $table->string('country', 4)->nullable();
            $table->string('path_photo')->nullable();
            $table->string('number_doc', 12)->nullable();
            $table->date('birthday')->nullable();
            $table->string('time_zone', 40)->nullable()->default('America/Lima');
            $table->text('facebook_id')->nullable();
            $table->text('google_id')->nullable();
            // NOTE: Compatibilidad estructural con el legado; password2 no se utilizará para autenticar.
            $table->string('password2')->nullable();
            $table->string('validate_code', 10)->nullable();
            $table->string('avatar', 80)->nullable();
            $table->uuid('avatar_uuid')->nullable();
            $table->integer('points')->default(0);
            $table->integer('coins')->default(0);
            $table->string('latitude', 30)->nullable();
            $table->string('longitude', 30)->nullable();
            $table->string('recover_password_token', 60)->nullable();
        });
    }

    /**
     * Restaura el esquema inicial únicamente cuando no hay cuentas que perder.
     *
     * @throws RuntimeException Si users contiene registros.
     */
    public function down(): void
    {
        $prefix = (string) config('custom.db_table_fix', '');
        $usersTable = $prefix.'users';

        if (DB::table($usersTable)->exists()) {
            throw new RuntimeException('No se puede revertir esta migración con usuarios existentes: se perderían campos de perfil. Respalde y planifique la reversión de datos.');
        }

        Schema::table($usersTable, function (Blueprint $table): void {
            $table->dropForeign(['user_type_id']);
            $table->dropForeign(['document_type_id']);
            $table->dropForeign(['user_state_id']);
            $table->dropColumn([
                'uuid', 'user_type_id', 'document_type_id', 'user_state_id', 'user',
                'first_lastname', 'second_lastname', 'cell_phone', 'cell_phone_code',
                'address', 'country', 'path_photo', 'number_doc', 'birthday', 'time_zone',
                'facebook_id', 'google_id', 'password2', 'validate_code', 'avatar',
                'avatar_uuid', 'points', 'coins', 'latitude', 'longitude', 'recover_password_token',
            ]);
            $table->string('name')->nullable(false)->change();
            $table->string('email')->change();
        });
    }
};
