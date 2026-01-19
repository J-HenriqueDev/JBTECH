@extends('layouts.layoutMaster')

@section('title', 'Relatório de Vendas')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('page-script')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            width: '100%',
            placeholder: 'Selecione um cliente',
            allowClear: true
        });
    });
</script>
@endsection

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0 text-primary" style="font-size: 2.5rem; font-weight: bold;">
        <i class="fas fa-shopping-cart"></i> Relatório de Vendas
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
        <form method="GET" action="{{ route('relatorios.vendas') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Data Início</label>
                    <input type="date" name="data_inicio" class="form-control" value="{{ request('data_inicio', isset($periodoPadrao) ? now()->subDays($periodoPadrao)->format('Y-m-d') : '') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Data Fim</label>
                    <input type="date" name="data_fim" class="form-control" value="{{ request('data_fim', date('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Cliente</label>
                    <select name="cliente_id" class="form-select select2">
                        <option value="">Todos</option>
                        @foreach(\App\Models\Clientes::all() as $cliente)
                        <option value="{{ $cliente->id }}" {{ request('cliente_id') == $cliente->id ? 'selected' : '' }}>
                            #{{ $cliente->id }} - {{ $cliente->nome }} - {{ $cliente->cpf_cnpj }}
                        </option>
                        @endforeach
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
    <div class="col-md-6">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6>Total de Vendas</h6>
                <h3>{{ $quantidade ?? 0 }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6>Valor Total</h6>
                <h3>R$ {{ number_format($total ?? 0, 2, ',', '.') }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Vendas</h5>
        <form method="GET" action="{{ route('relatorios.vendas') }}" style="display:inline;" target="_blank">
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
                        <th>Data</th>
                        <th>Produtos</th>
                        <th>Valor Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vendas as $venda)
                    <tr>
                        <td>#{{ $venda->id }}</td>
                        <td>{{ $venda->cliente->nome ?? 'N/A' }}</td>
                        <td>{{ \Carbon\Carbon::parse($venda->created_at)->format('d/m/Y H:i') }}</td>
                        <td>{{ $venda->produtos->count() }} item(ns)</td>
                        <td><strong>R$ {{ number_format($venda->valor_total, 2, ',', '.') }}</strong></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">Nenhuma venda encontrada</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection