<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\User;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        // Inicia a query com o relacionamento do usuário e ordena pelos mais recentes
        $query = Log::with('user')->latest();

        // Aplica o filtro de usuário, se presente
        if ($request->filled('usuario')) {
            $query->where('user_id', $request->usuario);
        }

        // Aplica o filtro de categoria, se presente
        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }

        // Aplica o filtro de ação, se presente (NOVO)
        if ($request->filled('acao')) {
            $query->where('acao', $request->acao);
        }

        // Aplica o filtro de busca nos detalhes, se presente
        if ($request->filled('detalhes')) {
            $query->where('detalhes', 'like', '%' . $request->detalhes . '%');
        }

        // Aplica o filtro de intervalo de datas, se presente
        if ($request->filled('data_inicial')) {
            $query->whereDate('created_at', '>=', $request->data_inicial);
        }
        if ($request->filled('data_final')) {
            $query->whereDate('created_at', '<=', $request->data_final);
        }

        // Busca os dados para preencher os filtros
        $usuarios = User::orderBy('name')->get();
        $categorias = Log::distinct()->pluck('categoria');
        $acoes = Log::distinct()->pluck('acao'); // (NOVO)

        // Pagina os resultados e mantém os parâmetros de filtro na URL
        $logs = $query->paginate($request->input('perPage', 10))->withQueryString();

        return view('content.Logs.index', compact('logs', 'usuarios', 'categorias', 'acoes'));
    }
}
