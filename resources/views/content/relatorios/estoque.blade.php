@extends('layouts.layoutMaster')

@section('title', 'Relatório de Estoque')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0 text-primary" style="font-size: 2.5rem; font-weight: bold;">
        <i class="fas fa-warehouse"></i> Relatório de Estoque
    </h1>
    <a href="{{ route('relatorios.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Voltar
    </a>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h6>Estoque Baixo (≤ 10)</h6>
                <h3>{{ $produtosBaixo->count() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h6>Estoque Médio (11-50)</h6>
                <h3>{{ $produtosMedio->count() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6>Estoque Alto (> 50)</h6>
                <h3>{{ $produtosAlto->count() }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Produtos por Nível de Estoque</h5>
        <form method="GET" action="{{ route('relatorios.estoque') }}" style="display:inline;" target="_blank">
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
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#baixo">Estoque Baixo</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#medio">Estoque Médio</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#alto">Estoque Alto</a>
            </li>
        </ul>
        <div class="tab-content mt-3">
            <div id="baixo" class="tab-pane fade show active">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Categoria</th>
                                <th>Estoque</th>
                                <th>Preço Venda</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($produtosBaixo as $produto)
                            <tr>
                                <td>#{{ $produto->id }}</td>
                                <td><strong>{{ $produto->nome }}</strong></td>
                                <td>{{ $produto->categoria->nome ?? 'N/A' }}</td>
                                <td><span class="badge bg-danger">{{ $produto->estoque }}</span></td>
                                <td>R$ {{ number_format($produto->preco_venda, 2, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">Nenhum produto com estoque baixo</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div id="medio" class="tab-pane fade">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Categoria</th>
                                <th>Estoque</th>
                                <th>Preço Venda</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($produtosMedio as $produto)
                            <tr>
                                <td>#{{ $produto->id }}</td>
                                <td><strong>{{ $produto->nome }}</strong></td>
                                <td>{{ $produto->categoria->nome ?? 'N/A' }}</td>
                                <td><span class="badge bg-warning">{{ $produto->estoque }}</span></td>
                                <td>R$ {{ number_format($produto->preco_venda, 2, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">Nenhum produto com estoque médio</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div id="alto" class="tab-pane fade">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Categoria</th>
                                <th>Estoque</th>
                                <th>Preço Venda</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($produtosAlto as $produto)
                            <tr>
                                <td>#{{ $produto->id }}</td>
                                <td><strong>{{ $produto->nome }}</strong></td>
                                <td>{{ $produto->categoria->nome ?? 'N/A' }}</td>
                                <td><span class="badge bg-success">{{ $produto->estoque }}</span></td>
                                <td>R$ {{ number_format($produto->preco_venda, 2, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">Nenhum produto com estoque alto</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
