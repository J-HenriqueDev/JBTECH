<?php
// app/Http/Controllers/OSController.php

namespace App\Http\Controllers;

use App\Models\OS;
use Illuminate\Support\Facades\Auth;
use App\Models\Clientes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OSController extends Controller
{
    public function index()
    {
        return view('content.os.listar', [
            'ordens' => OS::all(),
            'clientes' => Clientes::all()
        ]);
    }

    public function create()
    {
        return view('content.os.criar', [
            'clientes' => Clientes::all(),
            'tipos' => OS::TIPOS_DE_EQUIPAMENTO
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required',
            'tipo_id' => 'required',
            'data_de_entrada' => 'required|date',
            'prazo_entrega' => 'required|date|after:data_de_entrada',
            'problema_item' => 'required|string|max:255',
            'avarias' => 'nullable|string|max:255',
            'acessorios' => 'nullable|array',
            'senha_do_dispositivo' => 'nullable|string|max:255',
            'modelo_do_dispositivo' => 'nullable|string|max:255',
            'sn' => 'nullable|string|max:255',
            'fotos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $os = new OS();
        $os->cliente_id = $request->cliente_id;
        $os->tipo_id = $request->tipo_id;
        $os->data_de_entrada = $request->data_de_entrada;
        $os->prazo_entrega = $request->prazo_entrega;
        $os->problema_item = $request->problema_item;
        $os->avarias = $request->avarias;
        $os->usuario_id = $request->usuario_id;
        $os->modelo_do_dispositivo = $request->modelo_do_dispositivo;
        $os->sn = $request->sn;

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

            $cliente = $os->cliente;
            return redirect()->route('os.index')->with('success', 'OS <strong>' . $os->id . '</strong> criada com sucesso para o cliente <strong>' . $cliente->nome . '</strong>.');
        }

        return back()->with('error', 'Erro ao criar a OS.');
    }

    public function edit($id)
    {
        $os = OS::findOrFail($id);

        // Converte os campos acessórios e fotos para arrays
        $os->acessorios = explode(',', $os->acessorios);
        $os->fotos = explode(',', $os->fotos);

        return view('content.os.editar', [
            'clientes' => Clientes::all(),
            'tipos' => OS::TIPOS_DE_EQUIPAMENTO,
            'os' => $os // Passa a instância de OS com acessórios e fotos já convertidos
        ]);
    }


    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'cliente_id' => 'required',
            'tipo_id' => 'required',
            'data_de_entrada' => 'required|date',
            'prazo_entrega' => 'required|date|after:data_de_entrada',
            'problema_item' => 'required|string|max:255',
            'avarias' => 'nullable|string|max:255',
            'acessorios' => 'nullable|array',
            'senha_do_dispositivo' => 'nullable|string|max:255',
            'modelo_do_dispositivo' => 'nullable|string|max:255',
            'sn' => 'nullable|string|max:255',
            'fotos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
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
        $os->usuario_id = $request->usuario_id;

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

        return redirect()->route('os.index')->with('success', 'OS atualizada com sucesso!');
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
