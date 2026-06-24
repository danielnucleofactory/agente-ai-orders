<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AgentTokenAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $token         = $request->header('X-Agent-Token');
        $expectedToken = config('services.agent.api_token');

        if (!$token || !$expectedToken || !hash_equals($expectedToken, $token)) {
            return response()->json(['error' => 'No autorizado.'], 401);
        }

        return $next($request);
    }
}