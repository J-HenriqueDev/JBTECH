<?php

namespace App\Http\Middleware;

use App\Services\LogService;
use Closure;
use Illuminate\Http\Request;

class RegistrarLog
{
    public function handle(Request $request, Closure $next, $categoria, $acao)
    {
        // Processa a requisição primeiro
        $response = $next($request);

        // Verifica se a requisição foi bem-sucedida (status 2xx)
        if ($response->isSuccessful()) {
            // Registra o log apenas para métodos POST, PUT, PATCH, DELETE
            if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
                // Obtém detalhes dinâmicos da requisição (se houver)
                $detalhes = $request->input('log_detalhes');

                // Registra o log
                LogService::registrar($categoria, $acao, $detalhes);
            }
        }

        return $response;
    }
}
