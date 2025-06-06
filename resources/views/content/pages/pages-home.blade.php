@extends('layouts/layoutMaster')

@section('title', 'Dashboard')

@section('content')
<div class="card mb-4">
    <!-- Card de Fundo -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <!-- Saudação Personalizada -->
            <h1 class="mb-4 text-primary" style="font-size: 2.5rem; font-weight: bold; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);">
                <i class="fas fa-smile"></i> {{ saudacao() }}, {{ auth()->user()->name }}!
            </h1>

            <!-- Primeira Linha: Cards de Resumo -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white shadow">
                        <div class="card-body">
                            <h5 class="card-title text-white"><i class="fas fa-users text-white"></i> Clientes</h5>
                            <p class="card-text">{{ $totalClientes }} cadastrados</p>
                            <a href="{{ route('clientes.index') }}" class="btn btn-light">Ver clientes</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white shadow">
                        <div class="card-body">
                            <h5 class="card-title text-white"><i class="fas fa-box"></i> Produtos</h5>
                            <p class="card-text">{{ $totalProdutos }} cadastrados</p>
                            <a href="{{ route('produtos.index') }}" class="btn btn-light">Ver produtos</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white shadow">
                        <div class="card-body">
                            <h5 class="card-title text-white"><i class="fas fa-file-invoice-dollar"></i> Orçamentos</h5>
                            <p class="card-text">{{ $totalOrcamentos }} emitidos</p>
                            <a href="{{ route('orcamentos.index') }}" class="btn btn-light">Ver orçamentos</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white shadow">
                        <div class="card-body">
                            <h5 class="card-title text-white"><i class="fas fa-shopping-cart"></i> Vendas</h5>
                            <p class="card-text">{{ $totalVendas }} realizadas</p>
                            <a href="{{ route('vendas.index') }}" class="btn btn-light">Ver vendas</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Segunda Linha: Gráficos -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card shadow">
                        <div class="card-header bg-gradient-info text-white">
                            <h5 class="card-title text-white"><i class="fas fa-chart-line"></i> Vendas Mensais</h5>
                        </div>
                        <div class="card-body">
                            <div id="vendasMensaisChart"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow">
                        <div class="card-header bg-gradient-purple text-white">
                            <h5 class="card-title text-white"><i class="fas fa-chart-bar"></i> Produtos Mais Vendidos</h5>
                        </div>
                        <div class="card-body">
                            <div id="produtosMaisVendidosChart"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Terceira Linha: Listas de Tarefas/Alertas -->
            <div class="row mb-4">
                <!-- Clientes Recentes -->
                <div class="col-md-3 -bottom-3">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title text-white"><i class="fas fa-user-plus"></i> Clientes Recentes</h5>
                            <a href="{{ route('clientes.create') }}" class="btn btn-light btn-sm shadow-sm">
                                <i class="fas fa-plus"></i> Novo Cliente
                            </a>
                        </div>
                        <div class="card-body text-dark">
                            <ul class="list-group">
                                @foreach($clientesRecentes->take(5) as $cliente) <!-- Limita a 5 itens -->
                                <li class="list-group-item">
                                    <a href="{{ route('clientes.edit', $cliente->id) }}" class="text-decoration-none text-dark">
                                        <strong>#{{ $cliente->id }}</strong> - {{ $cliente->nome }}
                                        <span class="badge bg-primary float-end">{{ $cliente->created_at->format('d/m/Y') }}</span>
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                            <!-- Botão "Ver Todos" -->
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
                    <div class="card shadow">
                        <div class="card-header bg-warning text-white">
                            <h5 class="card-title text-white"><i class="fas fa-file-alt"></i> Orçamentos Recentes</h5>
                            <a href="{{ route('orcamentos.create') }}" class="btn btn-light btn-sm shadow-sm">
                                <i class="fas fa-plus"></i> Novo Orçamento
                            </a>
                        </div>
                        <div class="card-body text-dark">
                            <ul class="list-group">
                                @foreach($orcamentosRecentes->take(5) as $orcamento) <!-- Limita a 5 itens -->
                                <li class="list-group-item">
                                    <a href="{{ route('orcamentos.edit', $orcamento->id) }}" class="text-decoration-none text-dark">
                                        <strong>#{{ $orcamento->id }}</strong> - {{ $orcamento->cliente->nome }}
                                        <span class="badge bg-warning float-end">{{ \DateTime::createFromFormat('Y-m-d', $orcamento->data)->format('d/m/Y') }}</span>
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                            <!-- Botão "Ver Todos" -->
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
                    <div class="card shadow">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="card-title text-white"><i class="fas fa-tasks"></i> OS Recentes</h5>
                            <a href="{{ route('os.create') }}" class="btn btn-light btn-sm shadow-sm">
                                <i class="fas fa-plus"></i> Nova OS
                            </a>
                        </div>
                        <div class="card-body text-dark">
                            <ul class="list-group">
                                @foreach($ordensRecentes->take(5) as $ordem) <!-- Limita a 5 itens -->
                                <li class="list-group-item">
                                    <a href="{{ route('os.edit', $ordem->id) }}" class="text-decoration-none text-dark">
                                        <strong>#{{ $ordem->id }}</strong> - {{ $ordem->cliente->nome }}
                                        <span class="badge bg-secondary float-end">{{ $ordem->created_at->format('d/m/Y') }}</span>
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                            <!-- Botão "Ver Todos" -->
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
                    <div class="card shadow">
                        <div class="card-header bg-danger text-white">
                            <h5 class="card-title text-white"><i class="fas fa-tasks"></i> Vendas Recentes</h5>
                            <a href="{{ route('vendas.create') }}" class="btn btn-light btn-sm shadow-sm">
                                <i class="fas fa-plus"></i> Nova Venda
                            </a>
                        </div>
                        <div class="card-body text-dark">
                            <ul class="list-group">
                                @foreach($vendasRecentes->take(5) as $venda) <!-- Limita a 5 itens -->
                                <li class="list-group-item">
                                    <a href="{{ route('vendas.edit', $venda->id) }}" class="text-decoration-none text-dark">
                                        <strong>#{{ $venda->id }}</strong> - {{ $venda->cliente->nome }}
                                        <span class="badge bg-danger float-end">{{ \DateTime::createFromFormat('Y-m-d', $venda->data_venda)->format('d/m/Y') }}</span>
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                            <!-- Botão "Ver Todos" -->
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
                    <div class="card shadow">
                        <div class="card-header bg text-black">
                            <h5 class="card-title "><i class="fas fa-bolt"></i> Ações Rápidas</h5>
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
    .bg-gradient-primary {
        background: linear-gradient(45deg, #ffffff, #1d4ed8);
    }
    .bg-gradient-success {
        background: linear-gradient(45deg, #feffff, #059669);
    }
    .bg-gradient-warning {
        background: linear-gradient(45deg, #f59e0b, #d97706);
    }
    .bg-gradient-danger {
        background: linear-gradient(45deg, #ef4444, #dc2626);
    }
    .bg-gradient-info {
        background: linear-gradient(45deg, #06b6d4, #0e7490);
    }
    .bg-gradient-purple {
        background: linear-gradient(45deg, #8b5cf6, #7c3aed);
    }
    .bg-gradient-secondary {
        background: linear-gradient(45deg, #ffffff, #4b5563);
    }
</style>

<!-- Ícones do Font Awesome -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
@endsection
