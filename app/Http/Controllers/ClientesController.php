<?php

namespace App\Http\Controllers;

use App\Models\Clientes;
use App\Models\Enderecos;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ClientesController extends Controller
{
    public function index(Request $request)
    {
      $query = Clientes::query();

    if ($request->has('search') && $request->search != '') {
        $search = $request->search;
        $query->where(function ($query) use ($search) {
            $query->where('nome', 'LIKE', "%{$search}%")
                  ->orWhere('cpf_cnpj', 'LIKE', "%{$search}%");
        });
    }

    $clientes = $query->get();

    return view('content.clientes.listar', compact('clientes'));
    }

    public function create()
    {
        // Retorna a view content.clientes.criar
        return view('content.clientes.criar');
    }

    public function edit($id)
{
    // Busca o cliente pelo ID
    $cliente = Clientes::with('endereco')->findOrFail($id);

    // Retorna a view de edição com os dados do cliente
    return view('content.clientes.editar', compact('cliente'));
}

public function search(Request $request)
{
    $search = $request->input('search');

    if ($search === 'all') {
        // Retorna todos os clientes
        $clientes = Clientes::all();
    } else {
        // Realiza a busca
        $clientes = Clientes::where('nome', 'like', "%{$search}%")
                        ->orWhere('cpf_cnpj', 'like', "%{$search}%")
                        ->get();
    }

    return response()->json($clientes);
}


    public function store(Request $request)
    {
        // Remove as pontuações do CPF/CNPJ e do CEP
        $cpfCnpj = preg_replace('/\D/', '', $request->cpf); // Remove tudo que não é número
        $cep = preg_replace('/\D/', '', $request->cep); // Remove tudo que não é número

        // Verifica se é um CNPJ (14 dígitos) ou CPF (11 dígitos)
        $isCnpj = strlen($cpfCnpj) === 14;

        // Validação dos campos do formulário
        $request->validate([
            'nome' => 'required|string|max:255',
            'cpf' => [
                'required',
                'string',
                function($attribute, $value, $fail) use ($cpfCnpj, $isCnpj) {
                    if ($isCnpj && strlen($cpfCnpj) !== 14) {
                        $fail('O campo CNPJ deve ter 14 dígitos.');
                    } elseif (!$isCnpj && strlen($cpfCnpj) !== 11) {
                        $fail('O campo CPF deve ter 11 dígitos.');
                    }
                }
            ],
            'telefone' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'cep' => [
                'required',
                'string',
                function($attribute, $value, $fail) use ($cep) {
                    if (strlen($cep) !== 255) {
                        // $fail('O campo CEP deve ter 8 dígitos.');
                    }
                }
            ],
            'endereco' => 'required|string|max:255',
            'numero' => 'required|string|max:15',
            'bairro' => 'required|string|max:255',
            'cidade' => 'required|string|max:255',
            'estado' => 'required|string|max:2',
            'tipo_cliente' => 'required|string|max:50',
        ]);

        // Busca do endereço pelo CEP inserido (já sem pontuação)
        $endereco = $this->buscarEnderecoPorCep($cep);

        // Verifica se o endereço foi encontrado
        if ($endereco === null) {
            return redirect()->back()->with('noti', $this->createNotification('CEP não encontrado. Verifique o CEP digitado.', 'error'));
        }

        // Cadastra o endereço
        $endereco = Enderecos::create([
            'cep' => $cep,
            'endereco' => $request['endereco'],
            'numero' => $request->numero,
            'bairro' => $request['bairro'],
            'cidade' => $endereco['localidade'],
            'estado' => $endereco['uf'],
            'created_at' => Carbon::now()
        ]);

        // Verifica se o endereço foi criado
        if (!$endereco) {
            return redirect()->back()->with('noti', $this->createNotification('Erro ao cadastrar o endereço.', 'error'));
        }
        // dd($cpfCnpj);
        // Cadastra o cliente
        $cliente = Clientes::create([
          'nome' => $request->nome,
          'cpf_cnpj' => $cpfCnpj, // Aqui deve ser atribuído corretamente
          'telefone' => $request->telefone,
          'email' => $request->email,
          'endereco_id' => $endereco->id,
          'tipo_cliente' => $request->tipo_cliente,
          'created_at' => Carbon::now()
      ]);


        // Verifica se o cliente foi criado
        if (!$cliente) {
            return redirect()->back()->with('noti', $this->createNotification('Erro ao cadastrar o cliente.', 'error'));
        }

        // Retorno com mensagem de sucesso
        // session(['noti' => 'Cliente ' . $cliente->nome . ' cadastrado com sucesso!']);
        return redirect()->route('clientes.index')->with('noti', 'Cliente ' . $cliente->nome . ' cadastrado com sucesso!');

    }

    public function update(Request $request, $id)
    {
    // Remove a formatação do CPF antes de validar
    // Remove a formatação do CPF antes de validar
    $cpfCnpj = preg_replace('/[^0-9]/', '', $request->input('cpf'));
    $isCnpj = strlen($cpfCnpj) === 14;


// Debug: verifique se o CPF foi desformatado corretamente
// dd($cpfCnpj);


    // Validação dos dados recebidos
    $request->validate([
        'cpf' => [
                'required',
                'string',
                function($attribute, $value, $fail) use ($cpfCnpj, $isCnpj) {
                    if ($isCnpj && strlen($cpfCnpj) !== 14) {
                        $fail('O campo CNPJ deve ter 14 dígitos.');
                    } elseif (!$isCnpj && strlen($cpfCnpj) !== 11) {
                        $fail('O campo CPF deve ter 11 dígitos.');
                    }
                }
            ],
        'nome' => 'required|string|max:255',
        'inscricao_estadual' => 'nullable|string|max:20',
        'data_nascimento' => 'nullable|date',
        'telefone' => 'required|string|max:15',
        'email' => 'required|email|max:255',
        'cep' => 'required|string|max:10',
        'endereco' => 'required|string|max:255',
        'numero' => 'required|string|max:10',
        'bairro' => 'required|string|max:255',
        'cidade' => 'required|string|max:255',
        'estado' => 'required|string|max:2',
        'tipo_cliente' => 'required|boolean',
    ]);

    // Busca o cliente pelo ID
    $cliente = Clientes::with('endereco')->findOrFail($id);

    // Atualiza os dados do cliente
    $cliente->update($request->only(['nome', 'inscricao_estadual', 'data_nascimento', 'telefone', 'email', 'tipo_cliente']) + ['cpf' => $cpfCnpj]);

    // Atualiza os dados do endereço
    $cliente->endereco->update($request->only(['cep', 'endereco', 'numero', 'bairro', 'cidade', 'estado']));

    // Redireciona com uma notificação de sucesso
    // return redirect()->route('clientes.index')->with('noti', 'Cliente ' . $cliente->nome . ' atualizado com sucesso!');
    return redirect()->route('clientes.index')->with('noti', 'Cliente <strong>' . $cliente->nome . '</strong> atualizado com sucesso!');

}


    // Função para buscar endereço por CEP usando a API ViaCEP
    private function buscarEnderecoPorCep($cep)
    {
        if (!preg_match('/^[0-9]{8}$/', $cep)) {
            return null;
        }

        $client = new Client();
        $url = "https://viacep.com.br/ws/{$cep}/json/";

        try {
            $response = $client->get($url);
            $data = json_decode($response->getBody(), true);

            if (isset($data['erro'])) {
                return null;
            }

            return $data;
        } catch (\Exception $e) {
            Log::error('Erro ao buscar o CEP: ' . $e->getMessage());
            return null;
        }
    }

    // Função para criar notificações
    private function createNotification($message, $type)
    {
        return [
            'message' => $message,
            'alert-type' => $type
        ];
    }

}
