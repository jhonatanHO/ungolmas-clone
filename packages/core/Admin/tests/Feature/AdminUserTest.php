<?php

declare(strict_types=1);

namespace Ugm\Admin\Tests\Feature;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Symfony\Component\Console\Output\BufferedOutput;
use Tests\TestCase;
use Ugm\Admin\Database\Seeders\AdminUserSeeder;
use Ugm\Admin\Models\AdminUser;

/**
 * Comprueba el esquema y los administradores iniciales en una BD aislada en memoria.
 */
final class AdminUserTest extends TestCase
{
    private const string MIGRATION = 'packages/core/Admin/database/migrations/2026_11_06_000002_create_admin_users_table.php';

    /**
     * Impide operar sobre una conexión persistente o datos de producción.
     */
    protected function setUp(): void
    {
        parent::setUp();

        self::assertSame('sqlite', config('database.default'));
        self::assertSame(':memory:', config('database.connections.sqlite.database'));
        config()->set('admin.seed_passwords', [
            'admin01' => 'TestingOnly-Admin01!',
            'admin02' => 'TestingOnly-Admin02!',
        ]);
    }

    /**
     * Verifica que la migración de Admin no requiere tablas de usuarios públicos.
     */
    #[Test]
    public function migratesOnlyTheIndependentAdminSchema(): void
    {
        $prefix = (string) config('custom.db_table_fix', '');
        $adminTable = $prefix.'admin_users';
        $usersTable = $prefix.'users';

        $this->migrateAdmin();

        self::assertTrue(Schema::hasColumns($adminTable, [
            'id', 'uuid', 'user', 'email', 'name', 'first_lastname', 'second_lastname',
            'password', 'created_at', 'updated_at',
        ]));
        self::assertFalse(Schema::hasColumn($adminTable, 'user_type_id'));
        self::assertFalse(Schema::hasColumn($adminTable, 'user_state_id'));
        self::assertSame([], Schema::getForeignKeys($adminTable));
        self::assertFalse(Schema::hasTable($usersTable));
        self::assertSame(0, AdminUser::query()->count());
    }

    /**
     * Comprueba que en entorno local la migración inserta automáticamente los administradores demo.
     */
    #[Test]
    public function migratesAndSeedsAutomaticallyInLocalEnvironment(): void
    {
        $this->app->instance('env', 'local');

        $this->migrateAdmin();

        self::assertSame(2, AdminUser::query()->count());
        self::assertTrue(AdminUser::query()->where('user', 'admin01')->exists());
        self::assertTrue(AdminUser::query()->where('user', 'admin02')->exists());
    }

    /**
     * Comprueba las dos cuentas ficticias, UUID automáticos y contraseñas ocultas/hasheadas.
     */
    #[Test]
    public function seedsTwoAdminsWithIndependentIdsAndHashedPasswords(): void
    {
        $this->migrateAdmin();
        $this->artisanCommand('admin:seed')->assertExitCode(0);

        self::assertSame(2, AdminUser::query()->count());
        foreach (['admin01', 'admin02'] as $user) {
            $admin = AdminUser::query()->where('user', $user)->firstOrFail();
            self::assertGreaterThan(0, $admin->id);
            self::assertTrue(Str::isUuid($admin->uuid));
            self::assertSame($user.'@example.com', $admin->email);
            self::assertSame('Administrador', $admin->name);
            self::assertSame('Demo', $admin->first_lastname);
            self::assertTrue(Hash::check(config('admin.seed_passwords.'.$user), $admin->password));
            self::assertArrayNotHasKey('password', $admin->toArray());
        }
        self::assertSame(2, AdminUser::query()->distinct()->count('uuid'));
        self::assertFalse(Schema::hasTable(((string) config('custom.db_table_fix', '')).'users'));
    }

    /**
     * Mantiene UUID, perfil y contraseña al volver a ejecutar el seeder.
     */
    #[Test]
    public function repeatedSeedingPreservesExistingAccounts(): void
    {
        $this->migrateAdmin();
        $this->artisanCommand('admin:seed')->assertExitCode(0);
        $admin = AdminUser::query()->where('user', 'admin01')->firstOrFail();
        $admin->update(['name' => 'Nombre modificado', 'password' => 'ChangedOnlyForTesting!']);
        $before = AdminUser::query()->orderBy('id')->get()->map->getAttributes()->all();

        config()->set('admin.seed_passwords', ['admin01' => 'OtherTestingPassword!', 'admin02' => 'OtherTestingPassword!']);
        $this->artisanCommand('admin:seed')->assertExitCode(0);

        self::assertSame(2, AdminUser::query()->count());
        self::assertSame($before, AdminUser::query()->orderBy('id')->get()->map->getAttributes()->all());
        self::assertTrue(Hash::check('ChangedOnlyForTesting!', $admin->refresh()->password));
    }

    /**
     * Comprueba la unicidad de cada identificador administrativo.
     */
    #[Test]
    #[DataProvider('uniqueFields')]
    public function rejectsDuplicateIdentifiers(string $field): void
    {
        $this->migrateAdmin();
        $this->artisanCommand('admin:seed')->assertExitCode(0);
        $admin = AdminUser::query()->where('user', 'admin01')->firstOrFail();
        $attributes = [
            'uuid' => (string) Str::uuid(), 'user' => 'anotheradmin', 'email' => 'another@example.com',
            'name' => 'Otro', 'first_lastname' => 'Administrador', 'password' => Hash::make('TestingOnly-Password!'),
        ];
        $attributes[$field] = $admin->getAttribute($field);

        $this->expectException(QueryException::class);
        DB::table(((string) config('custom.db_table_fix', '')).'admin_users')->insert($attributes);
    }

