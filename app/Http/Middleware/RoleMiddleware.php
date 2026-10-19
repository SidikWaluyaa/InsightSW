<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!$request->user() || !$request->user()->hasRole($roles)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }
            
            // Arahkan ke halaman awal sesuai peran (User::homeRoute) agar tidak terjadi redirect berulang
            $redirectRoute = $request->user()?->homeRoute() ?? 'login';

            return redirect()->route($redirectRoute)->with('error', 'Anda tidak memiliki hak akses untuk halaman tersebut.');
        }

        return $next($request);
    }
}
