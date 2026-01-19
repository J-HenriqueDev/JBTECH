<?php

namespace App\Http\Controllers;

use App\Models\NaturezaOperacao;
use Illuminate\Http\Request;

class NaturezaOperacaoController extends Controller
{
    public function index()
    {
        $naturezas = NaturezaOperacao::orderBy('tipo')->orderBy('descricao')->get();
        return view('content.naturezas.index', compact('naturezas'));
    }

    public function create()
    {
        return view('content.naturezas.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'descricao' => 'required|string|max:255',
            'tipo' => 'required|in:entrada,saida',
            'cfop_estadual' => 'required|string|size:4',
            'cfop_interestadual' => 'required|string|size:4',
            'cfop_exterior' => 'nullable|string|size:4',
        ]);

        $dados = $request->all();
        $dados['padrao'] = $request->has('padrao');
        $dados['calcula_custo'] = $request->has('calcula_custo');
        $dados['movimenta_estoque'] = $request->has('movimenta_estoque');
        $dados['gera_financeiro'] = $request->has('gera_financeiro');

        if ($dados['padrao']) {
            NaturezaOperacao::where('tipo', $dados['tipo'])->update(['padrao' => false]);
        }

        NaturezaOperacao::create($dados);

        return redirect()->route('naturezas.index')->with('success', 'Natureza de Operação criada com sucesso!');
    }

    public function edit(NaturezaOperacao $natureza)
    {
        return view('content.naturezas.edit', compact('natureza'));
    }

    public function update(Request $request, NaturezaOperacao $natureza)
    {
        $request->validate([
            'descricao' => 'required|string|max:255',
            'tipo' => 'required|in:entrada,saida',
            'cfop_estadual' => 'required|string|size:4',
            'cfop_interestadual' => 'required|string|size:4',
            'cfop_exterior' => 'nullable|string|size:4',
        ]);

        $dados = $request->all();
        $dados['padrao'] = $request->has('padrao');
        $dados['calcula_custo'] = $request->has('calcula_custo');
        $dados['movimenta_estoque'] = $request->has('movimenta_estoque');
        $dados['gera_financeiro'] = $request->has('gera_financeiro');

        if ($dados['padrao']) {
            NaturezaOperacao::where('tipo', $dados['tipo'])->where('id', '!=', $natureza->id)->update(['padrao' => false]);
        }

        $natureza->update($dados);

        return redirect()->route('naturezas.index')->with('success', 'Natureza de Operação atualizada com sucesso!');
    }

    public function destroy(NaturezaOperacao $natureza)
    {
        $natureza->delete();
        return redirect()->route('naturezas.index')->with('success', 'Natureza de Operação excluída com sucesso!');
    }
}
