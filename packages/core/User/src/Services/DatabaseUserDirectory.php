<?php

declare(strict_types=1);

namespace Ugm\User\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use stdClass;
use Ugm\User\Contracts\UserDirectory;

/**
 * Lee los perfiles y sus catálogos sin consultar contraseñas, tokens o documentos.
 */
final readonly class DatabaseUserDirectory implements UserDirectory
{
    /**
     * Obtiene una página de usuarios ordenada por identificador descendente.
     *
     * @return LengthAwarePaginator<int, stdClass>
     */
    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        $prefix = (string) config('custom.db_table_fix', '');
        $users = $prefix.'users';
        $usersType = $prefix.'users_type';
        $userStates = $prefix.'user_states';

        return DB::table($users)
            ->leftJoin($usersType, "{$users}.user_type_id", '=', "{$usersType}.id")
            ->leftJoin($userStates, "{$users}.user_state_id", '=', "{$userStates}.id")
            ->select([
                "{$users}.id", "{$users}.user", "{$users}.name", "{$users}.first_lastname",
                "{$users}.second_lastname", "{$users}.email", "{$users}.created_at",
                "{$usersType}.type as user_type", "{$userStates}.state as user_state",
            ])
            ->orderByDesc("{$users}.id")
            ->paginate($perPage);
    }
}
