<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Services\LogService;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Registra uma ação no log do sistema.
     *
     * @param string $acao Ação realizada (ex: 'Criar', 'Editar')
     * @param string $detalhes Detalhes da ação
     * @param string $categoria Categoria do log (opcional, default: 'Geral')
     */
    protected function logAction($acao, $detalhes, $categoria = 'Geral')
    {
        LogService::registrar($categoria, $acao, $detalhes);
    }
}
