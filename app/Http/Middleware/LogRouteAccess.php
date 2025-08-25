<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class LogRouteAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Registrar información sobre la ruta accedida
        Log::info('Acceso a ruta', [
            'route' => Route::currentRouteName(),
            'url' => $request->url(),
            'path' => $request->path(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user' => $request->user() ? $request->user()->id : 'guest'
        ]);

        return $next($request);
    }
}
