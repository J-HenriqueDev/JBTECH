<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyCsrfToken
{
    // Rotas que devem ser excluídas da verificação CSRF
    protected $except = [
        'dashboard/produtos/import', // Ignora a verificação CSRF para esta rota
    ];

    public function handle(Request $request, Closure $next)
    {
        // Se a rota não está na lista de exceções e é uma requisição POST
        if (!in_array($request->path(), $this->except) && $request->isMethod('post')) {
            // Verifica se o header X-CSRF-TOKEN está presente
            if (!$request->hasHeader('X-CSRF-TOKEN')) {
                return response()->json(['message' => 'CSRF token mismatch.'], 419);
            }
        }

        return $next($request);
    }
}
