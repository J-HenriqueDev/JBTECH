<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;
use App\Services\LogService;

class CategoriaController extends Controller
{
    // Método para listar todas as categorias com seus produtos
    public function index()
    {
        $categorias = Categoria::with('produtos')->withCount('produtos')->orderBy('nome')->get();
        
        // Estatísticas
        $stats = [
            'total' => Categoria::count(),
            'total_produtos' => \App\Models\Produto::count(),
            'categoria_mais_produtos' => Categoria::withCount('produtos')->orderBy('produtos_count', 'desc')->first(),
        ];
        
        return view('content.categorias.listar', compact('categorias', 'stats'));
    }

    // Método para armazenar uma nova categoria
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255|unique:categorias,nome',
            'descricao' => 'nullable|string|max:500',
        ]);

        $categoria = Categoria::create($validated);
        
        LogService::registrar(
            'Categoria',
            'Criar',
            "Categoria '{$categoria->nome}' criada com sucesso"
        );

        return redirect()->route('categorias.index')->with('success', 'Categoria criada com sucesso!');
    }

    // Método para atualizar uma categoria
    public function update(Request $request, $id)
    {
        $categoria = Categoria::findOrFail($id);
        
        $validated = $request->validate([
            'nome' => 'required|string|max:255|unique:categorias,nome,' . $categoria->id,
            'descricao' => 'nullable|string|max:500',
        ]);

        $categoria->update($validated);
        
        LogService::registrar(
            'Categoria',
            'Atualizar',
            "Categoria '{$categoria->nome}' atualizada"
        );

        return redirect()->route('categorias.index')->with('success', 'Categoria atualizada com sucesso!');
    }

    // Método para excluir uma categoria
    public function destroy($id)
    {
        $categoria = Categoria::findOrFail($id);
        
        // Verifica se há produtos associados
        if ($categoria->produtos()->count() > 0) {
            return redirect()->back()->with('error', 'Não é possível excluir uma categoria que possui produtos associados!');
        }
        
        $nome = $categoria->nome;
        $categoria->delete();
        
        LogService::registrar(
            'Categoria',
            'Excluir',
            "Categoria '{$nome}' excluída"
        );

        return redirect()->route('categorias.index')->with('success', 'Categoria excluída com sucesso!');
    }
}
