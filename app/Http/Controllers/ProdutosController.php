<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProdutosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $produtos = Produto::all(); // Obtém todos os produtos
        $categorias = Categoria::all(); // Obtém todas as categorias
        return view('content.produtos.listar', compact('produtos', 'categorias')); // Passa produtos e categorias para a view
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $categorias = Categoria::all(); // Obtém todas as categorias
        $productsData = $request->input('productsData', []); // Obtém os dados dos produtos importados se existirem na requisição
        return view('content.produtos.criar', compact('categorias', 'productsData')); // Retorna a view com os dados
    }

    /**
     * Importa produtos de um arquivo XML e carrega os dados na view.
     */
    public function import(Request $request)
    {
        // Validação do arquivo de upload
        $request->validate([
            'xml_file' => 'required|file|mimes:xml',
        ]);

        // Inicializa a variável $productsData
        $productsData = [];

        // Tenta carregar o arquivo XML
        try {
            $xml = simplexml_load_file($request->file('xml_file')->getRealPath());
        } catch (\Exception $e) {
            Log::error('Erro ao ler o arquivo XML: ' . $e->getMessage());
            return redirect()->route('produtos.create')->withErrors('Erro ao ler o arquivo XML.');
        }

        // Validação da estrutura do XML
        if (!isset($xml->NFe->infNFe->det)) {
            Log::warning('Estrutura do XML inválida.');
            return redirect()->route('produtos.create')->withErrors('Estrutura do XML inválida.');
        }

        // Armazena os produtos em um array
        foreach ($xml->NFe->infNFe->det as $produto) {
            $productsData[] = [
                'nome' => (string) $produto->prod->xProd,
                'preco_custo' => (float) $produto->prod->vProd,
                'preco_venda' => (float) $produto->prod->vProd * 1.2,
                'codigo_barras' => (string) $produto->prod->cEAN,
                'ncm' => (string) $produto->prod->NCM,
                'tipo_produto' => (string) $produto->prod->uCom, // Se não estiver mais no formulário, remova
                'estoque' => (int) $produto->prod->qCom,
                'fornecedor_cnpj' => (string) $xml->NFe->infNFe->emit->CNPJ,
                'fornecedor_nome' => (string) $xml->NFe->infNFe->emit->xNome,
                'fornecedor_telefone' => (string) $xml->NFe->infNFe->emit->enderEmit->fone,
                'fornecedor_email' => 'fornecedor@exemplo.com', // Se não estiver mais, remova
            ];
        }

        // Log dos dados processados
        Log::info('Dados do XML processados:', ['productsData' => $productsData]);

        // Redireciona para a rota de criação com todos os produtos como parâmetros
        return redirect()->route('produtos.create', ['productsData' => $productsData]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validação dos campos do fornecedor
        $request->validate([
            'fornecedor_cnpj' => 'required|string|max:14',
            'fornecedor_nome' => 'required|string|max:255',
            'fornecedor_telefone' => 'required|string|max:15',
            'fornecedor_email' => 'required|email',
        ]);

        $produtos = $request->input('produtos');

        // Validação e armazenamento de cada produto
        foreach ($produtos as $produto) {
          // Convertendo os preços para float
          $produto['preco_custo'] = str_replace(',', '.', str_replace('.', '', $produto['preco_custo']));
          $produto['preco_venda'] = str_replace(',', '.', str_replace('.', '', $produto['preco_venda']));

          try {
              // Validação dos dados do produto
              $validated = $this->validateProduto($produto);

              // Verifica se o produto já existe
              $produtoExistente = Produto::where('nome', $validated['nome'])->first();

              if ($produtoExistente) {
                  // Atualiza o produto existente
                  $produtoExistente->update(array_merge($validated, [
                      'fornecedor_cnpj' => $request->fornecedor_cnpj,
                      'fornecedor_nome' => $request->fornecedor_nome,
                      'fornecedor_telefone' => $request->fornecedor_telefone,
                      'fornecedor_email' => $request->fornecedor_email,
                      'usuario_id' => $request->usuario_id,
                  ]));
              } else {
                  // Cria um novo produto
                  Produto::create(array_merge($validated, [
                      'fornecedor_cnpj' => $request->fornecedor_cnpj,
                      'fornecedor_nome' => $request->fornecedor_nome,
                      'fornecedor_telefone' => $request->fornecedor_telefone,
                      'fornecedor_email' => $request->fornecedor_email,
                      'usuario_id' => $request->usuario_id,
                  ]));
              }
          } catch (\Exception $e) {
              Log::error('Erro ao salvar o produto: ' . $e->getMessage());
              return redirect()->back()->withErrors('Erro ao salvar o produto: ' . $e->getMessage());
          }
      }



        return redirect()->route('produtos.index')->with('success', 'Produtos cadastrados com sucesso!');
    }

    // Método para validar um produto
    protected function validateProduto($produto)
    {
        return validator()->make($produto, [
            'nome' => 'required|string|max:255', // Removida a regra `unique`
            'preco_custo' => 'required|numeric',
            'preco_venda' => 'required|numeric',
            'codigo_barras' => 'required|string|max:13',
            'ncm' => 'required|string|max:8',
            'estoque' => 'required|integer',
            'categoria_id' => 'required|exists:categorias,id', // Validação do campo categoria_id
        ])->validate();
    }



    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $produto = Produto::find($id);

        if (!$produto) {
            return response()->json(['error' => 'Produto não encontrado'], 404);
        }

        return response()->json($produto);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Produto $produto)
    {
        $categorias = Categoria::all(); // Obtém todas as categorias
        return view('content.produtos.editar', compact('produto', 'categorias')); // Passa a variável $produto para a view
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Produto $produto)
    {
        $produto->delete(); // Remove o produto

        return redirect()->route('produtos.index')->with('success', 'Produto removido com sucesso!'); // Redireciona com mensagem de sucesso
    }

    // Outros métodos se necessário...
}
