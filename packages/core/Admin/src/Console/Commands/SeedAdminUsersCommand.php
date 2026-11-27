<?php

declare(strict_types=1);

namespace Ugm\Admin\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Ugm\Admin\Database\Seeders\AdminUserSeeder;

/**
 * Ejecuta el seeder propio de Admin sin modificar el seeder o autoload del cascarón.
 */
final class SeedAdminUsersCommand extends Command
{
    protected $signature = 'admin:seed';

    protected $description = 'Crea dos administradores ficticios (solo local/testing), sin modificar cuentas existentes';

    /**
     * Carga explícitamente el seeder situado fuera de src y lo ejecuta con el contenedor.
     */
    public function handle(): int
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->error('Este comando solo está disponible en local o testing.');

            return self::FAILURE;
        }

        $prefix = (string) config('custom.db_table_fix', '');

        if (! Schema::hasTable($prefix.'admin_users')) {
            $this->error('Primero ejecuta la migración de packages/core/Admin/database/migrations.');

            return self::FAILURE;
        }

        // NOTE: database/ permanece fuera del namespace PSR-4 de src/ por arquitectura del paquete.
        require_once __DIR__.'/../../../database/seeders/AdminUserSeeder.php';

        try {
            $seeder = new AdminUserSeeder;
            $seeder->setContainer($this->laravel)->setCommand($this);
            $seeder->__invoke();
        } catch (QueryException) {
            $this->error('No se cargaron cuentas: revisa posibles conflictos de usuario o correo. No se sobrescribieron datos.');

            return self::FAILURE;
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Administradores de desarrollo preparados: admin01@example.com y admin02@example.com. Las cuentas existentes se conservaron.');

        return self::SUCCESS;
    }
}
