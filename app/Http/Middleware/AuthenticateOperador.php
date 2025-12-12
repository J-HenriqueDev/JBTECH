<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateOperador
{
    public function handle(Request $request, Closure $next): Response
    {
        // Verifica se há um token Sanctum válido
        if ($request->user()) {
            return $next($request);
        }

        // Tenta autenticar via token do operador
        $user = $request->user('sanctum');
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Não autenticado'
            ], 401);
        }

        return $next($request);
    }
}


