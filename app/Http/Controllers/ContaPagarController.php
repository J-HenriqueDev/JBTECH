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

        // Filtro de Pesquisa
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('descricao', 'like', "%{$search}%")
                  ->orWhereHas('fornecedor', function ($subQ) use ($search) {
                      $subQ->where('nome', 'like', "%{$search}%");
                  });
            });
        }

        // Filtro de Status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filtro de Data (Padrao: Hoje, se não houver filtros explícitos)
        $dataInicio = $request->input('data_inicio');
        $dataFim = $request->input('data_fim');

        if ($dataInicio && $dataFim) {
            $query->whereBetween('data_vencimento', [$dataInicio, $dataFim]);
        } elseif (!$request->filled('search') && !$request->filled('status') && !$request->filled('data_inicio') && !$request->filled('data_fim')) {
            // Comportamento padrão: Apenas vencimento hoje
            $query->whereDate('data_vencimento', now());
            
            // Define valores para a view
            $dataInicio = now()->format('Y-m-d');
            $dataFim = now()->format('Y-m-d');
        }

        $contas = $query->orderBy('data_vencimento', 'asc')->paginate(10);

        // Estatísticas Globais (Independentes do filtro atual para visão geral)
        $stats = [
            'total_pendente' => ContaPagar::where('status', 'pendente')->sum('valor'),
            'total_hoje' => ContaPagar::where('status', 'pendente')->whereDate('data_vencimento', now())->sum('valor'),
            'total_atrasado' => ContaPagar::where('status', 'pendente')->whereDate('data_vencimento', '<', now())->sum('valor'),
        ];

        return view('content.contas-pagar.index', compact('contas', 'stats', 'dataInicio', 'dataFim'));
    }

    public function marcarComoPaga($id)
    {
        $conta = ContaPagar::findOrFail($id);
        
        if ($conta->status !== 'pago') {
            $conta->update([
                'status' => 'pago',
                'data_pagamento' => now()
            ]);
            return redirect()->back()->with('success', 'Conta marcada como paga com sucesso!');
        }
        
        return redirect()->back()->with('info', 'Esta conta já está paga.');
    }

    public function exportarRelatorio(Request $request)
    {
        // Reutiliza a lógica de filtro
        $query = ContaPagar::with('fornecedor');

        if ($request->filled('data_inicio') && $request->filled('data_fim')) {
            $query->whereBetween('data_vencimento', [$request->data_inicio, $request->data_fim]);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $contas = $query->orderBy('data_vencimento', 'asc')->get();

        $csvFileName = 'contas_pagar_' . date('Y-m-d_H-i') . '.csv';
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$csvFileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use($contas) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Descricao', 'Fornecedor', 'Vencimento', 'Valor', 'Status', 'Observacoes']);

            foreach ($contas as $conta) {
                fputcsv($file, [
                    $conta->id,
                    $conta->descricao,
                    $conta->fornecedor ? $conta->fornecedor->nome : 'N/A',
                    $conta->data_vencimento->format('d/m/Y'),
                    number_format($conta->valor, 2, ',', '.'),
                    ucfirst($conta->status),
                    $conta->observacoes
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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
            'recorrente' => 'nullable|boolean',
            'frequencia' => 'nullable|in:mensal,semanal,anual',
            'dia_vencimento' => 'nullable|integer|min:1|max:31',
        ]);

        ContaPagar::create([
            'descricao' => $request->descricao,
            'valor' => $request->valor,
            'data_vencimento' => $request->data_vencimento,
            'fornecedor_id' => $request->fornecedor_id,
            'status' => 'pendente',
            'origem' => 'manual',
            'observacoes' => $request->observacoes,
            'recorrente' => $request->recorrente ?? false,
            'frequencia' => $request->frequencia,
            'dia_vencimento' => $request->dia_vencimento,
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

    public function bulkPay(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:contas_pagar,id',
            'data_pagamento' => 'required|date',
            'metodo_pagamento' => 'nullable|string'
        ]);

        $count = 0;
        foreach ($request->ids as $id) {
            $conta = ContaPagar::find($id);
            if ($conta && $conta->status !== 'pago') {
                $conta->update([
                    'status' => 'pago',
                    'data_pagamento' => $request->data_pagamento,
                    'metodo_pagamento' => $request->metodo_pagamento,
                    'valor_pago' => $conta->valor // Assume full payment for bulk
                ]);
                $count++;
            }
        }

        return response()->json(['success' => true, 'message' => "$count contas marcadas como pagas com sucesso!"]);
    }
}
