@extends('layouts.layoutMaster')

@section('title', 'Relatório de Clientes')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0 text-primary" style="font-size: 2.5rem; font-weight: bold;">
        <i class="fas fa-users"></i> Relatório de Clientes
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
        <form method="GET" action="{{ route('relatorios.clientes') }}">
            <div class="row g-3">
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Atualizar Lista
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <h5>Total de Vendas dos Clientes: <strong class="text-success">R$ {{ number_format($totalVendas ?? 0, 2, ',', '.') }}</strong></h5>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Clientes ({{ $clientes->count() }})</h5>
        <form method="GET" action="{{ route('relatorios.clientes') }}" style="display:inline;" target="_blank">
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
                        <th>Nome</th>
                        <th>CPF/CNPJ</th>
                        <th>Email</th>
                        <th>Telefone</th>
                        <th>Cidade</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clientes as $cliente)
                    <tr>
                        <td>#{{ $cliente->id }}</td>
                        <td><strong>{{ $cliente->nome }}</strong></td>
                        <td>{{ formatarCpfCnpj($cliente->cpf_cnpj) }}</td>
                        <td>{{ $cliente->email }}</td>
                        <td>{{ $cliente->telefone }}</td>
                        <td>{{ $cliente->endereco->cidade ?? 'N/A' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">Nenhum cliente encontrado</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection