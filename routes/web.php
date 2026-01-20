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
use App\Http\Controllers\FornecedorController;
use App\Http\Controllers\ContaPagarController;
use App\Http\Controllers\CompraController;


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
  Route::resource('dashboard/fornecedores', FornecedorController::class);
  Route::post('/dashboard/contas-pagar/{id}/marcar-paga', [ContaPagarController::class, 'marcarComoPaga'])->name('contas-pagar.marcar-paga');
  Route::post('/dashboard/contas-pagar/bulk-pay', [ContaPagarController::class, 'bulkPay'])->name('contas-pagar.bulk-pay');
  Route::get('/dashboard/contas-pagar/exportar', [ContaPagarController::class, 'exportarRelatorio'])->name('contas-pagar.exportar');
  Route::resource('dashboard/contas-pagar', ContaPagarController::class);
  Route::resource('dashboard/compras', CompraController::class);
  Route::put('/dashboard/compras/items/{id}/status', [CompraController::class, 'updateItemStatus'])->name('compras.updateItemStatus');
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

  Route::resource('dashboard/users', UserController::class)->names([
    'index' => 'users.index',
    'create' => 'users.create',
    'store' => 'users.store',
    'show' => 'users.show',
    'edit' => 'users.edit',
    'update' => 'users.update',
    'destroy' => 'users.destroy',
  ]);

  // Rotas para Configurações
  Route::get('/dashboard/configuracoes', [ConfiguracaoController::class, 'index'])->name('configuracoes.index');
  Route::post('/dashboard/configuracoes', [ConfiguracaoController::class, 'store'])->name('configuracoes.store');
  Route::get('/dashboard/util/consulta-cnpj/{cnpj}', [ConfiguracaoController::class, 'consultaCnpj'])->name('util.consulta-cnpj');

  // Rotas para Natureza de Operação
  Route::resource('dashboard/naturezas', \App\Http\Controllers\NaturezaOperacaoController::class);

  // Rotas para NF-e
  // Rotas para Manifesto (Novo) - DEVE vir antes do resource nfe
  Route::get('/dashboard/nfe/manifesto', [\App\Http\Controllers\ManifestoController::class, 'index'])->name('nfe.manifesto.index');
  Route::post('/dashboard/nfe/manifesto/manifestar', [\App\Http\Controllers\ManifestoController::class, 'manifestar'])->name('nfe.manifesto.manifestar');
  Route::post('/dashboard/nfe/manifesto/sincronizar', [\App\Http\Controllers\ManifestoController::class, 'sincronizar'])->name('nfe.manifesto.sincronizar');
  Route::get('/dashboard/nfe/manifesto/baixar-xml/{id}', [\App\Http\Controllers\ManifestoController::class, 'baixarXml'])->name('nfe.manifesto.baixarXml');

  // Rotas para NFe Avulsa (Antes do resource)
  Route::get('/dashboard/nfe/avulsa', [NFeController::class, 'createAvulsa'])->name('nfe.create-avulsa');
  Route::post('/dashboard/nfe/avulsa', [NFeController::class, 'storeAvulsa'])->name('nfe.store-avulsa');

  Route::get('/dashboard/nfe/config', [\App\Http\Controllers\NFeConfigController::class, 'index'])->name('nfe.config');
  Route::post('/dashboard/nfe/config', [\App\Http\Controllers\NFeConfigController::class, 'store'])->name('nfe.config.store');
  Route::post('/dashboard/nfe/testar-certificado', [\App\Http\Controllers\NFeConfigController::class, 'testarCertificado'])->name('nfe.testarCertificado');
  Route::get('/dashboard/nfe/{id}/consultar-status', [NFeController::class, 'consultarStatus'])->name('nfe.consultarStatus');
  Route::get('/dashboard/nfe/{id}/download-xml', [NFeController::class, 'downloadXml'])->name('nfe.downloadXml');
  Route::get('/dashboard/nfe/{id}/view-xml', [NFeController::class, 'viewXml'])->name('nfe.viewXml');
  Route::get('/dashboard/nfe/{id}/download-xml-cancelamento', [NFeController::class, 'downloadXmlCancelamento'])->name('nfe.downloadXmlCancelamento');
  Route::post('/dashboard/nfe/{id}/cancelar', [NFeController::class, 'cancelar'])->name('nfe.cancelar');
  Route::post('/dashboard/nfe/{id}/carta-correcao', [NFeController::class, 'cartaCorrecao'])->name('nfe.cartaCorrecao');
  Route::post('/dashboard/nfe/inutilizar', [NFeController::class, 'inutilizar'])->name('nfe.inutilizar');
  Route::post('/dashboard/nfe/{id}/enviar-email', [NFeController::class, 'enviarEmail'])->name('nfe.enviarEmail');
  Route::post('/dashboard/nfe/{id}/transmitir', [NFeController::class, 'transmitir'])->name('nfe.transmitir');
  Route::get('/dashboard/nfe/{id}/danfe', [NFeController::class, 'gerarDanfe'])->name('nfe.gerarDanfe');
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
  Route::post('dashboard/produtos/importar/chave', [ProdutosController::class, 'importarNFeChave'])->name('produtos.importar.chave');
  Route::get('/dashboard/produtos/consultar-fiscal/{codigo_barras}', [ProdutosController::class, 'consultarFiscal'])->name('produtos.consultarFiscal');
  Route::post('/dashboard/produtos/buscar-codigo', [ProdutosController::class, 'buscarPorCodigoBarras'])->name('produtos.buscar-codigo');
  Route::get('/dashboard/produtos/sugerir-categoria', [ProdutosController::class, 'sugerirCategoria'])->name('produtos.sugerirCategoria');



  // Route::post('dashboard/vendas', [VendaController::class,'create'])->name('vendas.create');
  // Route::get('dashboard/vendas', [VendaController::class,'create'])->name('vendas');
  // Route::post('/vendas/{id}/gerar-cobranca', [VendaController::class, 'gerarCobranca'])->name('vendas.gerarCobranca');
  Route::post('/vendas/{id}/gerar-cobranca', [VendaController::class, 'gerarCobranca'])->name('vendas.gerarCobranca');
  Route::get('/dashboard/vendas/{id}/pdf', [VendaController::class, 'exportarPdf'])->name('vendas.exportarPdf');
  Route::resource('/dashboard/vendas', VendaController::class);

  Route::post('/pagseguro/notification', [PagSeguroController::class, 'notification'])->name('pagseguro.notification');



  // Rota para processar o upload do XML

  Route::resource('dashboard/categorias', CategoriaController::class)->only([
    'index',
    'store',
    'update',
    'destroy'
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

  // Rotas de Gerenciamento de Usuários
  Route::resource('dashboard/users', \App\Http\Controllers\UserController::class);

  // Rotas para Importação de Notas (Entrada)
  Route::get('/dashboard/notas-entrada', [\App\Http\Controllers\NotaEntradaController::class, 'index'])->name('notas-entrada.index');
  Route::post('/dashboard/notas-entrada/buscar', [\App\Http\Controllers\NotaEntradaController::class, 'buscarNovas'])->name('notas-entrada.buscar');
  Route::post('/dashboard/notas-entrada/baixar-por-chave', [\App\Http\Controllers\NotaEntradaController::class, 'baixarPorChave'])->name('notas-entrada.baixar-por-chave');
  Route::post('/dashboard/notas-entrada/upload-xml', [\App\Http\Controllers\NotaEntradaController::class, 'uploadXml'])->name('notas-entrada.upload-xml');
  Route::get('/dashboard/notas-entrada/{id}/processar', [\App\Http\Controllers\NotaEntradaController::class, 'processar'])->name('notas-entrada.processar');
  Route::post('/dashboard/notas-entrada/{id}/confirmar', [\App\Http\Controllers\NotaEntradaController::class, 'confirmarProcessamento'])->name('notas-entrada.confirmar');

  // Rotas para erro misc
  Route::post('/pages/misc-error', [MiscError::class, 'index'])->name('pages-misc-error');
});

// Rota para trocar idioma - fora do middleware auth
Route::get('/lang/{locale}', [LanguageController::class, 'swap']);
