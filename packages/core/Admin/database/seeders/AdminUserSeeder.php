<?php

declare(strict_types=1);

namespace Ugm\Admin\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Ugm\Admin\Models\AdminUser;

/**
 * Crea dos cuentas ficticias para desarrollo sin sobrescribir cuentas existentes.
 */
final class AdminUserSeeder extends Seeder
{
    private const array DEMO_ADMINS = [
        ['user' => 'admin01', 'email' => 'admin01@example.com', 'second_lastname' => 'Uno', 'password' => 'Admin01-LocalOnly!'],
        ['user' => 'admin02', 'email' => 'admin02@example.com', 'second_lastname' => 'Dos', 'password' => 'Admin02-LocalOnly!'],
    ];

    /**
     * Carga las cuentas en una transacción y comunica las claves de desarrollo al crearlas.
     *
     * @throws RuntimeException Fuera de local/testing o con contraseñas configuradas inválidas.
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Los administradores ficticios solo se pueden cargar en local o testing.');
        }

        // NOTE: Credenciales ficticias exclusivas de local/testing; nunca usar en producción.
        $prefix = (string) config('custom.db_table_fix', '');
        $table = $prefix.'admin_users';

        $generated = DB::transaction(function () use ($table): array {
            $generated = [];

            foreach (self::DEMO_ADMINS as $item) {
                if ((new AdminUser)->setTable($table)->newQuery()->where(['user' => $item['user'], 'email' => $item['email']])->exists()) {
                    continue;
                }

                $configured = config("admin.seed_passwords.{$item['user']}");
                if ($configured !== null && (! is_string($configured) || strlen($configured) < 12)) {
                    throw new RuntimeException('Las contraseñas configuradas para el seeder deben tener al menos 12 caracteres.');
                }

                $admin = (new AdminUser)->setTable($table);
                $admin->fill([
                    ...$item,
                    'name' => 'Administrador',
                    'first_lastname' => 'Demo',
                    'password' => $configured ?? $item['password'],
                ]);
                $admin->save();

                if ($configured === null) {
                    $generated[$item['user']] = $item['password'];
                }
            }

            return $generated;
        });

        // NOTE: Solo se muestran al ejecutor local, después del commit; nunca se guardan en logs o archivos.
        if ($generated !== [] && $this->command !== null) {
            $this->command->warn('Guarda estas contraseñas de desarrollo: solo se mostrarán al crear las cuentas.');
            foreach ($generated as $user => $password) {
                $this->command->line("{$user}: {$password}");
            }
        }
    }
}
