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

        // Estatísticas financeiras
        $totalVendasMes = Venda::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('valor_total');

        $totalCobrancasPendentes = \App\Models\Cobranca::where('status', 'pendente')->sum('valor');

        // Produtos em estoque baixo
        $produtosEstoqueBaixo = Produto::where('estoque', '<=', 10)->count();

        // Vendas do mês anterior para comparação
        $totalVendasMesAnterior = Venda::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->sum('valor_total');

        $crescimentoVendas = $totalVendasMesAnterior > 0
            ? (($totalVendasMes - $totalVendasMesAnterior) / $totalVendasMesAnterior) * 100
            : 0;

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
            ->where('status', 'pendente') // Apenas pendentes
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

        // Top 5 produtos em estoque baixo
        $produtosEstoqueBaixoLista = Produto::where('estoque', '<=', 10)
            ->orderBy('estoque', 'asc')
            ->limit(5)
            ->get();

        // Cobranças pendentes recentes
        $cobrancasPendentes = \App\Models\Cobranca::where('status', 'pendente')
            ->with('venda.cliente')
            ->orderBy('data_vencimento', 'asc')
            ->limit(5)
            ->get();

        // Vendas por método de pagamento
        $vendasPorMetodo = \App\Models\Cobranca::selectRaw('metodo_pagamento, COUNT(*) as total, SUM(valor) as valor_total')
            ->where('status', 'pago')
            ->groupBy('metodo_pagamento')
            ->get();

        // Saudação
        $saudacao = $this->saudacao();

        // Passa os dados para a view
        return view('content.pages.pages-home', compact(
            'saudacao',
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
            'produtosMaisVendidos',
            'totalVendasMes',
            'totalCobrancasPendentes',
            'produtosEstoqueBaixo',
            'crescimentoVendas',
            'produtosEstoqueBaixoLista',
            'cobrancasPendentes',
            'vendasPorMetodo'
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

        // Ensure locale is Portuguese for month names
        Carbon::setLocale('pt_BR');

        for ($i = 11; $i >= 0; $i--) {
            $data = Carbon::now()->subMonths($i);
            $meses[] = ucfirst($data->translatedFormat('M')); // Nome do mês (ex: Jan, Fev)
            $vendasMensais[] = Venda::whereYear('created_at', $data->year)
                ->whereMonth('created_at', $data->month)
                ->sum('valor_total');
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
