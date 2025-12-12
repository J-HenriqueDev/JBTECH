@extends('layouts.layoutMaster')

@section('title', 'Relatório de Movimentações Semanais')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0 text-primary" style="font-size: 2.5rem; font-weight: bold;">
        <i class="fas fa-chart-line"></i> Relatório de Movimentações Semanais
    </h1>
    <a href="{{ route('relatorios.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Voltar
    </a>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Filtros</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('relatorios.movimentacoes') }}">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Data Início</label>
                    <input type="date" name="data_inicio" class="form-control" value="{{ request('data_inicio', $dataInicio->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Data Fim</label>
                    <input type="date" name="data_fim" class="form-control" value="{{ request('data_fim', $dataFim->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6>Total de Itens Movimentados</h6>
                <h3>{{ number_format($totalItens ?? 0, 0, ',', '.') }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6>Valor Total Movimentado</h6>
                <h3>R$ {{ number_format($totalMovimentado ?? 0, 2, ',', '.') }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Movimentações por Semana</h5>
        <form method="GET" action="{{ route('relatorios.movimentacoes') }}" style="display:inline;" target="_blank">
            @foreach(request()->except('exportar') as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <input type="hidden" name="exportar" value="pdf">
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-file-pdf"></i> Visualizar PDF
            </button>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Semana</th>
                        <th>Produto</th>
                        <th>Categoria</th>
                        <th>Quantidade</th>
                        <th>Nº de Vendas</th>
                        <th>Valor Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movimentacoes as $mov)
                    <tr>
                        <td><strong>{{ $mov['semana'] }}</strong></td>
                        <td>{{ $mov['produto']->nome }}</td>
                        <td>{{ $mov['produto']->categoria->nome ?? 'N/A' }}</td>
                        <td><span class="badge bg-info">{{ $mov['quantidade'] }}</span></td>
                        <td>{{ $mov['vendas'] }}</td>
                        <td><strong>R$ {{ number_format($mov['valor_total'], 2, ',', '.') }}</strong></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">Nenhuma movimentação encontrada no período</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection



