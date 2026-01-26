<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\Categoria;
use App\Models\Fornecedor;
use App\Models\ProdutoCodigo;
use App\Models\Configuracao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\LogService;

class ProdutosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $produtos = Produto::with('categoria')->orderBy('id', 'desc')->get(); // Obtém todos os produtos com categoria, ordenados por ID decrescente
        $categorias = Categoria::all(); // Obtém todas as categorias

        // Busca a configuração do usuário autenticado
        $edicaoInline = \App\Models\Configuracao::get('produtos_edicao_inline', '0') == '1';

        // Registra um log
        LogService::registrar(
            'Produto', // Categoria
            'Listar', // Ação
            'Listou todos os produtos' // Detalhes
        );

        return view('content.produtos.listar', compact('produtos', 'categorias', 'edicaoInline'));
    }

    /**
     * Sugere uma categoria com base no nome do produto
     */
    public function sugerirCategoria(Request $request)
    {
        $nomeProduto = $request->input('nome');
        if (!$nomeProduto) {
            return response()->json(['success' => false, 'categoria_id' => null]);
        }

        // Normaliza o nome do produto (lowercase)
        $nomeProduto = mb_strtolower($nomeProduto);
        $palavrasProduto = explode(' ', $nomeProduto);

        $melhorCategoriaId = null;
        $maiorPontuacao = 0;

        $categorias = Categoria::all();

        foreach ($categorias as $categoria) {
            $pontuacao = 0;
            $nomeCategoria = mb_strtolower($categoria->nome);

            // Verifica se o nome da categoria está contido no nome do produto
            if (str_contains($nomeProduto, $nomeCategoria)) {
                $pontuacao += 10;
            }

            // Verifica palavras-chave
            if ($categoria->palavras_chave) {
                $palavrasChave = explode(',', mb_strtolower($categoria->palavras_chave));
                foreach ($palavrasChave as $chave) {
                    $chave = trim($chave);
                    if ($chave && str_contains($nomeProduto, $chave)) {
                        $pontuacao += 5;
                    }
                }
            }

            if ($pontuacao > $maiorPontuacao) {
                $maiorPontuacao = $pontuacao;
                $melhorCategoriaId = $categoria->id;
            }
        }

        return response()->json([
            'success' => true,
            'categoria_id' => $melhorCategoriaId
        ]);
    }

    /**
     * Dispara a categorização massiva de produtos em background.
     */
    public function categorizarMassivo()
    {
        // Dispara o comando em background (queue)
        \Illuminate\Support\Facades\Artisan::queue('products:categorize');

        return redirect()->back()->with('success', 'Processo de categorização automática (IA) iniciado em segundo plano.');
    }

    /**
     * Dispara o preenchimento massivo de dados fiscais em background.
     */
    public function preencherFiscalMassivo()
    {
        // Dispara o comando em background (queue)
        \Illuminate\Support\Facades\Artisan::queue('products:fill-fiscal');

        return redirect()->back()->with('success', 'Processo de preenchimento fiscal (IA) iniciado em segundo plano.');
    }

    /**
     * Executa a categorização em lote via comando Artisan.
     */
    public function categorizarLote()
    {
        try {
            // Dispara o comando Artisan diretamente (Síncrono) para feedback imediato
            // Usuários preferem esperar e ver acontecer do que "não acontecer nada"
            // Força a recategorização (--force) conforme solicitação do usuário para corrigir tudo
            \Illuminate\Support\Facades\Artisan::call('products:categorize', ['--force' => true]);
            $output = \Illuminate\Support\Facades\Artisan::output();

            // Grava o output no log de console
            $logPath = storage_path('logs/console-output.log');
            $logEntry = "\n--- Categorização Manual em Lote (FORCE): " . date('Y-m-d H:i:s') . " ---\n" . $output . "\n";
            file_put_contents($logPath, $logEntry, FILE_APPEND);

            LogService::registrar('Produto', 'Categorização em Lote', 'Usuário solicitou categorização manual em lote (FORCE).');

            // Remove ANSI codes and extra whitespace
            $cleanOutput = preg_replace('/\x1b\[[0-9;]*m/', '', $output);

            Log::info("Categorização Output: " . $cleanOutput);

            if (str_contains($cleanOutput, '0 produtos foram atualizados') || str_contains($cleanOutput, 'Produtos encontrados para análise: 0')) {
                return redirect()->route('produtos.index')->with('warning', 'Nenhum produto precisou de atualização de categoria.');
            }

            return redirect()->route('produtos.index')->with('success', 'Categorização em lote finalizada com sucesso! Verifique o console para detalhes.');
        } catch (\Exception $e) {
            Log::error("Erro ao executar categorização em lote: " . $e->getMessage());
            return redirect()->route('produtos.index')->with('error', 'Erro ao executar categorização: ' . $e->getMessage());
        }
    }

    /**
     * Executa o preenchimento fiscal em lote via comando Artisan.
     */
    public function fiscalLote()
    {
        try {
            // Dispara o comando Artisan diretamente (Síncrono)
            // Força a atualização (--force) para garantir correção de dados incorretos
            $exitCode = \Illuminate\Support\Facades\Artisan::call('products:fill-fiscal', ['--force' => true]);
            $output = \Illuminate\Support\Facades\Artisan::output();

            // Grava o output no log de console
            $logPath = storage_path('logs/console-output.log');
            $logEntry = "\n--- Preenchimento Fiscal Manual em Lote (FORCE): " . date('Y-m-d H:i:s') . " ---\n" . $output . "\n";
            file_put_contents($logPath, $logEntry, FILE_APPEND);

            LogService::registrar('Produto', 'Fiscal em Lote', 'Usuário solicitou preenchimento fiscal manual em lote (FORCE).');

            if (str_contains($output, 'Nenhum produto precisou')) {
                return redirect()->route('produtos.index')->with('warning', 'Nenhum produto precisou de atualização fiscal.');
            }

            return redirect()->route('produtos.index')->with('success', 'Processo de preenchimento fiscal finalizado. Verifique os logs do console para detalhes.');
        } catch (\Exception $e) {
            Log::error("Erro ao executar preenchimento fiscal em lote: " . $e->getMessage());
            return redirect()->route('produtos.index')->with('error', 'Erro ao executar preenchimento fiscal: ' . $e->getMessage());
        }
    }

    /**
     * Consulta dados fiscais de um produto via API externa (Service).
     */
    public function consultarFiscal($codigoBarras, \App\Services\FiscalService $fiscalService)
    {
        try {
            $dados = $fiscalService->consultarPorCodigoBarras($codigoBarras);

            if ($dados) {
                // Registra um log
                LogService::registrar(
                    'Produto',
                    'Consultar Fiscal',
                    "Consulta fiscal realizada para o código: {$codigoBarras}"
                );

                return response()->json([
                    'success' => true,
                    'data' => $dados
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Produto não encontrado na base fiscal.'
                ], 404);
            }
        } catch (\Exception $e) {
            Log::error("Erro na consulta fiscal: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao consultar dados fiscais: ' . $e->getMessage()
            ], 500);
        }
    }

    public function listar(Request $request)
    {
        // Tente obter os produtos do banco
        try {
            $query = Produto::query();

            // Filtro de busca (para Select2)
            if ($request->has('q')) {
                $termo = $request->q;
                $query->where(function ($q) use ($termo) {
                    $q->where('nome', 'LIKE', "%{$termo}%")
                        ->orWhere('codigo_barras', 'LIKE', "%{$termo}%")
                        ->orWhere('id', 'LIKE', "%{$termo}%");
                });
            }

            // Limita os resultados para evitar sobrecarga
            $produtos = $query->limit(50)->get();

            if ($produtos->isEmpty()) {
                // Retorna array vazio em vez de erro 404 para o Select2 funcionar corretamente
                return response()->json([]);
            }

            // Formata os produtos para incluir estoque
            $produtosFormatados = $produtos->map(function ($produto) {
                return [
                    'id' => $produto->id,
                    'text' => $produto->nome . ' - (Cód: ' . ($produto->codigo_barras ?? $produto->id) . ')', // Formato para Select2
                    'nome' => $produto->nome,
                    'preco_venda' => $produto->preco_venda,
                    'preco_custo' => $produto->preco_custo, // Adicionado custo
                    'estoque' => $produto->estoque ?? 0,
                    'codigo_barras' => $produto->codigo_barras
                ];
            });

            // Se for requisição AJAX do Select2, retorna no formato esperado ou array simples
            return response()->json($produtosFormatados);
        } catch (\Exception $e) {
            // Registra um log de erro
            LogService::registrar(
                'Produto', // Categoria
                'Erro', // Ação
                "Erro ao buscar produtos: {$e->getMessage()}" // Detalhes
            );

            return response()->json(['error' => 'Erro ao buscar produtos: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $categorias = Categoria::all(); // Obtém todas as categorias
        $fornecedores = Fornecedor::all(); // Obtém todos os fornecedores

        // Registra um log
        LogService::registrar(
            'Produto', // Categoria
            'Criar', // Ação
            'Acessou o formulário de criação de produto' // Detalhes
        );

        return view('content.produtos.criar', compact('categorias', 'fornecedores')); // Retorna a view de criação
    }

    /**
     * View para importar e editar dados de XML.
     */
    public function importarView(Request $request)
    {
        $categorias = Categoria::all();
        $productsData = []; // Definir como array vazio para evitar erro na view

        // Registra um log
        LogService::registrar(
            'Produto', // Categoria
            'Importar', // Ação
            'Acessou a página de importação de produtos' // Detalhes
        );

        return view('content.produtos.importar', compact('categorias', 'productsData'));
    }

    /**
     * Importa produtos de um arquivo XML e carrega os dados na view de importação.
     */
    public function import(Request $request)
    {
        $categorias = Categoria::all();

        // Validação do arquivo de upload
        $request->validate([
            'xml_file' => 'required|file|mimes:xml',
        ]);

        try {
            $xmlContent = file_get_contents($request->file('xml_file')->getRealPath());
            $xml = simplexml_load_string($xmlContent);
        } catch (\Exception $e) {
            Log::error('Erro ao ler o arquivo XML: ' . $e->getMessage());

            // Registra um log de erro
            LogService::registrar(
                'Produto', // Categoria
                'Erro', // Ação
                "Erro ao ler o arquivo XML: {$e->getMessage()}" // Detalhes
            );

            return redirect()->route('produtos.importar')->withErrors('Erro ao ler o arquivo XML.');
        }

        return $this->processarImportacao($xml, $categorias);
    }

    /**
     * Importa produtos via Chave de Acesso (download da SEFAZ).
     */
    public function importarNFeChave(Request $request, \App\Services\NFeService $nfeService)
    {
        $request->validate([
            'chave_acesso' => 'required|string|size:44',
        ]);

        $categorias = Categoria::all();

        try {
            // Tenta baixar o XML da SEFAZ
            $xmlContent = $nfeService->downloadPorChave($request->chave_acesso);
            $xml = simplexml_load_string($xmlContent);

            // Registra um log
            LogService::registrar(
                'Produto',
                'Importar',
                "Download de NFe via chave realizado: {$request->chave_acesso}"
            );

            return $this->processarImportacao($xml, $categorias);
        } catch (\Exception $e) {
            Log::error('Erro ao importar por chave: ' . $e->getMessage());

            return redirect()->route('produtos.importar')
                ->withErrors('Erro ao buscar NFe na SEFAZ: ' . $e->getMessage() . ' Tente baixar o XML manualmente e importar.');
        }
    }

    /**
     * Processa o objeto XML e retorna a view
     */
    private function processarImportacao($xml, $categorias)
    {
        $productsData = [];
        $fornecedor = [];

        // Verifica se o XML tem a estrutura esperada
        if (!isset($xml->NFe->infNFe->det)) {
            // Tenta verificar se é um procNFe (XML distribuído)
            if (isset($xml->protNFe) && isset($xml->NFe)) {
                // Estrutura procNFe, ajusta o ponteiro se necessário, mas geralmente simplexml acessa direto
                // Se for procNFe, o infNFe está dentro de NFe
            } elseif (!isset($xml->infNFe->det)) {
                Log::warning('Estrutura do XML inválida.');
                return redirect()->route('produtos.importar')->withErrors('Estrutura do XML inválida.');
            }
        }

        // Ajuste para pegar infNFe corretamente dependendo da raiz
        $infNFe = isset($xml->NFe->infNFe) ? $xml->NFe->infNFe : $xml->infNFe;

        // Extrai os dados do fornecedor do XML
        if (isset($infNFe->emit)) {
            $fornecedor = [
                'cnpj' => (string) $infNFe->emit->CNPJ,
                'nome' => (string) $infNFe->emit->xNome,
                'telefone' => (string) $infNFe->emit->enderEmit->fone,
                'email' => '', // O email do fornecedor geralmente não está no XML
            ];
        }

        // Percorre os produtos dentro do XML
        foreach ($infNFe->det as $produto) {
            $productsData[] = [
                'nome' => (string) $produto->prod->xProd,
                'preco_custo' => (float) $produto->prod->vProd, // Valor original do XML
                'preco_venda' => (float) $produto->prod->vProd, // Valor original do XML (sem margem de lucro)
                'codigo_barras' => (string) $produto->prod->cEAN,
                'ncm' => (string) $produto->prod->NCM,
                'estoque' => (int) $produto->prod->qCom,
                'categoria_id' => null, // Categoria não está no XML, será selecionada manualmente
            ];

            // Tenta pré-identificar produto existente para facilitar a vida do usuário
            $match = Produto::where('codigo_barras', $produto->prod->cEAN)->first();
            if (!$match) {
                $match = Produto::where('nome', $produto->prod->xProd)->first();
            }

            if ($match) {
                $productsData[count($productsData) - 1]['id'] = $match->id;
                $productsData[count($productsData) - 1]['match_type'] = 'existente';
            } else {
                $productsData[count($productsData) - 1]['match_type'] = 'novo';
            }
        }

        Log::info('Produtos extraídos do XML:', ['productsData' => $productsData]);

        // Registra um log
        LogService::registrar(
            'Produto', // Categoria
            'Importar', // Ação
            'Produtos extraídos do XML com sucesso' // Detalhes
        );

        // Redireciona para a view de importação com os dados extraídos
        return view('content.produtos.importar', compact('productsData', 'categorias', 'fornecedor'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'fornecedor_cnpj' => 'nullable|string|max:20',
            'fornecedor_nome' => 'nullable|string|max:255',
            'fornecedor_telefone' => 'nullable|string|max:15',
            'fornecedor_email' => 'nullable|email',
        ]);

        $produtos = $request->input('produtos');

        // Verifique se $produtos é um array
        if (!is_array($produtos)) {
            // Registra um log de erro
            LogService::registrar(
                'Produto', // Categoria
                'Erro', // Ação
                'Formato de dados inválido. Esperado um array de produtos.' // Detalhes
            );

            return redirect()->back()->withErrors('Formato de dados inválido. Esperado um array de produtos.');
        }

        foreach ($produtos as $produto) {
            // Verifique se $produto é um array
            if (!is_array($produto)) {
                continue; // Pula para o próximo item se não for um array
            }

            // Define o valor padrão do estoque como 1 se estiver vazio ou null
            if (!isset($produto['estoque']) || $produto['estoque'] === '' || $produto['estoque'] === null) {
                $produto['estoque'] = 1;
            }

            if (!isset($produto['unidade_comercial']) || $produto['unidade_comercial'] === '') {
                $produto['unidade_comercial'] = 'UN';
            }

            // Verifique se os campos existem e são strings antes de processar
            if (isset($produto['preco_custo']) && is_string($produto['preco_custo'])) {
                $produto['preco_custo'] = str_replace(',', '.', str_replace('.', '', $produto['preco_custo']));
            } else {
                $produto['preco_custo'] = 0.00; // Valor padrão se não for válido
            }

            if (isset($produto['preco_venda']) && is_string($produto['preco_venda'])) {
                $produto['preco_venda'] = str_replace(',', '.', str_replace('.', '', $produto['preco_venda']));
            } else {
                $produto['preco_venda'] = 0.00; // Valor padrão se não for válido
            }

            // Trata preços avançados
            if (isset($produto['preco_atacado']) && is_string($produto['preco_atacado'])) {
                $produto['preco_atacado'] = str_replace(',', '.', str_replace('.', '', $produto['preco_atacado']));
            }
            if (isset($produto['preco_promocional']) && is_string($produto['preco_promocional'])) {
                $produto['preco_promocional'] = str_replace(',', '.', str_replace('.', '', $produto['preco_promocional']));
            }

            // Define categoria_id como 6 se não for fornecido
            if (!isset($produto['categoria_id']) || empty($produto['categoria_id'])) {
                $produto['categoria_id'] = 6;
            }

            // Adiciona o usuario_id do usuário autenticado
            $produto['usuario_id'] = Auth::user()->id;

            // Garante que o campo fabricante esteja presente, mesmo que vazio
            if (!isset($produto['fabricante'])) {
                $produto['fabricante'] = '';
            }

            // Gera código de barras automaticamente se configurado
            if (Configuracao::get('produtos_gerar_codigo_barras', '1') == '1') {
                if (empty($produto['codigo_barras']) || !isset($produto['codigo_barras'])) {
                    // Gera um código de barras único com no máximo 13 caracteres
                    // Formato: time() (10) + user_id + rand
                    // Cortamos para 13 caracteres para garantir compatibilidade EAN-13
                    $baseCode = time() . Auth::user()->id . rand(100, 999);
                    $produto['codigo_barras'] = substr($baseCode, 0, 13);
                }
            }

            try {
                $validated = $this->validateProduto($produto);

                $produtoExistente = null;

                // 0. Se o ID foi passado explicitamente (vido da importação)
                if (isset($produto['id']) && !empty($produto['id'])) {
                    $produtoExistente = Produto::find($produto['id']);
                }

                // 1. Busca por código de barras (mais preciso)
                if (!$produtoExistente && !empty($validated['codigo_barras'])) {
                    $produtoExistente = Produto::where('codigo_barras', $validated['codigo_barras'])->first();
                }

                // 2. Se não achou, busca por nome
                if (!$produtoExistente) {
                    $produtoExistente = Produto::where('nome', $validated['nome'])->first();
                }

                if ($produtoExistente) {
                    $produtoExistente->update(array_merge($validated, [
                        'fornecedor_cnpj' => $request->fornecedor_cnpj,
                        'fornecedor_nome' => $request->fornecedor_nome,
                        'fornecedor_telefone' => $request->fornecedor_telefone,
                        'fornecedor_email' => $request->fornecedor_email,
                    ]));

                    $produtoSalvo = $produtoExistente;
                } else {
                    $novoProduto = Produto::create(array_merge($validated, [
                        'fornecedor_cnpj' => $request->fornecedor_cnpj,
                        'fornecedor_nome' => $request->fornecedor_nome,
                        'fornecedor_telefone' => $request->fornecedor_telefone,
                        'fornecedor_email' => $request->fornecedor_email,
                        'usuario_id' => Auth::user()->id,
                    ]));

                    // LogService::registrar para criação ainda é útil pois o Loggable (update) não pega Create explicitamente,
                    // a menos que adicionemos created event. O Loggable atual só tem updating e deleted.
                    // Mas o usuário pediu para remover logs MANUAIS de "Atualizou com sucesso".
                    // Vou remover o de Update acima, mas manter o de Create se o Loggable não cobrir.
                    // O Loggable atual NÃO tem 'created'. Vou manter o create por segurança, ou adicionar 'created' no Loggable?
                    // O usuário disse: "A partir de agora, o log automático do Model (via Trait Loggable) é o nosso padrão único para edições."
                    // Edições = Updates. Create é Criação. Vou remover apenas os de Update/Delete duplicados.

                    $produtoSalvo = $novoProduto;
                }

                // Salvar relacionamentos (Fornecedores e Códigos Extras)
                if (isset($produto['fornecedores']) && is_array($produto['fornecedores'])) {
                    $produtoSalvo->fornecedores()->sync($produto['fornecedores']);
                }

                if (isset($produto['codigos_adicionais']) && is_array($produto['codigos_adicionais'])) {
                    // Remove anteriores para evitar duplicação/lixo (estratégia simples de substituição)
                    $produtoSalvo->codigosAdicionais()->delete();

                    foreach ($produto['codigos_adicionais'] as $codigoItem) {
                        if (!empty($codigoItem['codigo'])) {
                            $produtoSalvo->codigosAdicionais()->create([
                                'codigo' => $codigoItem['codigo'],
                                'descricao' => $codigoItem['descricao'] ?? null,
                            ]);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Erro ao salvar o produto: ' . $e->getMessage());

                // Registra um log de erro
                LogService::registrar(
                    'Produto', // Categoria
                    'Erro', // Ação
                    "Erro ao salvar o produto: {$e->getMessage()}" // Detalhes
                );

                return redirect()->back()->withErrors('Erro ao salvar o produto: ' . $e->getMessage());
            }
        }

        // Registra um log
        LogService::registrar(
            'Produto', // Categoria
            'Criar', // Ação
            'Produtos cadastrados com sucesso' // Detalhes
        );

        return redirect()->route('produtos.index')->with('success', 'Produtos cadastrados com sucesso!');
    }

    protected function validateProduto($produto)
    {
        return validator()->make($produto, [
            'nome' => 'required|string|max:255',
            'preco_custo' => 'required|numeric',
            'preco_venda' => 'required|numeric',
            'codigo_barras' => 'nullable|string|max:13',
            'ncm' => 'nullable|string|max:8',
            'estoque' => 'nullable|integer', // Mantido como nullable, pois o valor já foi tratado
            'categoria_id' => 'nullable|exists:categorias,id',
            'fabricante' => 'nullable|string|max:255',
            'usuario_id' => 'required|exists:users,id',
            // Novos campos (Tabs)
            'ativo' => 'boolean',
            'tipo_item' => 'nullable|string|max:2',
            'codigo_servico' => 'nullable|string|max:20',
            'estoque_minimo' => 'nullable|integer',
            'estoque_maximo' => 'nullable|integer',
            'localizacao' => 'nullable|string|max:255',
            'peso_liquido' => 'nullable|numeric',
            'peso_bruto' => 'nullable|numeric',
            'largura' => 'nullable|numeric',
            'altura' => 'nullable|numeric',
            'comprimento' => 'nullable|numeric',
            'observacoes_internas' => 'nullable|string',
            // Campos Fiscais
            'cest' => 'nullable|string|max:7',
            'cfop_interno' => 'nullable|string|max:4',
            'cfop_externo' => 'nullable|string|max:4',
            'unidade_comercial' => 'nullable|string|max:6',
            'unidade_tributavel' => 'nullable|string|max:6',
            'origem' => 'nullable|integer',
            'csosn_icms' => 'nullable|string|max:4',
            'cst_icms' => 'nullable|string|max:3',
            'cst_pis' => 'nullable|string|max:3',
            'cst_cofins' => 'nullable|string|max:3',
            'aliquota_icms' => 'nullable|numeric',
            'aliquota_pis' => 'nullable|numeric',
            'aliquota_cofins' => 'nullable|numeric',
            'perc_icms_fcp' => 'nullable|numeric',
            // Campos de Preços Avançados
            'preco_atacado' => 'nullable|numeric',
            'qtd_min_atacado' => 'nullable|integer',
            'preco_promocional' => 'nullable|numeric',
            'inicio_promocao' => 'nullable|date',
            'fim_promocao' => 'nullable|date',
        ])->validate();
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $produto = Produto::find($id);

        if (!$produto) {
            // Registra um log de aviso
            LogService::registrar(
                'Produto', // Categoria
                'Visualizar', // Ação
                "Produto ID: {$id} não encontrado" // Detalhes
            );

            return response()->json(['error' => 'Produto não encontrado'], 404);
        }

        // Registra um log
        LogService::registrar(
            'Produto', // Categoria
            'Visualizar', // Ação
            "Visualizou o produto ID: {$produto->id}" // Detalhes
        );

        return response()->json($produto);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            // Busca o produto pelo ID
            $produto = Produto::with(['fornecedores', 'codigosAdicionais'])->findOrFail($id);

            // Busca as categorias para o dropdown
            $categorias = Categoria::all();
            $fornecedores = Fornecedor::all();

            // Registra um log
            LogService::registrar(
                'Produto', // Categoria
                'Editar', // Ação
                "Acessou o formulário de edição do produto ID: {$produto->id}" // Detalhes
            );

            // Retorna a view de edição com os dados do produto e categorias
            return view('content.produtos.editar', compact('produto', 'categorias', 'fornecedores'));
        } catch (\Exception $e) {
            Log::error("Erro ao acessar a página de edição do produto ID: {$id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Registra um log de erro
            LogService::registrar(
                'Produto', // Categoria
                'Erro', // Ação
                "Erro ao acessar a página de edição do produto ID: {$id}" // Detalhes
            );

            // Redireciona de volta com uma mensagem de erro
            return redirect()->route('produtos.index')->with('error', 'Erro ao carregar a página de edição.');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Busca o produto pelo ID
            $produto = Produto::findOrFail($id);

            // Valida os dados do formulário
            $request->validate([
                'nome' => 'required|string|max:255',
                'preco_custo' => 'required|string', // Alterado para string
                'preco_venda' => 'required|string', // Alterado para string
                'codigo_barras' => 'nullable|string|max:13',
                'ncm' => 'nullable|string|max:8',
                'estoque' => 'nullable|integer',
                'categoria_id' => 'nullable|exists:categorias,id',
                'fabricante' => 'nullable|string|max:255',
                'fornecedor_cnpj' => 'nullable|string|max:20',
                'fornecedor_nome' => 'nullable|string|max:255',
                'fornecedor_telefone' => 'nullable|string|max:15',
                'fornecedor_email' => 'nullable|email',
                // Campos Fiscais
                'cest' => 'nullable|string|max:7',
                'cfop_interno' => 'nullable|string|max:4',
                'cfop_externo' => 'nullable|string|max:4',
                'unidade_comercial' => 'nullable|string|max:6',
                'unidade_tributavel' => 'nullable|string|max:6',
                'origem' => 'nullable|integer',
                'csosn_icms' => 'nullable|string|max:4',
                'cst_icms' => 'nullable|string|max:3',
                'cst_pis' => 'nullable|string|max:3',
                'cst_cofins' => 'nullable|string|max:3',
                'aliquota_icms' => 'nullable|numeric',
                'aliquota_pis' => 'nullable|numeric',
                'aliquota_cofins' => 'nullable|numeric',
                'perc_icms_fcp' => 'nullable|numeric',
                // Campos de Preços Avançados
                'preco_atacado' => 'nullable|string',
                'qtd_min_atacado' => 'nullable|integer',
                'preco_promocional' => 'nullable|string',
                'inicio_promocao' => 'nullable|date',
                'fim_promocao' => 'nullable|date',
            ]);

            // Converte os valores de preço para o formato numérico
            $preco_custo = str_replace(['.', ','], ['', '.'], $request->preco_custo);
            $preco_venda = str_replace(['.', ','], ['', '.'], $request->preco_venda);

            // Trata preços avançados
            $precoAtacado = $request->preco_atacado ? str_replace(['.', ','], ['', '.'], $request->preco_atacado) : null;
            $precoPromocional = $request->preco_promocional ? str_replace(['.', ','], ['', '.'], $request->preco_promocional) : null;

            // Gera código de barras automaticamente se configurado e não informado
            $codigoBarras = $request->codigo_barras;
            if (Configuracao::get('produtos_gerar_codigo_barras', '1') == '1' && empty($codigoBarras)) {
                $codigoBarras = str_pad(time() . Auth::user()->id . rand(100, 999), 13, '0', STR_PAD_LEFT);
            }

            // Captura dados antigos para log de auditoria
            $oldData = $produto->only(['nome', 'preco_custo', 'preco_venda', 'estoque']);

            // Atualiza os dados do produto
            $produto->update([
                'nome' => $request->nome,
                'preco_custo' => $preco_custo,
                'preco_venda' => $preco_venda,
                'codigo_barras' => $codigoBarras,
                'ncm' => $request->ncm,
                'estoque' => $request->estoque,
                'categoria_id' => $request->categoria_id,
                'fabricante' => $request->fabricante,
                'fornecedor_cnpj' => $request->fornecedor_cnpj,
                'fornecedor_nome' => $request->fornecedor_nome,
                'fornecedor_telefone' => $request->fornecedor_telefone,
                'fornecedor_email' => $request->fornecedor_email,
                'cest' => $request->cest,
                'cfop_interno' => $request->cfop_interno,
                'cfop_externo' => $request->cfop_externo,
                'unidade_comercial' => $request->unidade_comercial,
                'unidade_tributavel' => $request->unidade_tributavel,
                'origem' => $request->origem,
                'csosn_icms' => $request->csosn_icms,
                'cst_icms' => $request->cst_icms,
                'cst_pis' => $request->cst_pis,
                'cst_cofins' => $request->cst_cofins,
                'aliquota_icms' => $request->aliquota_icms,
                'aliquota_pis' => $request->aliquota_pis,
                'aliquota_cofins' => $request->aliquota_cofins,
                'perc_icms_fcp' => $request->perc_icms_fcp,
                // Campos Preços Avançados
                'preco_atacado' => $precoAtacado,
                'qtd_min_atacado' => $request->qtd_min_atacado,
                'preco_promocional' => $precoPromocional,
                'inicio_promocao' => $request->inicio_promocao,
                'fim_promocao' => $request->fim_promocao,
            ]);

            return redirect()->route('produtos.index')->with('success', "Produto #{$produto->id} '{$produto->nome}' atualizado com sucesso!");
        } catch (\Exception $e) {
            Log::error("Erro ao atualizar o produto ID: {$id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Registra um log de erro
            LogService::registrar(
                'Produto', // Categoria
                'Erro', // Ação
                "Erro ao atualizar o produto ID: {$id}" // Detalhes
            );

            return redirect()->back()->with('error', 'Erro ao atualizar o produto. Por favor, tente novamente.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Produto $produto)
    {
        $produto->delete();

        return redirect()->route('produtos.index')->with('success', 'Produto removido com sucesso!');
    }

    /**
     * Atualiza um produto via AJAX (edição inline)
     */
    public function updateInline(Request $request, $id)
    {
        try {
            $produto = Produto::findOrFail($id);

            $request->validate([
                'campo' => 'required|string|in:nome,preco_custo,preco_venda,estoque,categoria_id',
                'valor' => 'required',
            ]);

            $campo = $request->campo;
            $valor = $request->valor;

            // Converte valores numéricos
            if (in_array($campo, ['preco_custo', 'preco_venda'])) {
                $valor = str_replace(['.', ','], ['', '.'], $valor);
                $valor = (float) $valor;
            } elseif ($campo === 'estoque') {
                $valor = (int) $valor;
            } elseif ($campo === 'categoria_id') {
                $valor = (int) $valor;
            }

            $oldValue = $produto->$campo;
            $produto->update([$campo => $valor]);

            // Log automático via Trait Loggable

            return response()->json([
                'success' => true,
                'message' => 'Produto atualizado com sucesso!',
                'produto' => $produto->fresh(['categoria'])
            ]);
        } catch (\Exception $e) {
            Log::error("Erro ao atualizar produto inline ID: {$id}", [
                'error' => $e->getMessage(),
                'campo' => $request->campo,
                'valor' => $request->valor,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar produto: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Busca produto por código de barras ou ID
     */
    public function buscarPorCodigoBarras(Request $request)
    {
        $termo = $request->input('termo');

        if (!$termo) {
            return response()->json(['success' => false, 'message' => 'Termo de busca não informado.'], 400);
        }

        // Tenta buscar por código de barras exato
        $produto = Produto::where('codigo_barras', $termo)->first();

        // Se não encontrar, tenta pelo ID
        if (!$produto && is_numeric($termo)) {
            $produto = Produto::find($termo);
        }

        if ($produto) {
            return response()->json([
                'success' => true,
                'produto' => [
                    'id' => $produto->id,
                    'nome' => $produto->nome,
                    'preco_venda' => $produto->preco_venda,
                    'unidade' => $produto->unidade_comercial,
                    'ncm' => $produto->ncm,
                    'codigo_barras' => $produto->codigo_barras,
                    'cfop' => $produto->cfop_interno ?? '5102' // Fallback
                ]
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Produto não encontrado.'], 404);
    }
}
