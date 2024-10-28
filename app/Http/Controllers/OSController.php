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
            'clientes' => Clientes::all() // Carregue seus clientes
        ]);
    }

    public function create()
    {
        // Retorna a view content.clientes.criar
        return view('content.os.criar', [
          'clientes' => Clientes::all(), // Carregue seus clientes
          'tipos' => OS::TIPOS_DE_EQUIPAMENTO // Passando os tipos para a view
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
            'acessorios' => 'nullable|array', // Para os acessórios
            'senha_do_dispositivo' => 'string|max:255',
            'fotos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // ex: máximo 2MB
        ]);

        // Criação da OS
        $os = new OS();
        $os->cliente_id = $request->cliente_id;
        $os->tipo_id = $request->tipo_id;
        $os->data_de_entrada = $request->data_de_entrada;
        $os->prazo_entrega = $request->prazo_entrega;
        $os->problema_item = $request->problema_item;
        $os->avarias = $request->avarias;
        $os->usuario_id = $request->usuario_id;

        // Captura dos acessórios
        if ($request->has('acessorios')) {
            $os->acessorios = implode(',', $request->acessorios); // Converte o array em string, se necessário
        } else {
            $os->acessorios = null; // Ou deixe como `Nenhum`, se preferir
        }

        $os->senha_do_dispositivo = $request->senha_do_dispositivo;


           // Salvar a OS
    if ($os->save()) {
      // Salvar fotos, se existirem
      if ($request->hasFile('fotos')) {
          $caminhos = []; // Array para armazenar os caminhos das fotos
          foreach ($request->file('fotos') as $foto) {
              // Salvar a foto e obter o caminho
              $caminho = $foto->store('fotos/os', 'public');
              $caminhos[] = $caminho; // Adicionar o caminho ao array
          }
          // Armazenar os caminhos como uma string separada por vírgula
          $os->fotos = implode(',', $caminhos);
          $os->save(); // Salvar novamente a OS com os caminhos das fotos

              // Pegue o cliente relacionado
          $cliente = $os->cliente; // Certifique-se que isso retorna o cliente correto

      }

       // Redireciona com a mensagem formatada
      return redirect()->route('os.index')->with('success', 'OS <strong>' . $os->id . '</strong> criada com sucesso para o cliente <strong>' . $cliente->nome . '</strong>.');
  }

  return back()->with('error', 'Erro ao criar a OS.');
}

public function edit($id)
{
    $os = OS::findOrFail($id); // Carrega a OS diretamente

    return view('content.os.editar', [
        'clientes' => Clientes::all(), // Carregue seus clientes
        'tipos' => OS::TIPOS_DE_EQUIPAMENTO, // Passando os tipos para a view
        'os' => $os, // Passa a instância de OS
        'acessorios'=> $os->acessorios = explode(',', $os->acessorios), // Converte a string em um array
        'fotos'=> $os->fotos = explode(',', $os->fotos)
         // Converte a string em um array
    ]);
}


public function update(Request $request, $id)
{
    // Validação dos dados do formulário
    $validator = Validator::make($request->all(), [
        'cliente_id' => 'required',
        'tipo_id' => 'required',
        'data_de_entrada' => 'required|date',
        'prazo_entrega' => 'required|date',
        'problema_item' => 'required|string',
        'avarias' => 'nullable|string',
        'fotos.*' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', // Validação para as fotos
    ]);

    if ($validator->fails()) {
        return redirect()->back()->withErrors($validator)->withInput();
    }

    // Atualização da OS
    $os = Os::findOrFail($id);
    $os->cliente_id = $request->cliente_id;
    $os->tipo_id = $request->tipo_id;
    $os->data_de_entrada = $request->data_de_entrada;
    $os->prazo_entrega = $request->prazo_entrega;
    $os->problema_item = $request->problema_item;
    $os->avarias = $request->avarias;
    $os->senha_do_dispositivo = $request->senha_do_dispositivo;
    $os->usuario_id = $request->usuario_id; // ID do usuário autenticado

    // Lógica para atualizar as fotos
    if ($request->hasFile('fotos')) {
        foreach ($request->file('fotos') as $file) {
            $path = $file->store('fotos', 'public'); // Salva as fotos na pasta public/fotos
            $os->fotos()->create(['path' => $path]); // Salva o caminho da foto no banco de dados
        }
    }

    $os->save(); // Salva a OS no banco de dados

    return redirect()->route('os.index')->with('success', 'OS atualizada com sucesso!');
}



public function search(Request $request)
{
    $search = $request->input('search');

    if ($search === 'all') {
        // Retorna todas as ordens de serviço
        $ordens = OS::with('cliente')->get();
    } else {
        // Realiza a busca
        $ordens = OS::with('cliente')
            ->where('id', 'like', "%{$search}%") // Busca pela ID da OS
            ->orWhereHas('cliente', function($query) use ($search) {
                $query->where('nome', 'like', "%{$search}%") // Correção aqui
                      ->orWhere('cpf_cnpj', 'like', "%{$search}%");
            })
            ->orWhere('tipo_id', 'like', "%{$search}%") // Se você quiser incluir a busca pelo tipo de equipamento
            ->get();
    }

    return response()->json($ordens);
  }
}
