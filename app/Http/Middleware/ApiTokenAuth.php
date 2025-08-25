<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\PersonalAccessToken;

class ApiTokenAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'error' => 'Token de acceso requerido',
                'message' => 'Debe proporcionar un token de acceso válido en el header Authorization'
            ], 401);
        }

        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            return response()->json([
                'error' => 'Token inválido',
                'message' => 'El token proporcionado no es válido o ha expirado'
            ], 401);
        }

        // Verificar si el token ha expirado
        if ($accessToken->expires_at && $accessToken->expires_at->isPast()) {
            return response()->json([
                'error' => 'Token expirado',
                'message' => 'El token ha expirado'
            ], 401);
        }

        // Actualizar last_used_at
        $accessToken->forceFill(['last_used_at' => now()])->save();

        // Establecer el usuario autenticado
        $request->setUserResolver(function () use ($accessToken) {
            return $accessToken->tokenable;
        });

        return $next($request);
    }
}
