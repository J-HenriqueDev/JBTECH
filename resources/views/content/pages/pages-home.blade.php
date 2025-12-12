@extends('layouts/layoutMaster')

@section('title', 'Dashboard')

@section('content')
<!-- Card de Fundo -->
<div class="card mb-4">
    <div class="card-body">
            <!-- Saudação Personalizada -->
            <h1 class="mb-4 text-primary" style="font-size: 2.5rem; font-weight: bold; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);">
                <i class="fas fa-smile"></i> {{ saudacao() }}, {{ auth()->user()->name }}!
            </h1>

            <!-- Primeira Linha: Cards de Resumo -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h6 class="card-title"><i class="fas fa-users"></i> Clientes</h6>
                            <h3>{{ $totalClientes }}</h3>
                            <p class="mb-2">cadastrados</p>
                            <a href="{{ route('clientes.index') }}" class="btn btn-light btn-sm">Ver clientes</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h6 class="card-title"><i class="fas fa-box"></i> Produtos</h6>
                            <h3>{{ $totalProdutos }}</h3>
                            <p class="mb-2">cadastrados</p>
                            @if(isset($produtosEstoqueBaixo) && $produtosEstoqueBaixo > 0)
                            <small class="d-block mb-2"><i class="fas fa-exclamation-triangle"></i> {{ $produtosEstoqueBaixo }} com estoque baixo</small>
                            @endif
                            <a href="{{ route('produtos.index') }}" class="btn btn-light btn-sm">Ver produtos</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h6 class="card-title"><i class="fas fa-dollar-sign"></i> Vendas do Mês</h6>
                            <h3>R$ {{ number_format($totalVendasMes ?? 0, 2, ',', '.') }}</h3>
                            @if(isset($crescimentoVendas))
                            <p class="mb-2">
                                <i class="fas fa-arrow-{{ $crescimentoVendas >= 0 ? 'up' : 'down' }}"></i> 
                                {{ number_format(abs($crescimentoVendas), 2) }}% vs mês anterior
                            </p>
                            @endif
                            <a href="{{ route('vendas.index') }}" class="btn btn-light btn-sm">Ver vendas</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h6 class="card-title"><i class="fas fa-money-check-alt"></i> Cobranças Pendentes</h6>
                            <h3>R$ {{ number_format($totalCobrancasPendentes ?? 0, 2, ',', '.') }}</h3>
                            <p class="mb-2">a receber</p>
                            <a href="{{ route('cobrancas.index') }}" class="btn btn-light btn-sm">Ver cobranças</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Segunda Linha: Gráficos -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5 class="card-title"><i class="fas fa-chart-line"></i> Vendas Mensais</h5>
                        </div>
                        <div class="card-body">
                            <div id="vendasMensaisChart"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title"><i class="fas fa-chart-bar"></i> Produtos Mais Vendidos</h5>
                        </div>
                        <div class="card-body">
                            <div id="produtosMaisVendidosChart"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Nova Linha: Produtos em Estoque Baixo e Cobranças Pendentes -->
            @if(isset($produtosEstoqueBaixoLista) && $produtosEstoqueBaixoLista->count() > 0)
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h5 class="card-title"><i class="fas fa-exclamation-triangle"></i> Produtos em Estoque Baixo</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group">
                                @foreach($produtosEstoqueBaixoLista as $produto)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $produto->nome }}</strong>
                                        <br><small class="text-muted">Categoria: {{ $produto->categoria->nome ?? 'N/A' }}</small>
                                    </div>
                                    <span class="badge bg-danger rounded-pill">{{ $produto->estoque }} unidades</span>
                                </li>
                                @endforeach
                            </ul>
                            <a href="{{ route('produtos.index') }}" class="btn btn-danger btn-sm mt-3 w-100">Ver Todos os Produtos</a>
                        </div>
                    </div>
                </div>
                @endif
                @if(isset($cobrancasPendentes) && $cobrancasPendentes->count() > 0)
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-warning text-white">
                            <h5 class="card-title"><i class="fas fa-clock"></i> Cobranças Pendentes</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group">
                                @foreach($cobrancasPendentes as $cobranca)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $cobranca->venda->cliente->nome ?? 'N/A' }}</strong>
                                        <br><small class="text-muted">Vencimento: {{ $cobranca->data_vencimento ? $cobranca->data_vencimento->format('d/m/Y') : 'N/A' }}</small>
                                    </div>
                                    <span class="badge bg-warning text-dark rounded-pill">R$ {{ number_format($cobranca->valor, 2, ',', '.') }}</span>
                                </li>
                                @endforeach
                            </ul>
                            <a href="{{ route('cobrancas.index') }}" class="btn btn-warning btn-sm mt-3 w-100">Ver Todas as Cobranças</a>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Terceira Linha: Listas de Tarefas/Alertas -->
            <div class="row mb-4">
                <!-- Clientes Recentes -->
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title"><i class="fas fa-user-plus"></i> Clientes Recentes</h5>
                            <a href="{{ route('clientes.create') }}" class="btn btn-light btn-sm">
                                <i class="fas fa-plus"></i> Novo Cliente
                            </a>
                        </div>
                        <div class="card-body">
                            <ul class="list-group">
                                @foreach($clientesRecentes->take(5) as $cliente)
                                <li class="list-group-item">
                                    <a href="{{ route('clientes.edit', $cliente->id) }}" class="text-decoration-none">
                                        <strong>#{{ $cliente->id }}</strong> - {{ $cliente->nome }}
                                        <span class="badge bg-primary float-end">{{ $cliente->created_at->format('d/m/Y') }}</span>
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                            @if ($clientesRecentes->count() > 5)
                            <div class="mt-3 text-center">
                                <a href="{{ route('clientes.index') }}" class="btn btn-primary btn-sm">
                                    Ver Todos os Clientes
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Orçamentos Recentes -->
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header bg-warning text-white">
                            <h5 class="card-title"><i class="fas fa-file-alt"></i> Orçamentos Recentes</h5>
                            <a href="{{ route('orcamentos.create') }}" class="btn btn-light btn-sm">
                                <i class="fas fa-plus"></i> Novo Orçamento
                            </a>
                        </div>
                        <div class="card-body">
                            <ul class="list-group">
                                @foreach($orcamentosRecentes->take(5) as $orcamento)
                                <li class="list-group-item">
                                    <a href="{{ route('orcamentos.edit', $orcamento->id) }}" class="text-decoration-none">
                                        <strong>#{{ $orcamento->id }}</strong> - {{ $orcamento->cliente->nome }}
                                        <span class="badge bg-warning text-dark float-end">{{ $orcamento->data ? $orcamento->data->format('d/m/Y') : 'N/A' }}</span>
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                            @if ($orcamentosRecentes->count() > 5)
                            <div class="mt-3 text-center">
                                <a href="{{ route('orcamentos.index') }}" class="btn btn-warning btn-sm">
                                    Ver Todos os Orçamentos
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Ordens de Serviço Recentes -->
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="card-title"><i class="fas fa-tasks"></i> OS Recentes</h5>
                            <a href="{{ route('os.create') }}" class="btn btn-light btn-sm">
                                <i class="fas fa-plus"></i> Nova OS
                            </a>
                        </div>
                        <div class="card-body">
                            <ul class="list-group">
                                @foreach($ordensRecentes->take(5) as $ordem)
                                <li class="list-group-item">
                                    <a href="{{ route('os.edit', $ordem->id) }}" class="text-decoration-none">
                                        <strong>#{{ $ordem->id }}</strong> - {{ $ordem->cliente->nome }}
                                        <span class="badge bg-secondary float-end">{{ $ordem->created_at->format('d/m/Y') }}</span>
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                            @if ($ordensRecentes->count() > 5)
                            <div class="mt-3 text-center">
                                <a href="{{ route('os.index') }}" class="btn btn-secondary btn-sm">
                                    Ver Todas as OS
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Vendas Recentes -->
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h5 class="card-title"><i class="fas fa-shopping-cart"></i> Vendas Recentes</h5>
                            <a href="{{ route('vendas.create') }}" class="btn btn-light btn-sm">
                                <i class="fas fa-plus"></i> Nova Venda
                            </a>
                        </div>
                        <div class="card-body">
                            <ul class="list-group">
                                @foreach($vendasRecentes->take(5) as $venda)
                                <li class="list-group-item">
                                    <a href="{{ route('vendas.edit', $venda->id) }}" class="text-decoration-none">
                                        <strong>#{{ $venda->id }}</strong> - {{ $venda->cliente->nome }}
                                        <span class="badge bg-danger float-end">{{ $venda->data_venda ? $venda->data_venda->format('d/m/Y') : 'N/A' }}</span>
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                            @if ($vendasRecentes->count() > 5)
                            <div class="mt-3 text-center">
                                <a href="{{ route('vendas.index') }}" class="btn btn-danger btn-sm">
                                    Ver Todas as Vendas
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quarta Linha: Links Rápidos -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title"><i class="fas fa-bolt"></i> Ações Rápidas</h5>
                        </div>
                        <div class="card-body">
                            <a href="{{ route('clientes.create') }}" class="btn btn-primary me-2"><i class="fas fa-user-plus"></i> Cadastrar Cliente</a>
                            <a href="{{ route('produtos.create') }}" class="btn btn-success me-2"><i class="fas fa-box"></i> Cadastrar Produto</a>
                            <a href="{{ route('orcamentos.create') }}" class="btn btn-warning me-2"><i class="fas fa-file-invoice-dollar"></i> Criar Orçamento</a>
                            <a href="{{ route('os.create') }}" class="btn btn-info me-2"><i class="fas fa-tasks"></i> Criar Ordem de Serviço</a>
                            <a href="{{ route('vendas.create') }}" class="btn btn-danger"><i class="fas fa-shopping-cart"></i> Registrar Venda</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Scripts para Gráficos (ApexCharts) -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    // Gráfico de Vendas Mensais
    const vendasMensaisChart = new ApexCharts(document.querySelector("#vendasMensaisChart"), {
        chart: {
            type: 'line',
            height: 350,
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 800,
            },
        },
        series: [{
            name: 'Vendas Mensais',
            data: @json($vendasMensais['vendas'])
        }],
        xaxis: {
            categories: @json($vendasMensais['meses'])
        },
        colors: ['#3b82f6'],
        stroke: {
            curve: 'smooth',
            width: 5,
        },
        tooltip: {
            theme: 'dark',
        },
    });
    vendasMensaisChart.render();

    // Gráfico de Produtos Mais Vendidos
    const produtosMaisVendidosChart = new ApexCharts(document.querySelector("#produtosMaisVendidosChart"), {
        chart: {
            type: 'bar',
            height: 350,
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 800,
            },
        },
        series: [{
            name: 'Quantidade Vendida',
            data: @json($produtosMaisVendidos['quantidades'])
        }],
        xaxis: {
            categories: @json($produtosMaisVendidos['nomes'])
        },
        colors: ['#8b5cf6'],
        tooltip: {
            theme: 'dark',
        },
    });
    produtosMaisVendidosChart.render();
</script>

<!-- Fontes Personalizadas -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
    body {
        font-family: 'Poppins', sans-serif;
    }
    .btn-light:hover {
        background-color: #f8f9fa;
        transform: translateY(-1px);
        transition: all 0.2s ease-in-out;
    }
</style>

<!-- Ícones do Font Awesome -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
@endsection
