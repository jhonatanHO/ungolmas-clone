<?php

declare(strict_types=1);

namespace Ugm\Admin\Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Ugm\User\Contracts\UserDirectory;

/**
 * Verifica el listado administrativo usando exclusivamente perfiles ficticios en memoria.
 */
final class DashboardUsersTest extends TestCase
{
    /**
     * Impide que las pruebas consulten las cuentas importadas en la BD local.
     */
    protected function setUp(): void
    {
        parent::setUp();

        self::assertSame('sqlite', config('database.default'));
        self::assertSame(':memory:', config('database.connections.sqlite.database'));
    }

    /**
     * Presenta un estado vacío sin errores ni datos ficticios de correo.
     */
    #[Test]
    public function showsAnEmptyUserDirectory(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);

        $this->withSession(['session-admin' => true])
            ->get('/cmsnet')
            ->assertOk()
            ->assertSee('No hay usuarios')
            ->assertSee('0 usuarios registrados')
            ->assertDontSee('usuario01@example.com');
    }

    /**
     * Pagina los perfiles más recientes y mantiene fuera las cuentas administrativas.
     */
    #[Test]
    public function showsLatestPublicUsersWithPaginationAndRealTotals(): void
    {
        $this->createUsers(21);
        $this->artisan('admin:seed')->assertExitCode(0);

        $response = $this->withSession(['session-admin' => true])
            ->get('/cmsnet')
            ->assertOk()
            ->assertSee('21 usuarios registrados')
            ->assertSee('user21@example.com')
            ->assertSee('Activo')
            ->assertSee('Siguiente')
            ->assertDontSee('user01@example.com')
            ->assertDontSee('admin01@example.com');

        self::assertSame(21, $response->viewData('totalUsers'));
        $users = $response->viewData('users');
        self::assertCount(20, $users->items());
        self::assertSame(21, $users->items()[0]->id);
        self::assertSame(2, $users->items()[19]->id);

        $secondPage = $this->withSession(['session-admin' => true])
            ->get('/cmsnet?page=2')
            ->assertOk()
            ->assertSee('user01@example.com')
            ->assertDontSee('user21@example.com');
        self::assertCount(1, $secondPage->viewData('users')->items());
    }

    /**
     * Verifica que ni la consulta del contrato ni el HTML reciben secretos o documentos.
     */
    #[Test]
    public function neverSelectsAuthenticationOrDocumentFields(): void
    {
        $prefix = (string) config('custom.db_table_fix', '');
        $usersTable = $prefix.'users';

        $this->createUsers(1);
        DB::table($usersTable)->where('id', 1)->update([
            'password2' => 'legacy-password-sentinel',
            'recover_password_token' => 'recovery-token-sentinel',
            'number_doc' => 'PRIVATE123',
        ]);
        $directory = $this->app->make(UserDirectory::class);
        $user = $directory->paginate()->items()[0];

        self::assertSame([
            'id', 'user', 'name', 'first_lastname', 'second_lastname',
            'email', 'created_at', 'user_type', 'user_state',
        ], array_keys(get_object_vars($user)));

        $this->withSession(['session-admin' => true])
            ->get('/cmsnet')
            ->assertOk()
            ->assertDontSee(DB::table($usersTable)->value('password'), false)
            ->assertDontSee('legacy-password-sentinel')
            ->assertDontSee('recovery-token-sentinel')
            ->assertDontSee('PRIVATE123');
    }

    /**
     * Escapa nombres externos antes de renderizarlos en la tabla.
     */
    #[Test]
    public function escapesImportedProfileValues(): void
    {
        $prefix = (string) config('custom.db_table_fix', '');

        $this->createUsers(1);
        $name = '<script>alert("example")</script>';
        DB::table($prefix.'users')->where('id', 1)->update(['name' => $name]);

        $this->withSession(['session-admin' => true])
            ->get('/cmsnet')
            ->assertOk()
            ->assertSee($name)
            ->assertDontSee($name, false);
    }

    /**
     * Conserva la respuesta JSON de estado sin consultar perfiles ni requerir tablas.
     */
    #[Test]
    public function keepsStatusJsonFreeOfPersonalData(): void
    {
        $this->getJson('/cmsnet')->assertOk()
            ->assertJsonPath('module', 'Admin Panel (CMSNET)')
            ->assertJsonMissingPath('users')
            ->assertJsonMissingPath('email');
    }

    /**
     * Impide publicar los perfiles fuera del entorno local mientras no exista login real.
     */
    #[Test]
    public function refusesProfileListingOutsideLocalAndTesting(): void
    {
        $this->app->instance('env', 'production');

        $this->withSession(['session-admin' => true])
            ->get('/cmsnet')
            ->assertForbidden();
    }

    /**
     * Verifica el acceso a rutas públicas y redirección de rutas privadas cuando no existe session-admin.
     */
    #[Test]
    public function enforcesPublicAndPrivateRoutesWithoutAdminSession(): void
    {
        $this->get('/cmsnet/iniciar-sesion')
            ->assertOk()
            ->assertSee('Inicia sesión en');

        $this->get('/cmsnet/recuperar-clave')
            ->assertOk()
            ->assertSee('Recuperar contraseña');

        $this->get('/cmsnet')
            ->assertRedirect('/cmsnet/iniciar-sesion');

        $this->get('/cmsnet/cerrar-sesion')
            ->assertRedirect('/cmsnet/iniciar-sesion');

        $this->get('/cmsnet/package/core/admin')
            ->assertRedirect('/cmsnet/iniciar-sesion');
    }

    /**
     * Verifica las redirecciones de rutas públicas y acceso a rutas privadas cuando session-admin está activa.
     */
    #[Test]
    public function enforcesPublicAndPrivateRoutesWithActiveAdminSession(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);

        $this->withSession(['session-admin' => true])
            ->get('/cmsnet/iniciar-sesion')
            ->assertRedirect('/cmsnet');

        $this->withSession(['session-admin' => true])
            ->get('/cmsnet/recuperar-clave')
            ->assertRedirect('/cmsnet');

        $this->withSession(['session-admin' => true])
            ->get('/cmsnet')
            ->assertOk();

        $this->withSession(['session-admin' => true])
            ->get('/cmsnet/package/core/admin')
            ->assertOk();

        $this->withSession(['session-admin' => true])
            ->get('/cmsnet/cerrar-sesion')
            ->assertRedirect('/cmsnet/iniciar-sesion')
            ->assertSessionMissing('session-admin');
    }

    /**
     * Crea catálogos y perfiles de prueba sin consultar el volcado de producción.
     */
    private function createUsers(int $count): void
    {
        $prefix = (string) config('custom.db_table_fix', '');

        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        DB::table($prefix.'users_type')->insert([
            'id' => 1, 'uuid' => (string) Str::uuid(), 'type' => 'User', 'visible' => true, 'order' => 1,
        ]);
        DB::table($prefix.'user_states')->insert([
            'id' => 1, 'uuid' => (string) Str::uuid(), 'state' => 'Activo', 'order' => 1,
        ]);
        DB::table($prefix.'documents_type')->insert([
            'id' => 1, 'uuid' => (string) Str::uuid(), 'document' => 'Prueba',
            'short_description' => 'TEST', 'length' => 8, 'visible' => true, 'order' => 1,
        ]);
        $password = Hash::make('TestingOnly-Password!');

        for ($index = 1; $index <= $count; $index++) {
            $suffix = str_pad((string) $index, 2, '0', STR_PAD_LEFT);
            DB::table($prefix.'users')->insert([
                'id' => $index, 'uuid' => (string) Str::uuid(), 'user' => 'user'.$suffix,
                'email' => 'user'.$suffix.'@example.com', 'name' => 'Usuario '.$suffix,
                'first_lastname' => 'Prueba', 'second_lastname' => null,
                'password' => $password, 'user_type_id' => 1, 'document_type_id' => 1,
                'created_at' => '2026-11-06 12:00:00',
            ]);
        }
    }
}
