<?php

namespace App\Helpers;

use App\Models\Operador;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class PDVHelper
{
    /**
     * Obtém o operador autenticado a partir do token
     */
    public static function getOperadorAutenticado(Request $request): ?Operador
    {
        $bearerToken = $request->bearerToken();
        
        if (!$bearerToken) {
            return null;
        }

        // Busca o token no banco
        $token = PersonalAccessToken::findToken($bearerToken);
        
        if (!$token) {
            return null;
        }

        // Verifica se o token pertence a um operador
        $tokenable = $token->tokenable;
        
        if ($tokenable instanceof Operador) {
            return $tokenable;
        }

        return null;
    }

    /**
     * Obtém o ID do operador autenticado
     */
    public static function getOperadorId(Request $request): ?int
    {
        $operador = self::getOperadorAutenticado($request);
        return $operador ? $operador->id : null;
    }
}


