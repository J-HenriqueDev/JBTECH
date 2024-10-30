<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    // Método para listar todas as categorias com seus produtos
    public function index()
    {
        $categorias = Categoria::with('produtos')->get();
        return view('content.categorias.listar', compact('categorias'));
    }

    // Método para armazenar uma nova categoria
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
        ]);

        Categoria::create($validated);
        return redirect()->route('categorias.index')->with('success', 'Categoria criada com sucesso!');
    }
}
