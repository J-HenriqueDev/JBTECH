@extends('layouts.layoutMaster')

@section('title', 'Contas a Pagar')

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="text-primary" style="font-size: 2.5rem; font-weight: bold; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);">
        <i class="bx bx-money-withdraw"></i> Contas a Pagar
    </h1>
    <a href="{{ route('contas-pagar.create') }}" class="btn btn-primary">
        <i class="fas fa-plus-circle me-1"></i> Nova Conta
    </a>
</div>

<!-- Estatísticas -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h6 class="card-title text-white">Pendente Total</h6>
                <h3>R$ {{ number_format($stats['total_pendente'], 2, ',', '.') }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h6 class="card-title text-white">Vence Hoje</h6>
                <h3>R$ {{ number_format($stats['total_hoje'], 2, ',', '.') }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h6 class="card-title text-white">Em Atraso</h6>
                <h3>R$ {{ number_format($stats['total_atrasado'], 2, ',', '.') }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">Listagem de Contas</h5>
            </div>

            <div class="card-body">
                <!-- Filtros -->
                <form method="GET" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <input type="text" name="search" class="form-control" placeholder="Pesquisar por descrição ou fornecedor" value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">Todos os Status</option>
                                <option value="pendente" {{ request('status') == 'pendente' ? 'selected' : '' }}>Pendente</option>
                                <option value="pago" {{ request('status') == 'pago' ? 'selected' : '' }}>Pago</option>
                                <option value="cancelado" {{ request('status') == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                        </div>
                    </div>
                </form>

                <div class="table-responsive text-nowrap">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Vencimento</th>
                                <th>Descrição</th>
                                <th>Fornecedor</th>
                                <th>Valor</th>
                                <th>Status</th>
                                <th>Origem</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($contas as $conta)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($conta->data_vencimento)->format('d/m/Y') }}</td>
                                <td>{{ $conta->descricao }}</td>
                                <td>{{ $conta->fornecedor ? $conta->fornecedor->nome : 'N/A' }}</td>
                                <td>R$ {{ number_format($conta->valor, 2, ',', '.') }}</td>
                                <td>
                                    @if($conta->status == 'pendente')
                                    <span class="badge bg-warning">Pendente</span>
                                    @elseif($conta->status == 'pago')
                                    <span class="badge bg-success">Pago</span>
                                    @elseif($conta->status == 'atrasado')
                                    <span class="badge bg-danger">Atrasado</span>
                                    @else
                                    <span class="badge bg-secondary">{{ ucfirst($conta->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($conta->origem == 'importacao_nfe')
                                    <span class="badge bg-label-info">Importação NFe</span>
                                    @else
                                    <span class="badge bg-label-primary">Manual</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('contas-pagar.edit', $conta->id) }}" class="btn btn-sm btn-icon btn-outline-primary" title="Editar"><i class="bx bx-edit-alt"></i></a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">Nenhuma conta encontrada.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $contas->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection