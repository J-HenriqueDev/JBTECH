@extends('layouts.layoutMaster')

@section('title', 'Relatório Financeiro')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0 text-primary" style="font-size: 2.5rem; font-weight: bold;">
        <i class="fas fa-money-check-alt"></i> Relatório Financeiro
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
        <form method="GET" action="{{ route('relatorios.financeiro') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Data Início</label>
                    <input type="date" name="data_inicio" class="form-control" value="{{ request('data_inicio') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Data Fim</label>
                    <input type="date" name="data_fim" class="form-control" value="{{ request('data_fim') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Todos</option>
                        <option value="pendente" {{ request('status') == 'pendente' ? 'selected' : '' }}>Pendente</option>
                        <option value="pago" {{ request('status') == 'pago' ? 'selected' : '' }}>Pago</option>
                        <option value="cancelado" {{ request('status') == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h6>Pendentes</h6>
                <h3>R$ {{ number_format($totalPendente ?? 0, 2, ',', '.') }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6>Pagas</h6>
                <h3>R$ {{ number_format($totalPago ?? 0, 2, ',', '.') }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h6>Canceladas</h6>
                <h3>R$ {{ number_format($totalCancelado ?? 0, 2, ',', '.') }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Cobranças</h5>
        <form method="GET" action="{{ route('relatorios.financeiro') }}" style="display:inline;" target="_blank">
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
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Método</th>
                        <th>Valor</th>
                        <th>Status</th>
                        <th>Vencimento</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cobrancas as $cobranca)
                    <tr>
                        <td>#{{ $cobranca->id }}</td>
                        <td>{{ $cobranca->venda->cliente->nome ?? 'N/A' }}</td>
                        <td>
                            <span class="badge bg-info">{{ strtoupper($cobranca->metodo_pagamento) }}</span>
                        </td>
                        <td><strong>R$ {{ number_format($cobranca->valor, 2, ',', '.') }}</strong></td>
                        <td>
                            <span class="badge bg-{{ $cobranca->status == 'pago' ? 'success' : ($cobranca->status == 'cancelado' ? 'danger' : 'warning') }}">
                                {{ ucfirst($cobranca->status) }}
                            </span>
                        </td>
                        <td>{{ $cobranca->data_vencimento ? \Carbon\Carbon::parse($cobranca->data_vencimento)->format('d/m/Y') : 'N/A' }}</td>
                        <td>{{ \Carbon\Carbon::parse($cobranca->created_at)->format('d/m/Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">Nenhuma cobrança encontrada</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
