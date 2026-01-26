<?php

namespace App\Services;

use App\Models\Log;
use Illuminate\Support\Facades\Auth;

class LogService
{
    /**
     * Registra uma mudança feita por um humano.
     */
    public static function registrarMudanca($model, $id, $campo, $antigo, $novo)
    {
        $user = Auth::user() ? Auth::user()->name : 'Desconhecido';
        $acao = "Editou {$model} {$id}";
        $detalhes = "Campos: {$campo} de '{$antigo}' para '{$novo}'";
        
        self::registrar('Humano', $acao, $detalhes);
    }

    /**
     * Registra uma ação automática do sistema (ex: SEFAZ).
     */
    public static function registrarSistema($categoria, $acao, $detalhes = null)
    {
        // Logs de sistema geralmente não têm usuário logado, mas usamos um identificador padrão
        try {
            Log::create([
                'user_id' => null, // Sistema
                'categoria' => $categoria, // ex: 'SEFAZ'
                'acao' => $acao,
                'detalhes' => $detalhes,
                'ip' => '127.0.0.1',
                'user_agent' => 'System/CLI',
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Falha ao registrar log de sistema: ' . $e->getMessage());
        }
    }

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
        try {
            Log::create([
                'user_id' => Auth::id(), // ID do usuário logado
                'categoria' => $categoria,
                'acao' => $acao,
                'detalhes' => $detalhes,
                'ip' => request()->ip(), // IP do usuário
                'user_agent' => request()->userAgent(), // Navegador do usuário
            ]);
        } catch (\Exception $e) {
            // Silently fail to avoid breaking the application if logging fails
            // But try to log to system log if possible
            \Illuminate\Support\Facades\Log::error('Falha ao registrar log no banco de dados: ' . $e->getMessage());
        }
    }
}
