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
use App\Http\Controllers\LogController;
use App\Http\Controllers\NFeController;
use App\Http\Controllers\ConfiguracaoController;
use App\Http\Controllers\RelatorioController;


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
    Route::get('/dashboard/os/{id}/pdf', [OSController::class, 'gerarPdf'])->name('os.pdf');

    // Route para produtos
    Route::resource('dashboard/produtos', ProdutosController::class)->except('show');
    Route::post('dashboard/produtos/{id}/update-inline', [ProdutosController::class, 'updateInline'])->name('produtos.updateInline');

    // Rota resource para cobranças
    Route::resource('dashboard/cobrancas', CobrancaController::class);
    Route::get('/dashboard/cobrancas/{id}/pdf', [CobrancaController::class, 'pdf'])->name('cobrancas.pdf');
    Route::post('/dashboard/cobrancas/{id}/marcar-paga', [CobrancaController::class, 'marcarComoPaga'])->name('cobrancas.marcar-paga');
    Route::post('/dashboard/cobrancas/{id}/cancelar', [CobrancaController::class, 'cancelar'])->name('cobrancas.cancelar');

    Route::get('/logs', [LogController::class, 'index'])->name('logs.index');

    // Rotas para Configurações
    Route::get('/dashboard/configuracoes', [ConfiguracaoController::class, 'index'])->name('configuracoes.index');
    Route::post('/dashboard/configuracoes', [ConfiguracaoController::class, 'store'])->name('configuracoes.store');
    Route::post('/dashboard/configuracoes/testar-certificado', [ConfiguracaoController::class, 'testarCertificado'])->name('configuracoes.testarCertificado');

    // Rotas para NF-e
    Route::get('/dashboard/nfe/{id}/consultar-status', [NFeController::class, 'consultarStatus'])->name('nfe.consultarStatus');
    Route::get('/dashboard/nfe/{id}/download-xml', [NFeController::class, 'downloadXml'])->name('nfe.downloadXml');
    Route::get('/dashboard/nfe/{id}/download-xml-cancelamento', [NFeController::class, 'downloadXmlCancelamento'])->name('nfe.downloadXmlCancelamento');
    Route::post('/dashboard/nfe/{id}/cancelar', [NFeController::class, 'cancelar'])->name('nfe.cancelar');
    Route::resource('/dashboard/nfe', NFeController::class)->names([
        'index' => 'nfe.index',
        'create' => 'nfe.create',
        'store' => 'nfe.store',
        'show' => 'nfe.show',
    ]);


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
        'index', 'store', 'update', 'destroy'
    ]);

    Route::resource('dashboard/orcamentos', OrcamentoController::class);

    // Rotas para Relatórios
    Route::get('/dashboard/relatorios', [RelatorioController::class, 'index'])->name('relatorios.index');
    Route::get('/dashboard/relatorios/vendas', [RelatorioController::class, 'vendas'])->name('relatorios.vendas');
    Route::get('/dashboard/relatorios/produtos', [RelatorioController::class, 'produtos'])->name('relatorios.produtos');
    Route::get('/dashboard/relatorios/clientes', [RelatorioController::class, 'clientes'])->name('relatorios.clientes');
    Route::get('/dashboard/relatorios/financeiro', [RelatorioController::class, 'financeiro'])->name('relatorios.financeiro');
    Route::get('/dashboard/relatorios/estoque', [RelatorioController::class, 'estoque'])->name('relatorios.estoque');
    Route::get('/dashboard/relatorios/movimentacoes', [RelatorioController::class, 'movimentacoes'])->name('relatorios.movimentacoes');
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
