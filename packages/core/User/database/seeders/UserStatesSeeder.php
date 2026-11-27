<?php

declare(strict_types=1);

namespace Ugm\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Carga el catálogo inicial de estados de cuenta de usuario sin sobrescribir registros existentes.
 */
final class UserStatesSeeder extends Seeder
{
    private const array DEFAULT_STATES = [
        ['id' => 1, 'state' => 'Activo', 'description' => 'Cuenta activa', 'order' => 1, 'visible' => true],
        ['id' => 2, 'state' => 'Inactivo', 'description' => 'Cuenta inactiva', 'order' => 2, 'visible' => true],
        ['id' => 3, 'state' => 'Suspendido', 'description' => 'Cuenta suspendida', 'order' => 3, 'visible' => true],
    ];

    /**
     * Inserta los estados de cuenta base en la tabla prefijada.
     */
    public function run(): void
    {
        $prefix = (string) config('custom.db_table_fix', '');
        $table = $prefix.'user_states';
        $now = now();

        DB::transaction(function () use ($table, $now): void {
            foreach (self::DEFAULT_STATES as $item) {
                if (DB::table($table)->where('id', $item['id'])->orWhere('state', $item['state'])->exists()) {
                    continue;
                }

                DB::table($table)->insert([
                    ...$item,
                    'uuid' => (string) Str::uuid(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });
    }
}
