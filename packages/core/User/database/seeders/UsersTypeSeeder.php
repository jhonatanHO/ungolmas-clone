<?php

declare(strict_types=1);

namespace Ugm\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Carga el catálogo inicial de tipos de usuario sin sobrescribir registros existentes.
 */
final class UsersTypeSeeder extends Seeder
{
    private const array DEFAULT_TYPES = [
        ['id' => 1, 'type' => 'User', 'visible' => true, 'order' => 1],
        ['id' => 2, 'type' => 'Sponsor', 'visible' => true, 'order' => 2],
    ];

    /**
     * Inserta los tipos de usuario base en la tabla prefijada.
     */
    public function run(): void
    {
        $prefix = (string) config('custom.db_table_fix', '');
        $table = $prefix.'users_type';
        $now = now();

        DB::transaction(function () use ($table, $now): void {
            foreach (self::DEFAULT_TYPES as $item) {
                if (DB::table($table)->where('id', $item['id'])->orWhere('type', $item['type'])->exists()) {
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
