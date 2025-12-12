<?php
// app/Http/Controllers/OSController.php

namespace App\Http\Controllers;

use App\Models\OS;
use Illuminate\Support\Facades\Auth;
use App\Models\Clientes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\LogService;
use Barryvdh\DomPDF\Facade\Pdf;

class OSController extends Controller
{
    public function index(Request $request)
    {
        $query = OS::with('cliente');
        
        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('cliente', function($q) use ($search) {
                      $q->where('nome', 'like', "%{$search}%")
                        ->orWhere('cpf_cnpj', 'like', "%{$search}%");
                  });
            });
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('tipo_id')) {
            $query->where('tipo_id', $request->tipo_id);
        }
        
        if ($request->filled('data_inicio')) {
            $query->whereDate('data_de_entrada', '>=', $request->data_inicio);
        }
        
        if ($request->filled('data_fim')) {
            $query->whereDate('data_de_entrada', '<=', $request->data_fim);
        }
        
        // Estatísticas
        $stats = [
            'total' => OS::count(),
            'pendentes' => OS::where('status', 'pendente')->count(),
            'em_andamento' => OS::where('status', 'em_andamento')->count(),
            'concluidas' => OS::where('status', 'concluida')->count(),
            'entregues' => OS::where('status', 'entregue')->count(),
        ];
        
        $ordens = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return view('content.os.listar', compact('ordens', 'stats'));
    }

    public function create()
    {
        return view('content.os.criar', [
            'clientes' => Clientes::with('endereco')->orderBy('nome')->get(),
            'tipos' => OS::TIPOS_DE_EQUIPAMENTO,
            'status' => OS::STATUS
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'tipo_id' => 'required|in:' . implode(',', array_keys(OS::TIPOS_DE_EQUIPAMENTO)),
            'data_de_entrada' => 'required|date',
            'prazo_entrega' => 'required|date|after:data_de_entrada',
            'problema_item' => 'required|string',
            'avarias' => 'nullable|string',
            'acessorios' => 'nullable|array',
            'senha_do_dispositivo' => 'nullable|string|max:255',
            'modelo_do_dispositivo' => 'nullable|string|max:255',
            'sn' => 'nullable|string|max:255',
            'status' => 'nullable|in:' . implode(',', array_keys(OS::STATUS)),
            'observacoes' => 'nullable|string',
            'valor_servico' => 'nullable|numeric|min:0',
            'fotos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Processa valor do serviço
        $valorServico = null;
        if ($request->filled('valor_servico')) {
            $valorStr = str_replace(['.', ','], ['', '.'], $request->valor_servico);
            $valorServico = floatval($valorStr);
        }
        
        $os = new OS();
        $os->cliente_id = $request->cliente_id;
        $os->tipo_id = $request->tipo_id;
        $os->data_de_entrada = $request->data_de_entrada;
        $os->prazo_entrega = $request->prazo_entrega;
        $os->problema_item = $request->problema_item;
        $os->avarias = $request->avarias;
        $os->usuario_id = Auth::id();
        $os->modelo_do_dispositivo = $request->modelo_do_dispositivo;
        $os->sn = $request->sn;
        $os->status = $request->status ?? 'pendente';
        $os->observacoes = $request->observacoes;
        $os->valor_servico = $valorServico;

        if ($request->has('acessorios')) {
            $os->acessorios = implode(',', $request->acessorios);
        } else {
            $os->acessorios = null;
        }

        $os->senha_do_dispositivo = $request->senha_do_dispositivo;

        if ($os->save()) {
            if ($request->hasFile('fotos')) {
                $caminhos = [];
                foreach ($request->file('fotos') as $foto) {
                    $caminho = $foto->store('fotos/os', 'public');
                    $caminhos[] = $caminho;
                }
                $os->fotos = implode(',', $caminhos);
                $os->save();
            }

            LogService::registrar(
                'OS',
                'Criar',
                "OS ID: {$os->id} criada para cliente: {$os->cliente->nome}"
            );
            
            $cliente = $os->cliente;
            return redirect()->route('os.index')->with('success', 'OS <strong>' . $os->id . '</strong> criada com sucesso para o cliente <strong>' . $cliente->nome . '</strong>.');
        }

        return back()->with('error', 'Erro ao criar a OS.');
    }
    
    public function show($id)
    {
        $os = OS::with(['cliente.endereco', 'usuario'])->findOrFail($id);
        return view('content.os.show', compact('os'));
    }

    public function edit($id)
    {
        $os = OS::findOrFail($id);

        // Converte os campos acessórios e fotos para arrays
        if ($os->acessorios) {
            $os->acessorios = is_array($os->acessorios) ? $os->acessorios : explode(',', $os->acessorios);
        } else {
            $os->acessorios = [];
        }
        
        if ($os->fotos && !is_array($os->fotos)) {
            $os->fotos = explode(',', $os->fotos);
        }

        return view('content.os.editar', [
            'clientes' => Clientes::with('endereco')->orderBy('nome')->get(),
            'tipos' => OS::TIPOS_DE_EQUIPAMENTO,
            'status' => OS::STATUS,
            'os' => $os
        ]);
    }


    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'cliente_id' => 'required|exists:clientes,id',
            'tipo_id' => 'required|in:' . implode(',', array_keys(OS::TIPOS_DE_EQUIPAMENTO)),
            'data_de_entrada' => 'required|date',
            'prazo_entrega' => 'required|date|after:data_de_entrada',
            'problema_item' => 'required|string',
            'avarias' => 'nullable|string',
            'acessorios' => 'nullable|array',
            'senha_do_dispositivo' => 'nullable|string|max:255',
            'modelo_do_dispositivo' => 'nullable|string|max:255',
            'sn' => 'nullable|string|max:255',
            'status' => 'required|in:' . implode(',', array_keys(OS::STATUS)),
            'observacoes' => 'nullable|string',
            'valor_servico' => 'nullable|numeric|min:0',
            'data_conclusao' => 'nullable|date',
            'fotos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Processa valor do serviço
        $valorServico = null;
        if ($request->filled('valor_servico')) {
            $valorStr = str_replace(['.', ','], ['', '.'], $request->valor_servico);
            $valorServico = floatval($valorStr);
        }
        
        $os = OS::findOrFail($id);
        $os->cliente_id = $request->cliente_id;
        $os->tipo_id = $request->tipo_id;
        $os->data_de_entrada = $request->data_de_entrada;
        $os->prazo_entrega = $request->prazo_entrega;
        $os->problema_item = $request->problema_item;
        $os->avarias = $request->avarias;
        $os->senha_do_dispositivo = $request->senha_do_dispositivo;
        $os->modelo_do_dispositivo = $request->modelo_do_dispositivo;
        $os->sn = $request->sn;
        $os->status = $request->status;
        $os->observacoes = $request->observacoes;
        $os->valor_servico = $valorServico;
        $os->data_conclusao = $request->data_conclusao;

        if ($request->has('acessorios')) {
            $os->acessorios = implode(',', $request->acessorios);
        } else {
            $os->acessorios = null;
        }

        if ($request->hasFile('fotos')) {
            $caminhos = [];
            foreach ($request->file('fotos') as $foto) {
                $caminho = $foto->store('fotos/os', 'public');
                $caminhos[] = $caminho;
            }
            $os->fotos = implode(',', $caminhos);
        }

        $os->save();
        
        LogService::registrar(
            'OS',
            'Atualizar',
            "OS ID: {$os->id} atualizada"
        );

        return redirect()->route('os.index')->with('success', 'OS atualizada com sucesso!');
    }
    
    public function destroy($id)
    {
        $os = OS::findOrFail($id);
        $osId = $os->id;
        $os->delete();
        
        LogService::registrar(
            'OS',
            'Excluir',
            "OS ID: {$osId} excluída"
        );
        
        return redirect()->route('os.index')->with('success', 'OS excluída com sucesso!');
    }
    
    public function gerarPdf($id)
    {
        $os = OS::with(['cliente.endereco', 'usuario'])->findOrFail($id);
        $pdf = Pdf::loadView('content.os.pdf', compact('os'));
        
        LogService::registrar(
            'OS',
            'Gerar PDF',
            "PDF da OS ID: {$os->id} gerado"
        );
        
        return $pdf->stream('os-' . $os->id . '.pdf');
    }

    public function search(Request $request)
    {
        $search = $request->input('search');

        if ($search === 'all') {
            $ordens = OS::with('cliente')->get();
        } else {
            $ordens = OS::with('cliente')
                ->where('id', 'like', "%{$search}%")
                ->orWhereHas('cliente', function ($query) use ($search) {
                    $query->where('nome', 'like', "%{$search}%")
                          ->orWhere('cpf_cnpj', 'like', "%{$search}%");
                })
                ->orWhere('tipo_id', 'like', "%{$search}%")
                ->get();
        }

        return response()->json($ordens);
    }
}
