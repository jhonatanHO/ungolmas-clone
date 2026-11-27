<?php

declare(strict_types=1);

namespace Ugm\User\Tests\Feature;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;
use Ugm\User\Database\Seeders\DocumentsTypeSeeder;
use Ugm\User\Database\Seeders\UserStatesSeeder;
use Ugm\User\Database\Seeders\UsersTypeSeeder;

/**
 * Verifica las migraciones del módulo de usuarios exclusivamente en SQLite en memoria.
 */
final class UserSchemaTest extends TestCase
{
    private const string USERS_MIGRATION = 'packages/core/User/database/migrations/2026_11_06_000004_create_user_table.php';

    /** @var list<string> */
    private const array MIGRATIONS = [
        'packages/core/User/database/migrations/2026_11_06_000001_create_users_type_table.php',
        'packages/core/User/database/migrations/2026_11_06_000002_create_user_states_table.php',
        'packages/core/User/database/migrations/2026_11_06_000003_create_documents_type_table.php',
        self::USERS_MIGRATION,
    ];

    /**
     * Impide que estas pruebas operen sobre una BD persistente.
     */
    protected function setUp(): void
    {
        parent::setUp();

        self::assertSame('sqlite', config('database.default'));
        self::assertSame(':memory:', config('database.connections.sqlite.database'));
    }

    /**
     * Comprueba el descubrimiento de las migraciones y las columnas del volcado.
     */
    #[Test]
    public function migratesPlatformUsersAndRelatedCatalogs(): void
    {
        $prefix = (string) config('custom.db_table_fix', '');
        $usersTable = $prefix.'users';
        $usersTypeTable = $prefix.'users_type';
        $userStatesTable = $prefix.'user_states';
        $documentsTypeTable = $prefix.'documents_type';

        $this->artisanCommand('migrate', ['--force' => true])->assertExitCode(0);

        self::assertTrue(Schema::hasColumns($usersTable, [
            'id', 'uuid', 'user_type_id', 'document_type_id', 'user_state_id', 'user',
            'name', 'first_lastname', 'second_lastname', 'email', 'password', 'password2',
            'cell_phone', 'cell_phone_code', 'address', 'country', 'path_photo', 'number_doc',
            'birthday', 'time_zone', 'facebook_id', 'google_id', 'validate_code', 'avatar',
            'avatar_uuid', 'points', 'coins', 'latitude', 'longitude', 'email_verified_at',
            'recover_password_token', 'remember_token', 'created_at', 'updated_at',
        ]));
        foreach ([$usersTable, $usersTypeTable, $userStatesTable, $documentsTypeTable] as $tableName) {
            self::assertSame(0, DB::table($tableName)->count());
        }

        self::assertTrue(Schema::hasTable('password_reset_tokens'));
        self::assertTrue(Schema::hasTable('sessions'));
        self::assertSame(
            [$documentsTypeTable, $userStatesTable, $usersTypeTable],
            collect(Schema::getForeignKeys($usersTable))->pluck('foreign_table')->sort()->values()->all(),
        );

        $this->insertCatalogs();
        DB::table($usersTable)->insert($this->userAttributes());

        $user = DB::table($usersTable)->first();
        self::assertNotNull($user);
        self::assertSame(1, $user->user_state_id);
        self::assertSame('America/Lima', $user->time_zone);
        self::assertSame(0, $user->points);
        self::assertSame(0, $user->coins);
        self::assertNull($user->name);
    }

    /**
     * Comprueba que los catálogos son relaciones reales, no solo columnas.
     */
    #[Test]
    public function rejectsUsersWithMissingCatalogReferences(): void
    {
        $prefix = (string) config('custom.db_table_fix', '');

        $this->artisanCommand('migrate', ['--force' => true])->assertExitCode(0);

        $this->expectException(QueryException::class);
        DB::table($prefix.'users')->insert($this->userAttributes());
    }

    /**
     * Impide modificar o clasificar automáticamente cuentas ya existentes.
     */
    #[Test]
    public function refusesToAlterExistingUsersWithoutADataMigration(): void
    {
        $prefix = (string) config('custom.db_table_fix', '');
        $usersTable = $prefix.'users';

        $initialMigration = require base_path('database/migrations/0001_01_01_000000_create_users_table.php');
        $initialMigration->up();
        DB::table($usersTable)->insert([
            'name' => 'Usuario de prueba',
            'email' => 'existing@example.com',
            'password' => Hash::make('test-only-password'),
        ]);

        try {
            $this->runMigration(self::USERS_MIGRATION, 'up');
            self::fail('La migración no debe alterar usuarios existentes.');
        } catch (RuntimeException $exception) {
            self::assertStringContainsString('migración de datos', $exception->getMessage());
        }

        self::assertSame(1, DB::table($usersTable)->count());
        self::assertFalse(Schema::hasColumn($usersTable, 'uuid'));
        self::assertFalse(Schema::hasTable($prefix.'users_type'));
    }