    /**
     * Proporciona las columnas que deben ser únicas.
     *
     * @return array<string, array{string}>
     */
    public static function uniqueFields(): array
    {
        return ['uuid' => ['uuid'], 'usuario' => ['user'], 'correo' => ['email']];
    }

    /**
     * Revierte toda la carga si una identidad de demostración entra en conflicto.
     */
    #[Test]
    public function conflictingAccountsAreNotOverwrittenOrPartiallySeeded(): void
    {
        $this->migrateAdmin();
        $existing = AdminUser::query()->create([
            'user' => 'admin02', 'email' => 'different@example.com', 'name' => 'Existente',
            'first_lastname' => 'Prueba', 'password' => 'TestingOnly-Password!',
        ]);
        $before = $existing->refresh()->getAttributes();

        $this->artisanCommand('admin:seed')->assertExitCode(1);

        self::assertSame(1, AdminUser::query()->count());
        self::assertSame($before, $existing->refresh()->getAttributes());
        self::assertFalse(AdminUser::query()->where('user', 'admin01')->exists());
    }

    /**
     * Impide cargar cuentas ficticias en producción desde el comando.
     */
    #[Test]
    public function commandRefusesProductionSeeding(): void
    {
        $this->migrateAdmin();
        $this->app->instance('env', 'production');

        $this->artisanCommand('admin:seed')->assertExitCode(1);
        self::assertSame(0, AdminUser::query()->count());
    }

    /**
     * Impide saltarse la protección de entorno invocando directamente el seeder.
     */
    #[Test]
    public function seederAlsoRefusesProduction(): void
    {
        require_once base_path('packages/core/Admin/database/seeders/AdminUserSeeder.php');
        $this->app->instance('env', 'production');

        $this->expectException(RuntimeException::class);
        (new AdminUserSeeder)->run();
    }

    /**
     * Informa si falta ejecutar la migración, sin crear tablas implícitamente.
     */
    #[Test]
    public function seedingRequiresTheAdminMigration(): void
    {
        $this->artisanCommand('admin:seed')->assertExitCode(1);
        self::assertFalse(Schema::hasTable(((string) config('custom.db_table_fix', '')).'admin_users'));
    }

    /**
     * Rechaza contraseñas de desarrollo demasiado cortas sin dejar cuentas parciales.
     */
    #[Test]
    public function rejectsWeakConfiguredPasswordsAtomically(): void
    {
        $this->migrateAdmin();
        config()->set('admin.seed_passwords.admin02', 'short');

        $this->artisanCommand('admin:seed')->assertExitCode(1);
        self::assertSame(0, AdminUser::query()->count());
    }

    /**
     * Comprueba las claves ficticias del seeder cuando no hay valores configurados.
     */
    #[Test]
    public function usesDefaultPasswordsWhenTheyAreNotConfigured(): void
    {
        $this->migrateAdmin();
        config()->set('admin.seed_passwords', ['admin01' => null, 'admin02' => null]);

        $output = new BufferedOutput;
        self::assertSame(0, Artisan::call('admin:seed', [], $output));
        $text = $output->fetch();
        $admins = AdminUser::query()->orderBy('id')->get();
        self::assertCount(2, $admins);
        foreach ($admins as $admin) {
            self::assertTrue(Hash::isHashed($admin->password));
            self::assertSame(1, preg_match('/^'.$admin->user.': ([^\r\n]+)$/m', $text, $matches));
            self::assertTrue(Hash::check($matches[1], $admin->password));
            $expectedPassword = $admin->user === 'admin01' ? 'Admin01-LocalOnly!' : 'Admin02-LocalOnly!';
            self::assertSame($expectedPassword, $matches[1]);
        }
        $firstAdmin = $admins->get(0);
        $secondAdmin = $admins->get(1);
        self::assertInstanceOf(AdminUser::class, $firstAdmin);
        self::assertInstanceOf(AdminUser::class, $secondAdmin);
        self::assertNotSame($firstAdmin->password, $secondAdmin->password);

        self::assertSame(0, Artisan::call('admin:seed', [], $output));
        self::assertStringNotContainsString('admin01:', $output->fetch());
    }

    /**
     * Verifica que revertir Admin deja intactas las cuentas públicas existentes.
     */
    #[Test]
    public function rollbackDoesNotTouchPublicUsers(): void
    {
        $prefix = (string) config('custom.db_table_fix', '');
        $usersTable = $prefix.'users';
        $adminTable = $prefix.'admin_users';

        $initialMigration = require base_path('database/migrations/0001_01_01_000000_create_users_table.php');
        $initialMigration->up();
        DB::table($usersTable)->insert([
            'name' => 'Usuario público', 'email' => 'public@example.com', 'password' => Hash::make('TestingOnly-Public!'),
        ]);
        $this->migrateAdmin();
        $this->artisanCommand('admin:seed')->assertExitCode(0);

        $migration = require base_path(self::MIGRATION);
        self::assertInstanceOf(Migration::class, $migration);
        $rollback = [$migration, 'down'];
        self::assertIsCallable($rollback);
        $rollback();

        self::assertFalse(Schema::hasTable($adminTable));
        self::assertSame(1, DB::table($usersTable)->count());
        self::assertSame('public@example.com', DB::table($usersTable)->value('email'));
    }

    /**
     * Ejecuta únicamente las migraciones registradas dentro de Admin.
     */
    private function migrateAdmin(): void
    {
        $this->artisanCommand('migrate', [
            '--path' => 'packages/core/Admin/database/migrations', '--force' => true,
        ])->assertExitCode(0);
    }
}
