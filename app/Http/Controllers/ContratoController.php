<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use App\Models\Clientes;
use App\Models\Servico;
use Illuminate\Http\Request;
use App\Services\LogService;

class ContratoController extends Controller
{
    public function index()
    {
        $contratos = Contrato::with(['cliente', 'servico'])->latest()->paginate(15);
        $diasAntecedencia = \App\Models\Configuracao::get('contratos_dias_antecedencia', 7);
        return view('content.contratos.index', compact('contratos', 'diasAntecedencia'));
    }

    public function create()
    {
        $clientes = Clientes::orderBy('nome')->get();
        $servicos = Servico::where('ativo', true)->orderBy('nome')->get();
        return view('content.contratos.create', compact('clientes', 'servicos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'servico_id' => 'nullable|exists:servicos,id',
            'descricao' => 'required|string|max:255',
            'valor' => 'required',
            'dia_vencimento' => 'required|integer|min:1|max:31',
            'dias_personalizados' => 'nullable|string',
            'data_inicio' => 'required|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
            'frequencia' => 'required|in:mensal,trimestral,semestral,anual',
            'forma_pagamento' => 'required|string',
            'codigo_servico' => 'nullable|string',
            'codigo_nbs' => 'nullable|string',
            'aliquota_iss' => 'nullable|numeric',
            'iss_retido' => 'nullable|boolean',
            'discriminacao_servico' => 'nullable|string',
            'observacoes' => 'nullable|string',
        ]);

        $validated['valor'] = str_replace(['.', ','], ['', '.'], $validated['valor']);
        $validated['iss_retido'] = $request->has('iss_retido');

        $contrato = new Contrato($validated);

        // Define o próximo faturamento
        $contrato->proximo_faturamento = $contrato->calcularProximoFaturamento($contrato->data_inicio);
        $contrato->save();

        LogService::registrar('Contratos', 'Criar', 'Criou contrato #' . $contrato->id);

        return redirect()->route('contratos.index')->with('success', 'Contrato criado com sucesso!');
    }

    public function edit(Contrato $contrato)
    {
        $clientes = Clientes::orderBy('nome')->get();
        $servicos = Servico::where('ativo', true)->orderBy('nome')->get();
        return view('content.contratos.edit', compact('contrato', 'clientes', 'servicos'));
    }

    public function update(Request $request, Contrato $contrato)
    {
        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'servico_id' => 'nullable|exists:servicos,id',
            'descricao' => 'required|string|max:255',
            'valor' => 'required',
            'dia_vencimento' => 'required|integer|min:1|max:31',
            'dias_personalizados' => 'nullable|string',
            'data_inicio' => 'required|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
            'frequencia' => 'required|in:mensal,trimestral,semestral,anual',
            'forma_pagamento' => 'required|string',
            'codigo_servico' => 'nullable|string',
            'codigo_nbs' => 'nullable|string',
            'aliquota_iss' => 'nullable|numeric',
            'iss_retido' => 'nullable|boolean',
            'discriminacao_servico' => 'nullable|string',
            'observacoes' => 'nullable|string',
        ]);

        $validated['valor'] = str_replace(['.', ','], ['', '.'], $validated['valor']);
        $validated['iss_retido'] = $request->has('iss_retido');

        $contrato->fill($validated);

        // Recalcular proximo faturamento se datas mudaram?
        // Por segurança, mantemos a logica manual ou só recalculamos se for explicitamente pedido
        // Mas se mudar o dia de vencimento, deveria ajustar o proximo?
        // Simplificação: Atualiza os dados e mantem o proximo faturamento (a menos que seja passado)

        $contrato->save();

        LogService::registrar('Contratos', 'Editar', 'Editou contrato #' . $contrato->id);

        return redirect()->route('contratos.index')->with('success', 'Contrato atualizado com sucesso!');
    }

    public function destroy(Contrato $contrato)
    {
        $contrato->delete();
        LogService::registrar('Contratos', 'Excluir', 'Excluiu contrato #' . $contrato->id);
        return redirect()->route('contratos.index')->with('success', 'Contrato excluído com sucesso!');
    }
}
