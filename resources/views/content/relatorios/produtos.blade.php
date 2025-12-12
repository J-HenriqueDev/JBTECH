@extends('layouts.layoutMaster')

@section('title', 'Relatório de Produtos')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0 text-primary" style="font-size: 2.5rem; font-weight: bold;">
        <i class="fas fa-box"></i> Relatório de Produtos
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
        <form method="GET" action="{{ route('relatorios.produtos') }}">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Categoria</label>
                    <select name="categoria_id" class="form-select">
                        <option value="">Todas</option>
                        @foreach(\App\Models\Categoria::all() as $categoria)
                        <option value="{{ $categoria->id }}" {{ request('categoria_id') == $categoria->id ? 'selected' : '' }}>
                            {{ $categoria->nome }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Estoque Mínimo</label>
                    <input type="number" name="estoque_minimo" class="form-control" value="{{ request('estoque_minimo', isset($estoqueMinimoPadrao) ? $estoqueMinimoPadrao : '') }}" placeholder="Ex: 10">
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

<div class="card mb-4">
    <div class="card-body">
        <h5>Valor Total em Estoque: <strong class="text-success">R$ {{ number_format($valorTotalEstoque ?? 0, 2, ',', '.') }}</strong></h5>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Produtos</h5>
        <form method="GET" action="{{ route('relatorios.produtos') }}" style="display:inline;" target="_blank">
            @foreach(request()->except('exportar') as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <input type="hidden" name="exportar" value="{{ isset($formatoPadrao) ? $formatoPadrao : 'pdf' }}">
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
                        <th>Nome</th>
                        <th>Categoria</th>
                        <th>Estoque</th>
                        <th>Preço Custo</th>
                        <th>Preço Venda</th>
                        <th>Lucro</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($produtos as $produto)
                    @php
                        $lucro = $produto->preco_venda - $produto->preco_custo;
                        $lucroPercentual = $produto->preco_custo > 0 ? (($lucro / $produto->preco_custo) * 100) : 0;
                    @endphp
                    <tr>
                        <td>#{{ $produto->id }}</td>
                        <td><strong>{{ $produto->nome }}</strong></td>
                        <td>{{ $produto->categoria->nome ?? 'N/A' }}</td>
                        <td>
                            <span class="badge bg-{{ $produto->estoque <= 10 ? 'danger' : ($produto->estoque <= 50 ? 'warning' : 'success') }}">
                                {{ $produto->estoque ?? 0 }}
                            </span>
                        </td>
                        <td>R$ {{ number_format($produto->preco_custo, 2, ',', '.') }}</td>
                        <td><strong>R$ {{ number_format($produto->preco_venda, 2, ',', '.') }}</strong></td>
                        <td>
                            <span class="badge bg-{{ $lucro >= 0 ? 'success' : 'danger' }}">
                                {{ number_format($lucroPercentual, 2, ',', '.') }}%
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">Nenhum produto encontrado</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
