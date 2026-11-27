<?php

declare(strict_types=1);

namespace Ugm\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Carga el catálogo inicial de tipos de documento de identidad sin sobrescribir registros existentes.
 */
final class DocumentsTypeSeeder extends Seeder
{
    private const array DEFAULT_DOCUMENTS = [
        ['id' => 1, 'document' => 'Documento Nacional de Identidad', 'short_description' => 'DNI', 'length' => 8, 'visible' => true, 'order' => 1],
        ['id' => 2, 'document' => 'Carnet de Extranjería', 'short_description' => 'CE', 'length' => 12, 'visible' => true, 'order' => 2],
        ['id' => 3, 'document' => 'Pasaporte', 'short_description' => 'PAS', 'length' => 12, 'visible' => true, 'order' => 3],
    ];

    /**
     * Inserta los tipos de documento base en la tabla prefijada.
     */
    public function run(): void
    {
        $prefix = (string) config('custom.db_table_fix', '');
        $table = $prefix.'documents_type';
        $now = now();

        DB::transaction(function () use ($table, $now): void {
            foreach (self::DEFAULT_DOCUMENTS as $item) {
                if (DB::table($table)->where('id', $item['id'])->orWhere('short_description', $item['short_description'])->exists()) {
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
