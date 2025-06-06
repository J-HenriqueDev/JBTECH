<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\Categoria;
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
        $produtos = Produto::all(); // Obtém todos os produtos
        $categorias = Categoria::all(); // Obtém todas as categorias

        // Registra um log
        LogService::registrar(
            'Produto', // Categoria
            'Listar', // Ação
            'Listou todos os produtos' // Detalhes
        );

        return view('content.produtos.listar', compact('produtos', 'categorias')); // Passa produtos e categorias para a view
    }

    public function listar()
    {
        // Tente obter os produtos do banco
        try {
            $produtos = Produto::all();  // Obtém todos os produtos

            if ($produtos->isEmpty()) {
                // Registra um log de aviso
                LogService::registrar(
                    'Produto', // Categoria
                    'Listar', // Ação
                    'Nenhum produto encontrado' // Detalhes
                );

                return response()->json(['error' => 'Nenhum produto encontrado'], 404);
            }

            // Registra um log
            LogService::registrar(
                'Produto', // Categoria
                'Listar', // Ação
                'Listou todos os produtos via API' // Detalhes
            );

            return response()->json($produtos);

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

        // Registra um log
        LogService::registrar(
            'Produto', // Categoria
            'Criar', // Ação
            'Acessou o formulário de criação de produto' // Detalhes
        );

        return view('content.produtos.criar', compact('categorias')); // Retorna a view de criação
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

        $productsData = [];
        $fornecedor = []; // Array para armazenar os dados do fornecedor

        try {
            $xml = simplexml_load_file($request->file('xml_file')->getRealPath());
        } catch (\Exception $e) {
            Log::error('Erro ao ler o arquivo XML: ' . $e->getMessage());

            // Registra um log de erro
            LogService::registrar(
                'Produto', // Categoria
                'Erro', // Ação
                "Erro ao ler o arquivo XML: {$e->getMessage()}" // Detalhes
            );

            return redirect()->route('produtos.importarView')->withErrors('Erro ao ler o arquivo XML.');
        }

        // Verifica se o XML tem a estrutura esperada
        if (!isset($xml->NFe->infNFe->det)) {
            Log::warning('Estrutura do XML inválida.');

            // Registra um log de aviso
            LogService::registrar(
                'Produto', // Categoria
                'Aviso', // Ação
                'Estrutura do XML inválida' // Detalhes
            );

            return redirect()->route('produtos.importarView')->withErrors('Estrutura do XML inválida.');
        }

        // Extrai os dados do fornecedor do XML
        if (isset($xml->NFe->infNFe->emit)) {
            $fornecedor = [
                'cnpj' => (string) $xml->NFe->infNFe->emit->CNPJ,
                'nome' => (string) $xml->NFe->infNFe->emit->xNome,
                'telefone' => (string) $xml->NFe->infNFe->emit->enderEmit->fone,
                'email' => '', // O email do fornecedor geralmente não está no XML
            ];
        }

        // Percorre os produtos dentro do XML
        foreach ($xml->NFe->infNFe->det as $produto) {
            $productsData[] = [
                'nome' => (string) $produto->prod->xProd,
                'preco_custo' => (float) $produto->prod->vProd, // Valor original do XML
                'preco_venda' => (float) $produto->prod->vProd, // Valor original do XML (sem margem de lucro)
                'codigo_barras' => (string) $produto->prod->cEAN,
                'ncm' => (string) $produto->prod->NCM,
                'estoque' => (int) $produto->prod->qCom,
                'categoria_id' => null, // Categoria não está no XML, será selecionada manualmente
            ];
        }

        Log::info('Produtos extraídos do XML:', ['productsData' => $productsData]);
        Log::info('Dados do fornecedor extraídos do XML:', ['fornecedor' => $fornecedor]);

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

            try {
                $validated = $this->validateProduto($produto);

                $produtoExistente = Produto::where('nome', $validated['nome'])->first();

                if ($produtoExistente) {
                    $produtoExistente->update(array_merge($validated, [
                        'fornecedor_cnpj' => $request->fornecedor_cnpj,
                        'fornecedor_nome' => $request->fornecedor_nome,
                        'fornecedor_telefone' => $request->fornecedor_telefone,
                        'fornecedor_email' => $request->fornecedor_email,
                    ]));

                    // Registra um log
                    LogService::registrar(
                        'Produto', // Categoria
                        'Atualizar', // Ação
                        "Produto ID: {$produtoExistente->id} atualizado" // Detalhes
                    );
                } else {
                    $novoProduto = Produto::create(array_merge($validated, [
                        'fornecedor_cnpj' => $request->fornecedor_cnpj,
                        'fornecedor_nome' => $request->fornecedor_nome,
                        'fornecedor_telefone' => $request->fornecedor_telefone,
                        'fornecedor_email' => $request->fornecedor_email,
                        'usuario_id' => Auth::user()->id,
                    ]));

                    // Registra um log
                    LogService::registrar(
                        'Produto', // Categoria
                        'Criar', // Ação
                        "Produto ID: {$novoProduto->id} criado" // Detalhes
                    );
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
            $produto = Produto::findOrFail($id);

            // Busca as categorias para o dropdown
            $categorias = Categoria::all();

            // Registra um log
            LogService::registrar(
                'Produto', // Categoria
                'Editar', // Ação
                "Acessou o formulário de edição do produto ID: {$produto->id}" // Detalhes
            );

            // Retorna a view de edição com os dados do produto e categorias
            return view('content.produtos.editar', compact('produto', 'categorias'));
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
            ]);

            // Converte os valores de preço para o formato numérico
            $preco_custo = str_replace(['.', ','], ['', '.'], $request->preco_custo);
            $preco_venda = str_replace(['.', ','], ['', '.'], $request->preco_venda);

            // Atualiza os dados do produto
            $produto->update([
                'nome' => $request->nome,
                'preco_custo' => $preco_custo,
                'preco_venda' => $preco_venda,
                'codigo_barras' => $request->codigo_barras,
                'ncm' => $request->ncm,
                'estoque' => $request->estoque,
                'categoria_id' => $request->categoria_id,
                'fabricante' => $request->fabricante,
                'fornecedor_cnpj' => $request->fornecedor_cnpj,
                'fornecedor_nome' => $request->fornecedor_nome,
                'fornecedor_telefone' => $request->fornecedor_telefone,
                'fornecedor_email' => $request->fornecedor_email,
            ]);

            // Registra um log
            LogService::registrar(
                'Produto', // Categoria
                'Atualizar', // Ação
                "Produto ID: {$produto->id} atualizado com sucesso" // Detalhes
            );

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

        // Registra um log
        LogService::registrar(
            'Produto', // Categoria
            'Excluir', // Ação
            "Produto ID: {$produto->id} excluído com sucesso" // Detalhes
        );

        return redirect()->route('produtos.index')->with('success', 'Produto removido com sucesso!');
    }
}
