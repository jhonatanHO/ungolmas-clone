<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * Admin - Admin Routes (/cmsnet)
 */
Route::get('/', function (Request $request) {
    if ($request->wantsJson()) {
        return response()->json([
            'platform' => 'UNGOLMAS Core',
            'module' => 'Admin Panel (CMSNET)',
            'status' => 'operational',
            'prefix' => config('app.admin_url', 'cmsnet'),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    return view('admin::dashboard');
})->name('admin.dashboard');
