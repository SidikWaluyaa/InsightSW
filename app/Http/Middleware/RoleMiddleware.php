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
            
            // Smart Redirect based on role to avoid access loops
            $redirectRoute = 'dashboard'; // Default Marketing
            
            if ($request->user()) {
                if ($request->user()->isCs() || $request->user()->isLeaderCs()) {
                    $redirectRoute = 'cs-dashboard';
                } elseif ($request->user()->isCx()) {
                    $redirectRoute = 'cx-upsell';
                } elseif ($request->user()->isFinance()) {
                    $redirectRoute = 'finance-sync';
                } elseif ($request->user()->isGudang()) {
                    $redirectRoute = 'warehouse-dashboard';
                }
            }
            
            return redirect()->route($redirectRoute)->with('error', 'Anda tidak memiliki hak akses untuk halaman tersebut.');
        }

        return $next($request);
    }
}
