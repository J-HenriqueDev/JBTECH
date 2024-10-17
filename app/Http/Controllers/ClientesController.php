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
    public function index()
    {
        return view('content.clientes.criar');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'cpf' => 'required|string|max:14', // Exemplo: formato do CPF
            'telefone' => 'required|string|max:15', // Exemplo: formato do telefone
            'email' => 'required|email|max:255',
            'cep' => 'required|string|size:8',
            'endereco' => 'required|string|max:255',
            'numero' => 'required|string|max:10',
            'bairro' => 'required|string|max:255',
            'cidade' => 'required|string|max:255',
            'estado' => 'required|string|max:2', // Exemplo: formato do estado
            'tipo_cliente' => 'required|string|max:50',
        ]);

        $endereco = $this->buscarEnderecoPorCep($request->cep);

        if ($endereco === null) {
            return redirect()->back()->with('noti', $this->createNotification('CEP não encontrado. Verifique o CEP digitado.', 'error'));
        }

        $endereco = Enderecos::create([
            'cep' => $endereco['cep'],
            'endereco' => $endereco['logradouro'],
            'numero' => $request->numero,
            'bairro' => $endereco['bairro'],
            'cidade' => $endereco['localidade'],
            'estado' => $endereco['uf'],
            'created_at' => Carbon::now()
        ]);

        Clientes::create([
            'nome' => $request->nome,
            'cpf' => $request->cpf,
            'telefone' => $request->telefone,
            'email' => $request->email,
            'endereco_id' => $endereco->id,
            'tipo_cliente' => $request->tipo_cliente,
            'created_at' => Carbon::now()
        ]);

        return redirect()->back()->with('noti', $this->createNotification('Cliente inserido com sucesso!', 'success'));
    }

    private function buscarEnderecoPorCep($cep)
    {
        // Verifica se o CEP possui 8 dígitos numéricos
        if (!preg_match('/^[0-9]{8}$/', $cep)) {
            return null; // Retorna null se o CEP for inválido
        }

        $client = new Client();
        $url = "https://viacep.com.br/ws/{$cep}/json/";

        try {
            $response = $client->get($url);
            $data = json_decode($response->getBody(), true);

            if (isset($data['erro'])) {
                return null; // Retorna null se o CEP não for encontrado
            }

            return $data; // Retorna os dados do endereço
        } catch (\Exception $e) {
            Log::error('Erro ao buscar o CEP: ' . $e->getMessage());
            return null; // Retorna null em caso de erro na requisição
        }
    }

    private function createNotification($message, $type)
    {
        return [
            'message' => $message,
            'alert-type' => $type
        ];
    }
    // ClientesController.php
    public function consultaCnpj($cnpj)
    {
        // Verifica se o CNPJ possui 14 dígitos numéricos
        if (!preg_match('/^[0-9]{14}$/', $cnpj)) {
            return response()->json(['error' => 'CNPJ inválido.'], 400);
        }

        $client = new Client();
        $url = "https://www.receitaws.com.br/v1/cnpj/{$cnpj}?apikey=1b29f81577654fb241cffbfeacd302497b34374d640afcf7ececa33a022e42ca";

        try {
            $response = $client->get($url);
            $data = json_decode($response->getBody(), true);

            // Preencher os campos do cliente
            return response()->json([
                'fantasia' => $data['fantasia'] ?? null,
                'razao_social' => $data['razao_social'] ?? null,
                'cep' => $data['cep'] ?? null,
                'endereco' => $data['logradouro'] ?? null,
                'numero' => $data['numero'] ?? null, // Se o número estiver disponível
                'bairro' => $data['bairro'] ?? null,
                'cidade' => $data['municipio'] ?? null,
                'estado' => $data['uf'] ?? null,
                'telefone' => $data['telefone'] ?? null,
                'email' => $data['email'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao consultar CNPJ: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao consultar CNPJ.'], 500);
        }
    }


}
