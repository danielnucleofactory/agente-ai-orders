<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * La aplicación se ofrece solo en español; evita depender de preferencias de usuario para __() / validaciones.
 */
class ForceSpanishLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        App::setLocale('es');

        return $next($request);
    }
}
