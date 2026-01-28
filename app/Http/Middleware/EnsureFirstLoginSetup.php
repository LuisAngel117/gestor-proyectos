<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFirstLoginSetup
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        if ($user->requiresFirstLoginSetup()) {
            if ($request->routeIs('first-login.*', 'logout')) {
                return $next($request);
            }

            return redirect()
                ->route('first-login.show')
                ->with('info', 'Completa tu perfil y cambia tu contraseña para continuar.');
        }

        return $next($request);
    }
}
