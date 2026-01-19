<?php

namespace App\Http\Controllers;

use App\Models\ContaPagar;
use App\Models\Fornecedor;
use Illuminate\Http\Request;

class ContaPagarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ContaPagar::with('fornecedor');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('descricao', 'like', "%{$search}%")
                ->orWhereHas('fornecedor', function ($q) use ($search) {
                    $q->where('nome', 'like', "%{$search}%");
                });
        }

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $contas = $query->orderBy('data_vencimento', 'asc')->paginate(10);

        $stats = [
            'total_pendente' => ContaPagar::where('status', 'pendente')->sum('valor'),
            'total_hoje' => ContaPagar::where('status', 'pendente')->whereDate('data_vencimento', now())->sum('valor'),
            'total_atrasado' => ContaPagar::where('status', 'pendente')->whereDate('data_vencimento', '<', now())->sum('valor'),
        ];

        return view('content.contas-pagar.index', compact('contas', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $fornecedores = Fornecedor::orderBy('nome')->get();
        return view('content.contas-pagar.create', compact('fornecedores'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'descricao' => 'required|string|max:255',
            'valor' => 'required|numeric|min:0',
            'data_vencimento' => 'required|date',
            'fornecedor_id' => 'nullable|exists:fornecedores,id',
        ]);

        ContaPagar::create([
            'descricao' => $request->descricao,
            'valor' => $request->valor,
            'data_vencimento' => $request->data_vencimento,
            'fornecedor_id' => $request->fornecedor_id,
            'status' => 'pendente',
            'origem' => 'manual',
            'observacoes' => $request->observacoes,
        ]);

        return redirect()->route('contas-pagar.index')->with('success', 'Conta a pagar cadastrada com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(ContaPagar $contaPagar)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $contaPagar = ContaPagar::findOrFail($id);
        $fornecedores = Fornecedor::orderBy('nome')->get();
        return view('content.contas-pagar.edit', compact('contaPagar', 'fornecedores'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $contaPagar = ContaPagar::findOrFail($id);

        $request->validate([
            'descricao' => 'required|string|max:255',
            'valor' => 'required|numeric|min:0',
            'data_vencimento' => 'required|date',
            'fornecedor_id' => 'nullable|exists:fornecedores,id',
            'status' => 'required|in:pendente,pago,cancelado,atrasado',
        ]);

        $data = $request->all();

        // Se marcou como pago e não tinha data de pagamento, define hoje
        if ($data['status'] == 'pago' && empty($data['data_pagamento'])) {
            $data['data_pagamento'] = now();
        }

        $contaPagar->update($data);

        return redirect()->route('contas-pagar.index')->with('success', 'Conta atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $contaPagar = ContaPagar::findOrFail($id);
        $contaPagar->delete();

        return redirect()->route('contas-pagar.index')->with('success', 'Conta excluída com sucesso!');
    }
}
