<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Clientes;

class OSController extends Controller
{
    public function index()
    {
        $clientes = ClienteS::orderBy('nome', 'ASC')->get();
        return view('os.criar', compact("clientes"));
    }

    public function store(Request $request)
        {
        //     $request->validate([
        //         'nome' => 'required',
        //         'cpf' => 'required',
        //         'telefone' => 'required',
        //         'email' => 'required',
        //         'cep' => 'required',
        //         'endereco' => 'required',
        //         'bairro' => 'required',
        //         'cidade' => 'required',
        //         'estado' => 'required',
        //         'tipo_cliente' => 'required',
        //     ]);

        //     $endereco =Endereco::create([
        //         'cep' => $request->cep,
        //         'endereco' => $request->endereco,
        //         'bairro' => $request->bairro,
        //         'cidade' => $request->cidade,
        //         'estado' => $request->estado,
        //         'created_at' => Carbon::now()
        //     ]);


        //     Cliente::insert([
        //         // 'user_id' => Auth::user()->id,
        //         'nome' => $request->nome,
        //         'cpf' => $request->cpf,
        //         'telefone' => $request->telefone,
        //         'email' => $request->email,
        //         'endereco_id' => $endereco->id,
        //         'tipo_cliente' => $request->tipo_cliente,
        //         'created_at' => Carbon::now()
        //     ]);

        //     // User::where('id', Auth::user()->id)->decrement('saldo', $valor);

        //     $noti = [
        //         'message' => 'Cliente inserido com sucesso!',
        //         'alert-type' => 'success'
        //     ];

        //     return redirect()->back()->with('noti', $noti);
        // }
           }   //
}
