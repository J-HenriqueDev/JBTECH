@extends('layouts.layoutMaster')

@section('title', 'Relatórios')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0 text-primary" style="font-size: 2.5rem; font-weight: bold; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);">
        <i class="fas fa-file-alt"></i> Relatórios
    </h1>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card shadow h-100">
            <div class="card-body text-center">
                <i class="fas fa-shopping-cart fa-3x text-primary mb-3"></i>
                <h5 class="card-title">Relatório de Vendas</h5>
                <p class="card-text">Visualize todas as vendas realizadas com filtros por data e cliente.</p>
                <a href="{{ route('relatorios.vendas') }}" class="btn btn-primary">
                    <i class="fas fa-eye"></i> Ver Relatório
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card shadow h-100">
            <div class="card-body text-center">
                <i class="fas fa-box fa-3x text-success mb-3"></i>
                <h5 class="card-title">Relatório de Produtos</h5>
                <p class="card-text">Análise completa do estoque e produtos cadastrados.</p>
                <a href="{{ route('relatorios.produtos') }}" class="btn btn-success">
                    <i class="fas fa-eye"></i> Ver Relatório
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card shadow h-100">
            <div class="card-body text-center">
                <i class="fas fa-users fa-3x text-info mb-3"></i>
                <h5 class="card-title">Relatório de Clientes</h5>
                <p class="card-text">Lista completa de clientes cadastrados no sistema.</p>
                <a href="{{ route('relatorios.clientes') }}" class="btn btn-info">
                    <i class="fas fa-eye"></i> Ver Relatório
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card shadow h-100">
            <div class="card-body text-center">
                <i class="fas fa-money-check-alt fa-3x text-warning mb-3"></i>
                <h5 class="card-title">Relatório Financeiro</h5>
                <p class="card-text">Análise financeira com cobranças pendentes, pagas e canceladas.</p>
                <a href="{{ route('relatorios.financeiro') }}" class="btn btn-warning">
                    <i class="fas fa-eye"></i> Ver Relatório
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card shadow h-100">
            <div class="card-body text-center">
                <i class="fas fa-warehouse fa-3x text-danger mb-3"></i>
                <h5 class="card-title">Relatório de Estoque</h5>
                <p class="card-text">Análise detalhada do estoque com produtos em baixa.</p>
                <a href="{{ route('relatorios.estoque') }}" class="btn btn-danger">
                    <i class="fas fa-eye"></i> Ver Relatório
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card shadow h-100">
            <div class="card-body text-center">
                <i class="fas fa-chart-line fa-3x text-info mb-3"></i>
                <h5 class="card-title">Movimentações Semanais</h5>
                <p class="card-text">Acompanhe as movimentações de produtos por semana e itens mais vendidos.</p>
                <a href="{{ route('relatorios.movimentacoes') }}" class="btn btn-info">
                    <i class="fas fa-eye"></i> Ver Relatório
                </a>
            </div>
        </div>
    </div>
</div>

@endsection
