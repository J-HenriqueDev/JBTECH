<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use App\Http\Middleware\LocaleMiddleware;

// Polyfill for SOAP constants if extension is missing
if (!defined('SOAP_1_1')) {
  define('SOAP_1_1', 1);
}
if (!defined('SOAP_1_2')) {
  define('SOAP_1_2', 2);
}

return Application::configure(basePath: dirname(__DIR__))
  ->withRouting(
    web: __DIR__ . '/../routes/web.php',
    api: __DIR__ . '/../routes/api.php',
    commands: __DIR__ . '/../routes/console.php',
    health: '/up',
  )
  ->withMiddleware(function (Middleware $middleware) {
    $middleware->trustProxies(at: '*', headers: Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_HOST | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO | Request::HEADER_X_FORWARDED_AWS_ELB);
    $middleware->web(LocaleMiddleware::class);
    // API não precisa de CSRF
    $middleware->validateCsrfTokens(except: [
      'api/*',
    ]);
  })
  ->withExceptions(function (Exceptions $exceptions) {
    // Para rotas da API, sempre retornar JSON em caso de erro 404
    $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, \Illuminate\Http\Request $request) {
      if ($request->expectsJson() || $request->is('api/*')) {
        return response()->json([
          'success' => false,
          'message' => 'Rota não encontrada',
          'path' => $request->path(),
        ], 404);
      }
    });

    // Para erros de autenticação em rotas da API, retornar JSON
    $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request) {
      if ($request->expectsJson() || $request->is('api/*')) {
        return response()->json([
          'success' => false,
          'message' => 'Não autenticado',
        ], 401);
      }
    });
  })->create();
