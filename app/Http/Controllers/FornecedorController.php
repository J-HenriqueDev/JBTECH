<?php

namespace App\Http\Controllers;

use App\Models\Fornecedor;
use Illuminate\Http\Request;

class FornecedorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Fornecedor::query();

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('nome', 'like', "%{$search}%")
                ->orWhere('cnpj', 'like', "%{$search}%");
        }

        $fornecedores = $query->orderBy('nome')->paginate(10);

        return view('content.fornecedores.index', compact('fornecedores'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('content.fornecedores.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'cnpj' => 'nullable|string|max:20|unique:fornecedores,cnpj',
            'email' => 'nullable|email|max:255',
        ]);

        // Remove mascara CNPJ se necessário
        $data = $request->all();
        // $data['cnpj'] = preg_replace('/[^0-9]/', '', $data['cnpj']);

        Fornecedor::create($data);

        return redirect()->route('fornecedores.index')->with('success', 'Fornecedor cadastrado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Fornecedor $fornecedor)
    {
        return view('content.fornecedores.show', compact('fornecedor'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $fornecedor = Fornecedor::findOrFail($id);
        return view('content.fornecedores.edit', compact('fornecedor'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $fornecedor = Fornecedor::findOrFail($id);

        $request->validate([
            'nome' => 'required|string|max:255',
            'cnpj' => 'nullable|string|max:20|unique:fornecedores,cnpj,' . $fornecedor->id,
            'email' => 'nullable|email|max:255',
        ]);

        $fornecedor->update($request->all());

        return redirect()->route('fornecedores.index')->with('success', 'Fornecedor atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $fornecedor = Fornecedor::findOrFail($id);

        // Verifica se tem produtos ou contas associadas
        if ($fornecedor->produtos()->exists() || \App\Models\ContaPagar::where('fornecedor_id', $fornecedor->id)->exists()) {
            return redirect()->route('fornecedores.index')->with('error', 'Não é possível excluir fornecedor com produtos ou contas associadas.');
        }

        $fornecedor->delete();

        return redirect()->route('fornecedores.index')->with('success', 'Fornecedor excluído com sucesso!');
    }
}
