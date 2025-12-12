<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PDV\ProdutoController;
use App\Http\Controllers\Api\PDV\VendaController;
use App\Http\Controllers\Api\PDV\CaixaController;
use App\Http\Controllers\Api\PDV\SincronizacaoController;
use App\Http\Controllers\Api\PDV\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Rotas públicas para autenticação
Route::post('/pdv/login', [AuthController::class, 'login']);

// Rota pública para testar conexão (sem autenticação)
Route::get('/pdv/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'API está online',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// Rotas protegidas do PDV
Route::middleware('auth:sanctum')->prefix('pdv')->group(function () {
    // Produtos
    Route::get('/produtos', [ProdutoController::class, 'index']);
    Route::post('/produtos/sincronizar', [ProdutoController::class, 'sincronizar']);
    Route::put('/produtos/{id}/estoque', [ProdutoController::class, 'atualizarEstoque']);

    // Vendas
    Route::post('/vendas', [VendaController::class, 'store']);
    Route::post('/vendas/sincronizar', [VendaController::class, 'sincronizar']);

    // Caixa
    Route::get('/caixa/status', [CaixaController::class, 'status']);
    Route::post('/caixa/abrir', [CaixaController::class, 'abrir']);
    Route::post('/caixa/{id}/fechar', [CaixaController::class, 'fechar']);
    Route::post('/caixa/sangria', [CaixaController::class, 'sangria']);
    Route::post('/caixa/suprimento', [CaixaController::class, 'suprimento']);
    Route::get('/caixa/{id}', [CaixaController::class, 'show']);

    // Sincronização
    Route::get('/sincronizacao/produtos', [SincronizacaoController::class, 'produtos']);
    Route::post('/sincronizacao/vendas', [SincronizacaoController::class, 'enviarVendas']);
    Route::get('/sincronizacao/status', [SincronizacaoController::class, 'status']);
});
