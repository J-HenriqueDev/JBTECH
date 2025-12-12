<?php

namespace App\Http\Controllers;

use App\Models\Venda;
use App\Models\Produto;
use App\Models\Clientes;
use App\Models\Cobranca;
use App\Models\Orcamento;
use App\Models\Configuracao;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RelatorioController extends Controller
{
    /**
     * Obtém dados da empresa das configurações
     */
    private function getEmpresaData()
    {
        return [
            'nome' => Configuracao::get('empresa_nome', 'JBTECH Informática'),
            'cnpj' => Configuracao::get('empresa_cnpj', '54.819.910/0001-20'),
            'telefone' => Configuracao::get('empresa_telefone', '+55 (24) 98113-2097'),
            'email' => Configuracao::get('empresa_email', 'informatica.jbtech@gmail.com'),
            'endereco' => Configuracao::get('empresa_endereco', 'Rua Willy Faulstich'),
            'numero' => Configuracao::get('empresa_numero', '252'),
            'bairro' => Configuracao::get('empresa_bairro', 'Centro'),
            'cidade' => Configuracao::get('empresa_cidade', 'Resende'),
            'uf' => Configuracao::get('empresa_uf', 'RJ'),
            'cep' => Configuracao::get('empresa_cep', '27520-000'),
        ];
    }
    
    public function index()
    {
        return view('content.relatorios.index');
    }

    public function vendas(Request $request)
    {
        $query = Venda::with(['cliente', 'produtos', 'user']);

        // Aplica período padrão se não informado
        $periodoPadrao = Configuracao::get('relatorios_periodo_padrao', '30');
        if (!$request->filled('data_inicio')) {
            $request->merge(['data_inicio' => now()->subDays($periodoPadrao)->format('Y-m-d')]);
        }
        if (!$request->filled('data_fim')) {
            $request->merge(['data_fim' => now()->format('Y-m-d')]);
        }

        if ($request->filled('data_inicio')) {
            $query->whereDate('created_at', '>=', $request->data_inicio);
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('created_at', '<=', $request->data_fim);
        }

        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }

        $vendas = $query->orderBy('created_at', 'desc')->get();
        
        // Agrupa por vendedor se configurado
        $agruparVendedor = Configuracao::get('relatorios_agrupar_vendedor', '0') == '1';
        $vendasAgrupadas = null;
        if ($agruparVendedor) {
            $vendasAgrupadas = $vendas->groupBy('user_id');
        }
        
        $total = $vendas->sum('valor_total');
        $quantidade = $vendas->count();
        
        // Formato padrão de exportação
        $formatoPadrao = Configuracao::get('relatorios_formato_padrao', 'pdf');
        $imprimirCabecalho = Configuracao::get('relatorios_imprimir_cabecalho', '1') == '1';

        if ($request->filled('exportar') && $request->exportar === $formatoPadrao) {
            $empresa = $this->getEmpresaData();
            $pdf = Pdf::loadView('content.relatorios.vendas-pdf', compact('vendas', 'vendasAgrupadas', 'total', 'quantidade', 'empresa', 'imprimirCabecalho', 'agruparVendedor'));
            return $pdf->stream('relatorio-vendas-' . now()->format('Y-m-d') . '.pdf');
        }

        return view('content.relatorios.vendas', compact('vendas', 'vendasAgrupadas', 'total', 'quantidade', 'periodoPadrao', 'formatoPadrao', 'agruparVendedor'));
    }

    public function produtos(Request $request)
    {
        $query = Produto::with('categoria');

        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        // Aplica estoque mínimo padrão se não informado
        $estoqueMinimoPadrao = Configuracao::get('produtos_estoque_minimo', '10');
        $estoqueMinimo = $request->filled('estoque_minimo') ? $request->estoque_minimo : $estoqueMinimoPadrao;
        
        if ($estoqueMinimo) {
            $query->where('estoque', '<=', $estoqueMinimo);
        }

        $produtos = $query->orderBy('nome')->get();
        
        $valorTotalEstoque = $produtos->sum(function($p) {
            return $p->preco_custo * ($p->estoque ?? 0);
        });

        $formatoPadrao = Configuracao::get('relatorios_formato_padrao', 'pdf');
        $imprimirCabecalho = Configuracao::get('relatorios_imprimir_cabecalho', '1') == '1';
        
        if ($request->filled('exportar') && $request->exportar === $formatoPadrao) {
            $empresa = $this->getEmpresaData();
            $pdf = Pdf::loadView('content.relatorios.produtos-pdf', compact('produtos', 'valorTotalEstoque', 'empresa', 'imprimirCabecalho'));
            return $pdf->stream('relatorio-produtos-' . now()->format('Y-m-d') . '.pdf');
        }

        return view('content.relatorios.produtos', compact('produtos', 'valorTotalEstoque', 'estoqueMinimoPadrao', 'formatoPadrao'));
    }

    public function clientes(Request $request)
    {
        $query = Clientes::with('endereco');

        if ($request->filled('tipo_cliente')) {
            $query->where('tipo_cliente', $request->tipo_cliente);
        }

        $clientes = $query->orderBy('nome')->get();
        
        $totalVendas = Venda::whereIn('cliente_id', $clientes->pluck('id'))->sum('valor_total');

        $formatoPadrao = Configuracao::get('relatorios_formato_padrao', 'pdf');
        $imprimirCabecalho = Configuracao::get('relatorios_imprimir_cabecalho', '1') == '1';
        
        if ($request->filled('exportar') && $request->exportar === $formatoPadrao) {
            $empresa = $this->getEmpresaData();
            $pdf = Pdf::loadView('content.relatorios.clientes-pdf', compact('clientes', 'totalVendas', 'empresa', 'imprimirCabecalho'));
            return $pdf->stream('relatorio-clientes-' . now()->format('Y-m-d') . '.pdf');
        }

        return view('content.relatorios.clientes', compact('clientes', 'totalVendas'));
    }

    public function financeiro(Request $request)
    {
        $query = Cobranca::with(['venda.cliente']);

        if ($request->filled('data_inicio')) {
            $query->whereDate('created_at', '>=', $request->data_inicio);
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('created_at', '<=', $request->data_fim);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $cobrancas = $query->orderBy('created_at', 'desc')->get();
        
        $totalPendente = $cobrancas->where('status', 'pendente')->sum('valor');
        $totalPago = $cobrancas->where('status', 'pago')->sum('valor');
        $totalCancelado = $cobrancas->where('status', 'cancelado')->sum('valor');

        $formatoPadrao = Configuracao::get('relatorios_formato_padrao', 'pdf');
        $imprimirCabecalho = Configuracao::get('relatorios_imprimir_cabecalho', '1') == '1';
        
        if ($request->filled('exportar') && $request->exportar === $formatoPadrao) {
            $empresa = $this->getEmpresaData();
            $pdf = Pdf::loadView('content.relatorios.financeiro-pdf', compact('cobrancas', 'totalPendente', 'totalPago', 'totalCancelado', 'empresa', 'imprimirCabecalho'));
            return $pdf->stream('relatorio-financeiro-' . now()->format('Y-m-d') . '.pdf');
        }

        return view('content.relatorios.financeiro', compact('cobrancas', 'totalPendente', 'totalPago', 'totalCancelado', 'formatoPadrao'));
    }

    public function estoque(Request $request)
    {
        $produtos = Produto::with('categoria')
            ->orderBy('estoque', 'asc')
            ->get();
        
        $produtosBaixo = $produtos->where('estoque', '<=', 10);
        $produtosMedio = $produtos->where('estoque', '>', 10)->where('estoque', '<=', 50);
        $produtosAlto = $produtos->where('estoque', '>', 50);

        if ($request->filled('exportar') && $request->exportar === 'pdf') {
            $empresa = $this->getEmpresaData();
            $pdf = Pdf::loadView('content.relatorios.estoque-pdf', compact('produtos', 'produtosBaixo', 'produtosMedio', 'produtosAlto', 'empresa'));
            return $pdf->stream('relatorio-estoque-' . now()->format('Y-m-d') . '.pdf');
        }

        return view('content.relatorios.estoque', compact('produtos', 'produtosBaixo', 'produtosMedio', 'produtosAlto'));
    }
    
    public function movimentacoes(Request $request)
    {
        // Calcula movimentações semanais
        $dataInicio = $request->filled('data_inicio') 
            ? Carbon::parse($request->data_inicio) 
            : Carbon::now()->subWeeks(4)->startOfWeek();
        $dataFim = $request->filled('data_fim') 
            ? Carbon::parse($request->data_fim) 
            : Carbon::now()->endOfWeek();
        
        // Busca vendas no período
        $vendas = Venda::with(['produtos', 'cliente'])
            ->whereBetween('created_at', [$dataInicio, $dataFim])
            ->get();
        
        // Agrupa movimentações por semana e produto
        $movimentacoes = [];
        foreach ($vendas as $venda) {
            foreach ($venda->produtos as $produto) {
                $semana = Carbon::parse($venda->created_at)->format('Y-W');
                $key = $semana . '-' . $produto->id;
                
                if (!isset($movimentacoes[$key])) {
                    $movimentacoes[$key] = [
                        'produto' => $produto,
                        'semana' => Carbon::parse($venda->created_at)->startOfWeek()->format('d/m/Y'),
                        'quantidade' => 0,
                        'valor_total' => 0,
                        'vendas' => 0,
                    ];
                }
                
                $movimentacoes[$key]['quantidade'] += $produto->pivot->quantidade;
                $movimentacoes[$key]['valor_total'] += $produto->pivot->valor_total;
                $movimentacoes[$key]['vendas']++;
            }
        }
        
        // Ordena por semana e valor
        usort($movimentacoes, function($a, $b) {
            if ($a['semana'] == $b['semana']) {
                return $b['valor_total'] <=> $a['valor_total'];
            }
            return $a['semana'] <=> $b['semana'];
        });
        
        $totalMovimentado = collect($movimentacoes)->sum('valor_total');
        $totalItens = collect($movimentacoes)->sum('quantidade');
        
        if ($request->filled('exportar') && $request->exportar === 'pdf') {
            $empresa = $this->getEmpresaData();
            $pdf = Pdf::loadView('content.relatorios.movimentacoes-pdf', compact('movimentacoes', 'totalMovimentado', 'totalItens', 'dataInicio', 'dataFim', 'empresa'));
            return $pdf->stream('relatorio-movimentacoes-' . now()->format('Y-m-d') . '.pdf');
        }
        
        return view('content.relatorios.movimentacoes', compact('movimentacoes', 'totalMovimentado', 'totalItens', 'dataInicio', 'dataFim'));
    }
}
