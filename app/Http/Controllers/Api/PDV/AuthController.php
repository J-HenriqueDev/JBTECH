<?php

namespace App\Http\Controllers\Api\PDV;

use App\Http\Controllers\Controller;
use App\Models\Operador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validated = $request->validate([
            'operador' => 'required|string',
            'senha' => 'required|string',
        ]);

        $operador = Operador::where('codigo', $validated['operador'])
            ->where('ativo', true)
            ->first();

        if (!$operador || !$operador->verificarSenha($validated['senha'])) {
            return response()->json([
                'success' => false,
                'message' => 'Operador ou senha inválidos'
            ], 401);
        }

        // Cria token usando Sanctum diretamente no operador
        $token = $operador->createToken('pdv-operador-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'operador' => [
                    'id' => $operador->id,
                    'codigo' => $operador->codigo,
                    'nome' => $operador->nome,
                ],
                'token' => $token,
            ]
        ]);
    }
}
