<?php

namespace App\Services;

use App\Models\Log;
use Illuminate\Support\Facades\Auth;

class LogService
{
    /**
     * Registra uma ação no log do sistema.
     *
     * @param string $categoria Categoria da ação (ex: "Orçamento").
     * @param string $acao Ação realizada (ex: "Criar").
     * @param string|null $detalhes Detalhes adicionais (ex: ID do registro).
     * @return void
     */
    public static function registrar($categoria, $acao, $detalhes = null)
    {
        Log::create([
            'user_id' => Auth::id(), // ID do usuário logado
            'categoria' => $categoria,
            'acao' => $acao,
            'detalhes' => $detalhes,
            'ip' => request()->ip(), // IP do usuário
            'user_agent' => request()->userAgent(), // Navegador do usuário
        ]);
    }
}