    /**
     * Restaura la tabla inicial y permite volver a aplicar las migraciones vacías.
     */
    #[Test]
    public function rollsBackAndMigratesAgainWithoutDroppingUsers(): void
    {
        $prefix = (string) config('custom.db_table_fix', '');
        $usersTable = $prefix.'users';

        $this->artisanCommand('migrate', ['--force' => true])->assertExitCode(0);

        foreach (array_reverse(self::MIGRATIONS) as $migrationPath) {
            $this->runMigration($migrationPath, 'down');
        }

        self::assertTrue(Schema::hasTable($usersTable));
        self::assertFalse(Schema::hasColumn($usersTable, 'uuid'));
        self::assertFalse(Schema::hasTable($prefix.'users_type'));
        self::assertFalse(Schema::hasTable($prefix.'user_states'));
        self::assertFalse(Schema::hasTable($prefix.'documents_type'));
        self::assertTrue(Schema::hasTable('sessions'));

        foreach (self::MIGRATIONS as $migrationPath) {
            $this->runMigration($migrationPath, 'up');
        }

        self::assertTrue(Schema::hasColumn($usersTable, 'uuid'));
        self::assertTrue(Schema::hasTable($prefix.'users_type'));
        self::assertTrue(Schema::hasTable($prefix.'user_states'));
        self::assertTrue(Schema::hasTable($prefix.'documents_type'));
    }

    /**
     * Protege los datos de perfil frente a una reversión destructiva accidental.
     */
    #[Test]
    public function refusesRollbackWhenUsersExist(): void
    {
        $prefix = (string) config('custom.db_table_fix', '');
        $usersTable = $prefix.'users';

        $this->artisanCommand('migrate', ['--force' => true])->assertExitCode(0);
        $this->insertCatalogs();
        DB::table($usersTable)->insert($this->userAttributes());

        try {
            $this->runMigration(self::USERS_MIGRATION, 'down');
            self::fail('La reversión no debe eliminar datos de perfil existentes.');
        } catch (RuntimeException $exception) {
            self::assertStringContainsString('usuarios existentes', $exception->getMessage());
        }

        self::assertSame(1, DB::table($usersTable)->count());
        self::assertTrue(Schema::hasColumn($usersTable, 'uuid'));
    }

    /**
     * Comprueba que los tres seeders de catálogos insertan los valores base de forma idempotente.
     */
    #[Test]
    public function seedsDefaultCatalogsIdempotently(): void
    {
        $prefix = (string) config('custom.db_table_fix', '');

        $this->artisanCommand('migrate', ['--force' => true])->assertExitCode(0);

        require_once base_path('packages/core/User/database/seeders/UsersTypeSeeder.php');
        require_once base_path('packages/core/User/database/seeders/UserStatesSeeder.php');
        require_once base_path('packages/core/User/database/seeders/DocumentsTypeSeeder.php');

        (new UsersTypeSeeder)->run();
        (new UserStatesSeeder)->run();
        (new DocumentsTypeSeeder)->run();

        self::assertSame(2, DB::table($prefix.'users_type')->count());
        self::assertSame(3, DB::table($prefix.'user_states')->count());
        self::assertSame(3, DB::table($prefix.'documents_type')->count());

        (new UsersTypeSeeder)->run();
        (new UserStatesSeeder)->run();
        (new DocumentsTypeSeeder)->run();

        self::assertSame(2, DB::table($prefix.'users_type')->count());
        self::assertSame(3, DB::table($prefix.'user_states')->count());
        self::assertSame(3, DB::table($prefix.'documents_type')->count());
    }

    /**
     * Runs a package migration after checking its requested lifecycle method.
     *
     * @param  'up'|'down'  $direction
     */
    private function runMigration(string $path, string $direction): void
    {
        $migration = require base_path($path);
        self::assertInstanceOf(Migration::class, $migration);
        $action = [$migration, $direction];
        self::assertIsCallable($action);
        $action();
    }

    /**
     * Prepara únicamente catálogos ficticios para probar integridad referencial.
     */
    private function insertCatalogs(): void
    {
        $prefix = (string) config('custom.db_table_fix', '');

        DB::table($prefix.'users_type')->insert([
            'id' => 1, 'uuid' => (string) Str::uuid(), 'type' => 'User', 'visible' => true, 'order' => 1,
        ]);
        DB::table($prefix.'user_states')->insert([
            'id' => 1, 'uuid' => (string) Str::uuid(), 'state' => 'Activo', 'order' => 1,
        ]);
        DB::table($prefix.'documents_type')->insert([
            'id' => 1, 'uuid' => (string) Str::uuid(), 'document' => 'Documento de prueba',
            'short_description' => 'TEST', 'length' => 8, 'visible' => true, 'order' => 1,
        ]);
    }

    /**
     * Devuelve una cuenta ficticia; nunca utiliza datos personales del volcado.
     *
     * @return array<string, int|string>
     */
    private function userAttributes(): array
    {
        return [
            'uuid' => (string) Str::uuid(), 'user' => 'testuser', 'email' => 'test@example.com',
            'password' => Hash::make('test-only-password'), 'user_type_id' => 1, 'document_type_id' => 1,
        ];
    }
}
