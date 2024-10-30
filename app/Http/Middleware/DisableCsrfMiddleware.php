<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DisableCsrfMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Verifica se o request é do tipo POST para desativar a proteção CSRF
        if ($request->isMethod('post')) {
            // Desativa a verificação CSRF
            return $next($request);
        }

        return $next($request);
    }
}
