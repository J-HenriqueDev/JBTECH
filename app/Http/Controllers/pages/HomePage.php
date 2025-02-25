<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Clientes; // Modelo de Cliente
use App\Models\Produto; // Modelo de Produto
use App\Models\Orcamento; // Modelo de Orçamento
use App\Models\OS; // Modelo de Ordem de Serviço
use App\Models\Venda; // Modelo de Venda
use Carbon\Carbon; // Para manipulação de datas
use Illuminate\Support\Facades\DB;

class HomePage extends Controller
{
  public function index()
  {
      // Total de registros
      $totalClientes = Clientes::count();
      $totalProdutos = Produto::count();
      $totalOrcamentos = Orcamento::count();
      $totalVendas = Venda::count();

      // Ordens de serviço recentes (com paginação)
      $ordensRecentes = OS::with('cliente') // Carrega o relacionamento com cliente
          ->orderBy('created_at', 'desc')
          ->paginate(4, ['*'], 'ordens_page'); // Paginação com 5 itens por página

      // Orçamentos recentes (com paginação)
      $orcamentosRecentes = Orcamento::with('cliente') // Carrega o relacionamento com cliente
          ->orderBy('created_at', 'desc')
          ->paginate(4, ['*'], 'orcamentos_page'); // Paginação com 5 itens por página

      // Orçamentos próximos da validade (últimos 7 dias)
      $orcamentosProximosValidade = Orcamento::where('validade', '>=', Carbon::now())
          ->where('validade', '<=', Carbon::now()->addDays(7))
          ->with('cliente') // Carrega o relacionamento com cliente
          ->orderBy('validade', 'asc')
          ->paginate(4, ['*'], 'validade_page'); // Paginação com 5 itens por página

      // Dados para gráficos
      $vendasMensais = $this->getVendasMensais(); // Vendas dos últimos 12 meses
      $produtosMaisVendidos = $this->getProdutosMaisVendidos(); // Produtos mais vendidos

      // Vendas recentes (com paginação)
      $vendasRecentes = Venda::with('cliente') // Carrega o relacionamento com cliente
          ->orderBy('created_at', 'desc')
          ->paginate(4, ['*'], 'vendas_page'); // Paginação com 5 itens por página

      // Clientes recentes (com paginação)
      $clientesRecentes = Clientes::orderBy('created_at', 'desc')
          ->paginate(4, ['*'], 'clientes_page'); // Paginação com 5 itens por página

      // Passa os dados para a view
      return view('content.pages.pages-home', compact(
          'totalClientes',
          'totalProdutos',
          'totalOrcamentos',
          'totalVendas',
          'ordensRecentes',
          'orcamentosRecentes',
          'vendasRecentes',
          'clientesRecentes',
          'orcamentosProximosValidade',
          'vendasMensais',
          'produtosMaisVendidos'
      ));
  }
  function saudacao()
    {
        $hora = date('H');
        if ($hora >= 5 && $hora < 12) {
            return 'Bom dia';
        } elseif ($hora >= 12 && $hora < 18) {
            return 'Boa tarde';
        } else {
            return 'Boa noite';
        }
    }
    private function getVendasMensais()
    {
        $vendasMensais = [];
        $meses = [];

        for ($i = 11; $i >= 0; $i--) {
            $data = Carbon::now()->subMonths($i);
            $meses[] = $data->format('M'); // Nome do mês (ex: Jan, Fev)
            $vendasMensais[] = Venda::whereYear('created_at', $data->year)
                ->whereMonth('created_at', $data->month)
                ->count();
        }

        return [
            'meses' => $meses,
            'vendas' => $vendasMensais
        ];
    }

    /**
     * Retorna os produtos mais vendidos.
     */
    private function getProdutosMaisVendidos()
    {
        $produtos = Produto::withCount(['vendas as total_vendido' => function ($query) {
            $query->select(DB::raw('SUM(produto_venda.quantidade)'));
        }])
        ->orderByDesc('total_vendido')
        ->limit(5) // Limita a 5 produtos
        ->get();

        $nomes = $produtos->pluck('nome')->toArray();
        $quantidades = $produtos->pluck('total_vendido')->toArray();

        return [
            'nomes' => $nomes,
            'quantidades' => $quantidades
        ];
    }
}
