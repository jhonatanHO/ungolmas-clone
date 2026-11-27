<?php

declare(strict_types=1);

namespace Ugm\Admin\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Ugm\Admin\Traits\AdminSeoMetaData;
use Ugm\User\Contracts\UserDirectory;

/*
|--------------------------------------------------------------------------
| AdminPageController
|--------------------------------------------------------------------------
|
| Unified controller for rendering CMSNET Admin views and enforcing session access.
|
*/

final class AdminPageController
{
    use AdminSeoMetaData;

    /**
     * Initializes the admin page controller with the user directory service.
     *
     * @param  UserDirectory  $directory  Read-only user directory contract.
     */
    public function __construct(
        private readonly UserDirectory $directory,
    ) {}

    /**
     * Renders the requested admin page or redirects based on the `session-admin` state and privacy flag.
     *
     * @param  Request  $request  Incoming HTTP request.
     * @param  string  $page  Page identifier defined in the route defaults.
     * @param  bool|null  $private  Explicit privacy flag indicating whether an active `session-admin` is required.
     */
    public function viewRenderPageAdmin(
        Request $request,
        string $page = 'dashboard',
        ?bool $private = null,
    ): View|RedirectResponse|JsonResponse {
        if ($page === 'dashboard' && $request->wantsJson()) {
            return response()->json([
                'platform' => 'UNGOLMAS Core',
                'module' => 'Admin Panel (CMSNET)',
                'status' => 'operational',
                'prefix' => config('custom.admin_url', 'cmsnet'),
                'timestamp' => now()->toIso8601String(),
            ]);
        }

        $config = $this->getAdminPageData($page);
        if ($config === null) {
            abort(404);
        }

        $isPrivate = $private ?? $config['private'];
        $hasAdminSession = (bool) $request->session()->get('session-admin', false);

        if ($isPrivate && ! $hasAdminSession) {
            return redirect()->route('admin.login');
        }

        if (! $isPrivate && $hasAdminSession) {
            return redirect()->route('admin.dashboard');
        }

        if ($page === 'logout') {
            $request->session()->forget('session-admin');

            return redirect()->route('admin.login');
        }

        $viewData = [
            'pageTitle' => $config['title'],
            'SEO' => $config['seo'],
            'login' => $hasAdminSession,
        ];

        if ($config['view'] === 'admin::dashboard') {
            // NOTE: Do not expose user profiles outside local/testing environments.
            abort_unless(app()->environment(['local', 'testing']), 403);

            $users = $this->directory->paginate();
            $viewData['users'] = $users;
            $viewData['totalUsers'] = $users->total();
        }

        return view((string) $config['view'], $viewData);
    }
}
