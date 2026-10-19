<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Halaman yang menunya dinonaktifkan sementara (config/menu.php) dialihkan ke halaman awal pengguna.
 */
class DisabledMenu
{
    public function handle(Request $request, Closure $next): Response
    {
        $route = $request->route()?->getName();

        if ($route && in_array($route, config('menu.nonaktif', []), true) && $request->user()) {
            return redirect()->route($request->user()->homeRoute())
                ->with('error', 'Menu tersebut sedang dinonaktifkan sementara.');
        }

        return $next($request);
    }
}
