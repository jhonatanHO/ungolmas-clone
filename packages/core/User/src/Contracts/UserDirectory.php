<?php

declare(strict_types=1);

namespace Ugm\User\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use stdClass;

/**
 * Expone una lectura paginada de perfiles sin datos de autenticación.
 */
interface UserDirectory
{
    /**
     * Devuelve los usuarios más recientes primero, con campos públicos del perfil.
     *
     * @return LengthAwarePaginator<int, stdClass>
     */
    public function paginate(int $perPage = 20): LengthAwarePaginator;
}
