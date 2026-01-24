<?php

namespace App\Http\Controllers;

use App\Models\Servico;
use Illuminate\Http\Request;
use App\Services\LogService;

class ServicoController extends Controller
{
    public function index()
    {
        $servicos = Servico::orderBy('nome')->paginate(15);
        return view('content.servicos.index', compact('servicos'));
    }

    public function create()
    {
        return view('content.servicos.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'codigo_servico' => 'nullable|string|max:20',
            'codigo_nbs' => 'nullable|string|max:20',
            'aliquota_iss' => 'nullable|numeric|min:0|max:100',
            'iss_retido' => 'nullable|boolean',
            'discriminacao_padrao' => 'nullable|string',
            'observacoes' => 'nullable|string',
        ]);

        $validated['iss_retido'] = $request->has('iss_retido');

        $servico = Servico::create($validated);

        LogService::registrar('Servicos', 'Criar', 'Criou modelo de serviço #' . $servico->id);

        return redirect()->route('servicos.index')->with('success', 'Modelo de serviço criado com sucesso!');
    }

    public function edit(Servico $servico)
    {
        return view('content.servicos.edit', compact('servico'));
    }

    public function update(Request $request, Servico $servico)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'codigo_servico' => 'nullable|string|max:20',
            'codigo_nbs' => 'nullable|string|max:20',
            'aliquota_iss' => 'nullable|numeric|min:0|max:100',
            'iss_retido' => 'nullable|boolean',
            'discriminacao_padrao' => 'nullable|string',
            'observacoes' => 'nullable|string',
        ]);

        $validated['iss_retido'] = $request->has('iss_retido');
        $validated['ativo'] = $request->has('ativo');

        $servico->update($validated);

        LogService::registrar('Servicos', 'Editar', 'Editou modelo de serviço #' . $servico->id);

        return redirect()->route('servicos.index')->with('success', 'Modelo de serviço atualizado com sucesso!');
    }

    public function destroy(Servico $servico)
    {
        // Verificar se tem contratos vinculados
        if ($servico->contratos()->count() > 0) {
            return redirect()->route('servicos.index')->with('error', 'Não é possível excluir este serviço pois existem contratos vinculados a ele.');
        }

        $servico->delete();
        LogService::registrar('Servicos', 'Excluir', 'Excluiu modelo de serviço #' . $servico->id);

        return redirect()->route('servicos.index')->with('success', 'Modelo de serviço excluído com sucesso!');
    }
}
