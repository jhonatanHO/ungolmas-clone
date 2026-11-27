<?php

use Illuminate\Support\Facades\Route;
use Ugm\Admin\Http\Controllers\AdminPageController;

/*
|--------------------------------------------------------------------------
| Public router
|--------------------------------------------------------------------------
|
| Pages that do not require login to access.
|
*/

Route::get('/iniciar-sesion', [AdminPageController::class, 'viewRenderPageAdmin'])
    ->name('admin.login')
    ->defaults('page', 'login')
    ->defaults('private', false);

Route::get('/recuperar-clave', [AdminPageController::class, 'viewRenderPageAdmin'])
    ->name('admin.password.request')
    ->defaults('page', 'forgot-password')
    ->defaults('private', false);

/*
|--------------------------------------------------------------------------
| Protected router
|--------------------------------------------------------------------------
|
| Pages that require an active session to access.
|
*/

Route::get('/', [AdminPageController::class, 'viewRenderPageAdmin'])
    ->name('admin.dashboard')
    ->defaults('page', 'dashboard')
    ->defaults('private', true);

Route::get('/cerrar-sesion', [AdminPageController::class, 'viewRenderPageAdmin'])
    ->name('admin.logout')
    ->defaults('page', 'logout')
    ->defaults('private', true);

Route::get('/package/core/admin', [AdminPageController::class, 'viewRenderPageAdmin'])
    ->name('admin.package.core.admin')
    ->defaults('page', 'package-core-admin')
    ->defaults('private', true);
