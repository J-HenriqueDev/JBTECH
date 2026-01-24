<?php

namespace App\Http\Controllers;

use App\Models\Clientes;
use App\Models\Enderecos;
use App\Models\Configuracao;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Validators\CpfCnpjValidator;
use App\Services\LogService;

class ClientesController extends Controller
{
    public function index(Request $request)
    {
        $query = Clientes::with('endereco');

        // Filtros
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($query) use ($search) {
                $query->where('nome', 'LIKE', "%{$search}%")
                    ->orWhere('cpf_cnpj', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('telefone', 'LIKE', "%{$search}%");
            });
        }

        if ($request->has('cidade') && $request->cidade != '') {
            $query->whereHas('endereco', function ($q) use ($request) {
                $q->where('cidade', 'LIKE', "%{$request->cidade}%");
            });
        }

        // Estatísticas
        $stats = [
            'total' => Clientes::count(),
            'total_vendas' => \App\Models\Venda::sum('valor_total'),
            'total_orcamentos' => \App\Models\Orcamento::count(),
        ];

        $clientes = $query->orderBy('nome')->paginate(15);

        return view('content.clientes.listar', compact('clientes', 'stats'));
    }

    public function show($id)
    {
        $cliente = Clientes::with([
            'endereco',
            'vendas' => function ($query) {
                $query->orderBy('created_at', 'desc')->limit(10);
            },
            'orcamentos' => function ($query) {
                $query->orderBy('created_at', 'desc')->limit(10);
            },
            'ordensServico' => function ($query) {
                $query->orderBy('created_at', 'desc')->limit(10);
            },
            'cobrancas' => function ($query) {
                $query->orderBy('cobrancas.created_at', 'desc')->limit(10);
            }
        ])->findOrFail($id);

        // Estatísticas do cliente
        $statsCliente = [
            'total_vendas' => $cliente->vendas->sum('valor_total'),
            'quantidade_vendas' => $cliente->vendas->count(),
            'total_orcamentos' => $cliente->orcamentos->count(),
            'total_os' => $cliente->ordensServico->count(),
            'cobrancas_pendentes' => $cliente->cobrancas->where('status', 'pendente')->sum('valor'),
            'cobrancas_pagas' => $cliente->cobrancas->where('status', 'pago')->sum('valor'),
        ];

        return view('content.clientes.show', compact('cliente', 'statsCliente'));
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

        // Validação dos campos do formulário com base nas configurações
        $exigirDocumento = \App\Models\Configuracao::get('clientes_exigir_documento', '0') == '1';
        $exigirEmail = \App\Models\Configuracao::get('clientes_exigir_email', '0') == '1';
        $exigirTelefone = \App\Models\Configuracao::get('clientes_exigir_telefone', '0') == '1';

        $rules = [
            'nome' => 'required|string|max:255',
            'cpf' => [
                $exigirDocumento ? 'required' : 'nullable',
                'string',
                function ($attribute, $value, $fail) use ($cpfCnpj, $isCnpj, $exigirDocumento) {
                    if ($exigirDocumento && empty($cpfCnpj)) {
                        $fail('O campo CPF/CNPJ é obrigatório.');
                        return;
                    }

                    if (!empty($cpfCnpj)) {
                        if ($isCnpj && strlen($cpfCnpj) !== 14) {
                            $fail('O campo CNPJ deve ter 14 dígitos.');
                        } elseif (!$isCnpj && strlen($cpfCnpj) !== 11) {
                            $fail('O campo CPF deve ter 11 dígitos.');
                        }

                        // Validação de CPF/CNPJ
                        if (!CpfCnpjValidator::validar($cpfCnpj)) {
                            $fail($isCnpj ? 'CNPJ inválido.' : 'CPF inválido.');
                        }

                        // Verifica se já existe
                        if (Clientes::where('cpf_cnpj', $cpfCnpj)->exists()) {
                            $fail($isCnpj ? 'Este CNPJ já está cadastrado.' : 'Este CPF já está cadastrado.');
                        }
                    }
                }
            ],
            'telefone' => $exigirTelefone ? 'required|string|max:255' : 'nullable|string|max:255',
            'email' => $exigirEmail ? 'required|email|max:255' : 'nullable|email|max:255',
            'cep' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($cep) {
                    if (strlen($cep) !== 8) {
                        $fail('O campo CEP deve ter 8 dígitos.');
                    }
                }
            ],
            'endereco' => 'required|string|max:255',
            'numero' => 'required|string|max:15',
            'bairro' => 'required|string|max:255',
            'cidade' => 'required|string|max:255',
            'estado' => 'required|string|max:2',
            'email_secundario' => 'nullable|email|max:255',
            'telefone_secundario' => 'nullable|string|max:255',
            'inscricao_municipal' => 'nullable|string|max:50',
            'indicador_ie' => 'nullable|integer',
        ];

        $request->validate($rules);

        $inscricaoEstadual = $request->inscricao_estadual;
        $uf = strtoupper($request->estado);
        if (!$this->validarInscricaoEstadualCliente($inscricaoEstadual, $uf)) {
            return redirect()->back()
                ->withErrors('A Inscrição Estadual é inválida para a UF informada. Use apenas números ou ISENTO.')
                ->withInput();
        }

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
            'cpf_cnpj' => $cpfCnpj,
            'telefone' => $request->telefone,
            'email' => $request->email,
            'endereco_id' => $endereco->id,
            'tipo_cliente' => $request->tipo_cliente ?? 0,
            'inscricao_estadual' => $request->inscricao_estadual ?? null,
            'data_nascimento' => $request->data_nascimento ?? null,
            'email_secundario' => $request->email_secundario,
            'telefone_secundario' => $request->telefone_secundario,
            'inscricao_municipal' => $request->inscricao_municipal,
            'indicador_ie' => $request->indicador_ie ?? 9,
            'suframa' => $request->suframa,
            'created_at' => Carbon::now()
        ]);

        // Nota: Limite de crédito será implementado quando o campo for adicionado à tabela

        LogService::registrar(
            'Cliente',
            'Criar',
            "Cliente '{$cliente->nome}' criado com sucesso"
        );


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
                function ($attribute, $value, $fail) use ($cpfCnpj, $isCnpj, $id) {
                    if ($isCnpj && strlen($cpfCnpj) !== 14) {
                        $fail('O campo CNPJ deve ter 14 dígitos.');
                    } elseif (!$isCnpj && strlen($cpfCnpj) !== 11) {
                        $fail('O campo CPF deve ter 11 dígitos.');
                    }

                    // Validação de CPF/CNPJ
                    if (!CpfCnpjValidator::validar($cpfCnpj)) {
                        $fail($isCnpj ? 'CNPJ inválido.' : 'CPF inválido.');
                    }

                    // Verifica se já existe (exceto o próprio registro)
                    if (Clientes::where('cpf_cnpj', $cpfCnpj)->where('id', '!=', $id)->exists()) {
                        $fail($isCnpj ? 'Este CNPJ já está cadastrado.' : 'Este CPF já está cadastrado.');
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
            'email_secundario' => 'nullable|email|max:255',
            'telefone_secundario' => 'nullable|string|max:255',
            'inscricao_municipal' => 'nullable|string|max:50',
            'indicador_ie' => 'nullable|integer',
        ]);

        $inscricaoEstadual = $request->inscricao_estadual;
        $uf = strtoupper($request->estado);
        if (!$this->validarInscricaoEstadualCliente($inscricaoEstadual, $uf)) {
            return redirect()->back()
                ->withErrors('A Inscrição Estadual é inválida para a UF informada. Use apenas números ou ISENTO.')
                ->withInput();
        }

        // Busca o cliente pelo ID
        $cliente = Clientes::with('endereco')->findOrFail($id);

        // Remove formatação do CEP
        $cep = preg_replace('/\D/', '', $request->cep);

        // Busca endereço atualizado pelo CEP
        $enderecoData = $this->buscarEnderecoPorCep($cep);

        // Atualiza os dados do cliente
        $cliente->update([
            'nome' => $request->nome,
            'cpf_cnpj' => $cpfCnpj,
            'telefone' => $request->telefone,
            'email' => $request->email,
            'tipo_cliente' => $request->tipo_cliente ?? 0,
            'inscricao_estadual' => $request->inscricao_estadual ?? null,
            'data_nascimento' => $request->data_nascimento ?? null,
            'email_secundario' => $request->email_secundario,
            'telefone_secundario' => $request->telefone_secundario,
            'inscricao_municipal' => $request->inscricao_municipal,
            'indicador_ie' => $request->indicador_ie ?? 9,
            'suframa' => $request->suframa,
        ]);

        // Atualiza os dados do endereço
        $cliente->endereco->update([
            'cep' => $cep,
            'endereco' => $request->endereco,
            'numero' => $request->numero,
            'bairro' => $request->bairro,
            'cidade' => $enderecoData ? $enderecoData['localidade'] : $request->cidade,
            'estado' => $enderecoData ? $enderecoData['uf'] : $request->estado,
        ]);

        LogService::registrar(
            'Cliente',
            'Atualizar',
            "Cliente '{$cliente->nome}' atualizado"
        );

        return redirect()->route('clientes.index')->with('noti', 'Cliente <strong>' . $cliente->nome . '</strong> atualizado com sucesso!');
    }

    protected function validarInscricaoEstadualCliente($inscricao, $uf)
    {
        if ($inscricao === null || $inscricao === '') {
            return true;
        }

        $valor = strtoupper(trim($inscricao));

        if ($valor === 'ISENTO') {
            return true;
        }

        $numeros = preg_replace('/[^0-9]/', '', $valor);

        if ($numeros === '') {
            return false;
        }

        $tamanho = strlen($numeros);

        $tamanhosPorUf = [
            'AC' => 13,
            'AL' => 9,
            'AP' => 9,
            'AM' => 9,
            'BA' => 8,
            'CE' => 9,
            'DF' => 13,
            'ES' => 9,
            'GO' => 9,
            'MA' => 9,
            'MT' => 11,
            'MS' => 8,
            'MG' => 13,
            'PA' => 9,
            'PB' => 9,
            'PR' => 10,
            'PE' => 14,
            'PI' => 9,
            'RJ' => 8,
            'RN' => 9,
            'RS' => 10,
            'RO' => 14,
            'RR' => 9,
            'SC' => 9,
            'SP' => 12,
            'SE' => 9,
            'TO' => 9,
        ];

        if (isset($tamanhosPorUf[$uf]) && $tamanho !== $tamanhosPorUf[$uf]) {
            return false;
        }

        if ($tamanho < 2 || $tamanho > 14) {
            return false;
        }

        return true;
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

    public function destroy($id)
    {
        $cliente = Clientes::findOrFail($id);

        // Verifica se há vendas, orçamentos ou OS associados
        if ($cliente->vendas()->count() > 0 || $cliente->orcamentos()->count() > 0 || $cliente->ordensServico()->count() > 0) {
            return redirect()->back()->with('error', 'Não é possível excluir este cliente pois possui vendas, orçamentos ou ordens de serviço associadas!');
        }

        $nome = $cliente->nome;
        $cliente->delete();

        LogService::registrar(
            'Cliente',
            'Excluir',
            "Cliente '{$nome}' excluído"
        );

        return redirect()->route('clientes.index')->with('noti', 'Cliente <strong>' . $nome . '</strong> excluído com sucesso!');
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
