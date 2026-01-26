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

    protected function logAtividade($acao, $modelo, $detalhes = [])
    {
        $user = Auth::user();
        $nomeUser = $user ? $user->name : 'Sistema';
        if (is_array($detalhes)) {
            $detalhesStr = json_encode($detalhes, JSON_UNESCAPED_UNICODE);
        } else {
            $detalhesStr = $detalhes;
        }

        LogService::registrar($modelo, $acao, $detalhesStr);
    }

    protected function registrarAcao($categoria, $acao, $detalhes = [])
    {
        if (is_array($detalhes)) {
            $detalhesStr = json_encode($detalhes, JSON_UNESCAPED_UNICODE);
        } else {
            $detalhesStr = $detalhes;
        }

        LogService::registrar($categoria, $acao, $detalhesStr);
    }

    /**
     * Helper padronizado para logs de ação
     */
    protected function logAction($acao, $detalhes = [], $categoria = 'Auditoria')
    {
        $this->registrarAcao($categoria, $acao, $detalhes);
    }
}
