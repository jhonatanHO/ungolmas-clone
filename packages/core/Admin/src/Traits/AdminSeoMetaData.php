<?php

declare(strict_types=1);

namespace Ugm\Admin\Traits;

/*
|--------------------------------------------------------------------------
| AdminSeoMetaData
|--------------------------------------------------------------------------
|
| Resolves view templates, access privacy, and SEO metadata for CMSNET Admin pages.
|
*/

trait AdminSeoMetaData
{
    /**
     * Returns the configuration and SEO metadata for a given admin page key.
     *
     * @param  string  $page  The unique identifier of the admin page.
     * @return array{
     *     title: string,
     *     view: view-string|null,
     *     private: bool,
     *     seo: array{title: string, description: string, keywords: string}
     * }|null
     */
    public function getAdminPageData(string $page): ?array
    {
        return match ($page) {
            'login' => [
                'title' => 'Iniciar sesión | CMSNET',
                'view' => 'admin::login',
                'private' => false,
                'seo' => [
                    'title' => 'Iniciar sesión | CMSNET',
                    'description' => 'Accede al panel administrativo CMSNET de UNGOLMAS Core.',
                    'keywords' => 'cmsnet, admin, iniciar sesión, login, ungolmas',
                ],
            ],
            'forgot-password' => [
                'title' => 'Recuperar contraseña | CMSNET',
                'view' => 'admin::forgot-password',
                'private' => false,
                'seo' => [
                    'title' => 'Recuperar contraseña | CMSNET',
                    'description' => 'Recupera tu clave de acceso al panel administrativo CMSNET.',
                    'keywords' => 'cmsnet, admin, recuperar clave, contraseña, ungolmas',
                ],
            ],
            'dashboard' => [
                'title' => 'Dashboard | CMSNET',
                'view' => 'admin::dashboard',
                'private' => true,
                'seo' => [
                    'title' => 'Dashboard | CMSNET',
                    'description' => 'Panel principal de administración y directorio de usuarios en CMSNET.',
                    'keywords' => 'cmsnet, dashboard, administración, usuarios, ungolmas',
                ],
            ],
            'package-core-admin' => [
                'title' => 'Core Admin | CMSNET',
                'view' => 'admin::dashboard',
                'private' => true,
                'seo' => [
                    'title' => 'Core Admin | CMSNET',
                    'description' => 'Configuración y gestión del módulo Core Admin en CMSNET.',
                    'keywords' => 'cmsnet, package, core, admin, ungolmas',
                ],
            ],
            'logout' => [
                'title' => 'Cerrar sesión | CMSNET',
                'view' => null,
                'private' => true,
                'seo' => [
                    'title' => 'Cerrar sesión | CMSNET',
                    'description' => 'Finalización de sesión del panel administrativo CMSNET.',
                    'keywords' => 'cmsnet, cerrar sesión, logout',
                ],
            ],
            default => null,
        };
    }
}
