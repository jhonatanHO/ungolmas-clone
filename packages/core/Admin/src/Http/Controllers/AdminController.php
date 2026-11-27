<?php

declare(strict_types=1);

namespace Ugm\Admin\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| AdminController
|--------------------------------------------------------------------------
|
| Controller for managing CMSNET Admin resource operations.
|
*/

final class AdminController
{
    /**
     * Lists admin resources.
     *
     * @param  Request  $request  Incoming HTTP request.
     */
    public function index(Request $request): JsonResponse
    {
        // TODO: Implement admin index logic.
        return response()->json();
    }

    /**
     * Stores a new admin resource.
     *
     * @param  Request  $request  Incoming HTTP request.
     */
    public function store(Request $request): JsonResponse
    {
        // TODO: Implement admin store logic.
        return response()->json();
    }

    /**
     * Updates an existing admin resource.
     *
     * @param  Request  $request  Incoming HTTP request.
     * @param  int|string  $id  Resource identifier.
     */
    public function update(Request $request, int|string $id): JsonResponse
    {
        // TODO: Implement admin update logic.
        return response()->json();
    }

    /**
     * Deletes an existing admin resource.
     *
     * @param  int|string  $id  Resource identifier.
     */
    public function delete(int|string $id): JsonResponse
    {
        // TODO: Implement admin delete logic.
        return response()->json();
    }
}
