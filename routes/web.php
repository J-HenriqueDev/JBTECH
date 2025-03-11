<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\language\LanguageController;
use App\Http\Controllers\pages\HomePage;
use App\Http\Controllers\pages\Page2;
use App\Http\Controllers\pages\MiscError;
use App\Http\Controllers\pages\Landing;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ClientesController;
use App\Http\Controllers\OSController;
use App\Http\Controllers\ProdutosController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\OrcamentoController;
use App\Http\Controllers\VendaController;
use App\Http\Controllers\CobrancaController;
use App\Http\Controllers\PagSeguroController;

// Rota principal "/" - acessível sem autenticação
Route::get('/', [Landing::class, 'index'])->name('front-pages-landing');

// Middleware para rotas protegidas
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    // Main Page Route
    Route::get('/page-2', [Page2::class, 'index'])->name('pages-page-2');
    Route::get('/dashboard', [HomePage::class, 'index'])->name('dashboard');

    // Routes protegidas
    Route::get('/get-reviews', [ReviewController::class, 'getReviews']);
    Route::get('dashboard/clientes/search', [ClientesController::class, 'search'])->name('dashboard.clientes.search');
    Route::get('dashboard/os/search', [OSController::class, 'search'])->name('dashboard.os.search');

    Route::resource('dashboard/clientes', ClientesController::class);
    Route::resource('dashboard/os', OSController::class);

    // Route para produtos
    Route::resource('dashboard/produtos', ProdutosController::class)->except('show');

    // Rota resource para cobranças
    Route::resource('dashboard/cobrancas', CobrancaController::class);


    Route::get('/produtos/lista', [ProdutosController::class, 'listar'])->name('produtos.lista');

    // Route::post('dashboard/produtos/import', [ProdutosController::class, 'import'])->name('produtos.import');
    // // Rota para importar produtos do XML

    Route::get('dashboard/produtos/importar', [ProdutosController::class, 'importarView'])->name('produtos.importar');
    Route::post('dashboard/produtos/importar', [ProdutosController::class, 'import'])->name('produtos.import');



    // Route::post('dashboard/vendas', [VendaController::class,'create'])->name('vendas.create');
    // Route::get('dashboard/vendas', [VendaController::class,'create'])->name('vendas');
    // Route::post('/vendas/{id}/gerar-cobranca', [VendaController::class, 'gerarCobranca'])->name('vendas.gerarCobranca');
    Route::post('/vendas/{id}/gerar-cobranca', [VendaController::class, 'gerarCobranca'])->name('vendas.gerarCobranca');
    Route::get('/dashboard/vendas/{id}/pdf', [VendaController::class, 'exportarPdf'])->name('vendas.exportarPdf');
    Route::resource('/dashboard/vendas', VendaController::class);

    Route::post('/pagseguro/notification', [PagSeguroController::class, 'notification'])->name('pagseguro.notification');



// Rota para processar o upload do XML

    Route::resource('dashboard/categorias', CategoriaController::class)->only([
        'index', 'store', 'destroy'
    ]);

    Route::resource('dashboard/orcamentos', OrcamentoController::class);
    Route::get('/dashboard/orcamentos/{id}/pdf', [OrcamentoController::class, 'gerarPdf'])->name('orcamentos.gerarPdf');
    Route::get('dashboard/orcamentos/search', [OrcamentoController::class, 'search'])->name('orcamentos.search');
    Route::post('/dashboard/orcamentos/obter-coordenadas', [OrcamentoController::class, 'obterCoordenadas'])->name('orcamentos.obterCoordenadas');
    Route::get('/dashboard/orcamentos/{id}/exportar-pdf', [OrcamentoController::class, 'exportarPdf'])->name('orcamentos.exportarPdf');
    Route::post('/dashboard/orcamentos/{id}/autorizar', [OrcamentoController::class, 'autorizar'])->name('orcamentos.autorizar');
    Route::post('/dashboard/orcamentos/{id}/recusar', [OrcamentoController::class, 'recusar'])->name('orcamentos.recusar');
    Route::get('/orcamentos/{id}/verificar-estoque', [OrcamentoController::class, 'verificarEstoque'])->name('orcamentos.verificarEstoque');

    // Rota para erro misc
    Route::post('/pages/misc-error', [MiscError::class, 'index'])->name('pages-misc-error');
});

// Rota para trocar idioma - fora do middleware auth
Route::get('/lang/{locale}', [LanguageController::class, 'swap']);
