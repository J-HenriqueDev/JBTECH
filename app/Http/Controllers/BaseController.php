<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as LaravelController;
use App\Services\LogService;
use Illuminate\Support\Facades\Auth;

class BaseController extends LaravelController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Registra uma atividade no log do sistema.
     * 
     * @param string $acao Ação realizada (ex: 'Criar', 'Editar')
     * @param string $modelo Nome do modelo ou módulo afetado
     * @param array|string $detalhes Detalhes da ação ou array de dados
     */
    protected function logAtividade($acao, $modelo, $detalhes = [])
    {
        $user = Auth::user();
        $nomeUser = $user ? $user->name : 'Sistema';
        $origem = $user ? 'Humano' : 'Sistema';
        
        // Formata detalhes se for array
        if (is_array($detalhes)) {
            $detalhesStr = json_encode($detalhes, JSON_UNESCAPED_UNICODE);
        } else {
            $detalhesStr = $detalhes;
        }

        // Se a ação não tiver o formato "Ação Modelo", montamos uma string descritiva
        $acaoCompleta = "{$acao} {$modelo}";

        // Integração com LogService
        // O LogService espera: registrar($categoria, $acao, $detalhes)
        // Vamos usar $modelo como Categoria (ex: Produto, Venda)
        // E $acao como Ação.
        
        LogService::registrar($modelo, $acao, $detalhesStr);
    }
}
