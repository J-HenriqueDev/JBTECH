<?php

namespace App\Http\Controllers;

use App\Models\Exemplo;
use Illuminate\Http\Request;
use App\Services\LogService;

class ExemploController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $exemplos = Exemplo::all();

        // Registra um log
        LogService::registrar(
            'Exemplo', // Categoria
            'Listar', // Ação
            'Listou todos os exemplos' // Detalhes
        );

        return view('exemplos.index', compact('exemplos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Registra um log
        LogService::registrar(
            'Exemplo', // Categoria
            'Criar', // Ação
            'Acessou a página de criação de exemplo' // Detalhes
        );

        return view('exemplos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
        ]);

        $exemplo = Exemplo::create($validated);

        // Registra um log
        LogService::registrar(
            'Exemplo', // Categoria
            'Criar', // Ação
            "Exemplo ID: {$exemplo->id} criado" // Detalhes
        );

        return redirect()->route('exemplos.index')->with('success', 'Exemplo criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $exemplo = Exemplo::findOrFail($id);

        // Registra um log
        // LogService::registrar(
        //    'Exemplo', // Categoria
        //    'Visualizar', // Ação
        //    "Exemplo ID: {$exemplo->id} visualizado" // Detalhes
        // );

        return view('exemplos.show', compact('exemplo'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $exemplo = Exemplo::findOrFail($id);

        // Registra um log
        LogService::registrar(
            'Exemplo', // Categoria
            'Editar', // Ação
            "Exemplo ID: {$exemplo->id} acessado para edição" // Detalhes
        );

        return view('exemplos.edit', compact('exemplo'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
        ]);

        $exemplo = Exemplo::findOrFail($id);
        $exemplo->update($validated);

        // Registra um log
        LogService::registrar(
            'Exemplo', // Categoria
            'Editar', // Ação
            "Exemplo ID: {$exemplo->id} atualizado" // Detalhes
        );

        return redirect()->route('exemplos.index')->with('success', 'Exemplo atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $exemplo = Exemplo::findOrFail($id);
        $exemplo->delete();

        // Registra um log
        LogService::registrar(
            'Exemplo', // Categoria
            'Excluir', // Ação
            "Exemplo ID: {$exemplo->id} excluído" // Detalhes
        );

        return redirect()->route('exemplos.index')->with('success', 'Exemplo excluído com sucesso!');
    }
}
