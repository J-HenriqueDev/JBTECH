<?php

namespace App\Http\Controllers;

use App\Models\SolicitacaoServico;
use App\Models\Clientes;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SolicitacaoServicoController extends Controller
{
    public function index()
    {
        $solicitacoes = SolicitacaoServico::with(['cliente', 'atendente'])
            ->orderBy('data_solicitacao', 'desc')
            ->paginate(20);

        return view('content.solicitacoes.index', compact('solicitacoes'));
    }

    public function create()
    {
        $clientes = Clientes::orderBy('nome')->get();
        $users = User::orderBy('name')->get();
        return view('content.solicitacoes.create', compact('clientes', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'atendente_id' => 'nullable|exists:users,id',
            'canal_atendimento' => 'required|string',
            'data_solicitacao' => 'required|date',
            'hora_solicitacao' => 'required',
            'tipo_atendimento' => 'required|string',
            'descricao' => 'required|string',
            'status' => 'required|string',
        ]);

        // Combine Date and Time
        $dataHora = Carbon::createFromFormat('Y-m-d H:i', $request->data_solicitacao . ' ' . $request->hora_solicitacao);

        SolicitacaoServico::create([
            'cliente_id' => $request->cliente_id,
            'atendente_id' => $request->atendente_id ?? auth()->id(), // Default to current user if not provided
            'canal_atendimento' => $request->canal_atendimento,
            'data_solicitacao' => $dataHora,
            'tipo_atendimento' => $request->tipo_atendimento,
            'descricao' => $request->descricao,
            'pendencias' => $request->pendencias,
            'status' => $request->status,
        ]);

        return redirect()->route('solicitacoes.index')->with('success', 'Solicitação registrada com sucesso!');
    }

    public function edit($id)
    {
        $solicitacao = SolicitacaoServico::findOrFail($id);
        $clientes = Clientes::orderBy('nome')->get();
        $users = User::orderBy('name')->get();

        return view('content.solicitacoes.edit', compact('solicitacao', 'clientes', 'users'));
    }

    public function update(Request $request, $id)
    {
        $solicitacao = SolicitacaoServico::findOrFail($id);

        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'atendente_id' => 'nullable|exists:users,id',
            'canal_atendimento' => 'required|string',
            'data_solicitacao' => 'required|date',
            'hora_solicitacao' => 'required',
            'tipo_atendimento' => 'required|string',
            'descricao' => 'required|string',
            'status' => 'required|string',
        ]);

        // Combine Date and Time
        $dataHora = Carbon::createFromFormat('Y-m-d H:i', $request->data_solicitacao . ' ' . $request->hora_solicitacao);

        $solicitacao->update([
            'cliente_id' => $request->cliente_id,
            'atendente_id' => $request->atendente_id,
            'canal_atendimento' => $request->canal_atendimento,
            'data_solicitacao' => $dataHora,
            'tipo_atendimento' => $request->tipo_atendimento,
            'descricao' => $request->descricao,
            'pendencias' => $request->pendencias,
            'status' => $request->status,
        ]);

        return redirect()->route('solicitacoes.index')->with('success', 'Solicitação atualizada com sucesso!');
    }

    public function destroy($id)
    {
        $solicitacao = SolicitacaoServico::findOrFail($id);
        $solicitacao->delete();

        return redirect()->route('solicitacoes.index')->with('success', 'Solicitação excluída com sucesso!');
    }
}
