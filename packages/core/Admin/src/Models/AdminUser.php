<?php

declare(strict_types=1);

namespace Ugm\Admin\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Identidad administrativa independiente de las cuentas públicas de UNGOLMAS.
 *
 * @property int $id
 * @property string $uuid
 * @property string $user
 * @property string $email
 * @property string $name
 * @property string $first_lastname
 * @property string|null $second_lastname
 * @property string $password
 */
final class AdminUser extends Authenticatable
{
    use HasUuids;

    protected $table = 'tg_admin_users';

    /**
     * Obtiene el nombre de la tabla aplicando el prefijo configurado en custom.db_table_fix.
     */
    public function getTable(): string
    {
        $prefix = (string) config('custom.db_table_fix', '');

        return $prefix.'admin_users';
    }

    protected $fillable = [
        'user',
        'email',
        'name',
        'first_lastname',
        'second_lastname',
        'password',
    ];

    protected $hidden = ['password'];

    // NOTE: Esta etapa no implementa persistencia de acceso ni remember_token.
    protected $rememberTokenName = '';

    /**
     * Genera el UUID público sin sustituir la clave primaria autoincremental.
     *
     * @return list<string>
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    /**
     * Hashea automáticamente las contraseñas asignadas al modelo.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }
}
