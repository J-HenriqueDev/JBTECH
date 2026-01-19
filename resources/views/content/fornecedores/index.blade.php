@extends('layouts.layoutMaster')

@section('title', 'Fornecedores')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="text-primary" style="font-size: 2.5rem; font-weight: bold; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);">
        <i class="bx bx-building-house"></i> Fornecedores
    </h1>
    <a href="{{ route('fornecedores.create') }}" class="btn btn-primary">
        <i class="fas fa-plus-circle me-1"></i> Novo Fornecedor
    </a>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">Listagem de Fornecedores</h5>
            </div>

            <div class="card-body">
                <!-- Filtros -->
                <form method="GET" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <input type="text" name="search" class="form-control" placeholder="Pesquisar por Nome ou CNPJ" value="{{ request('search') }}">
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
                                <th>CNPJ</th>
                                <th>Nome</th>
                                <th>Telefone</th>
                                <th>Cidade/UF</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($fornecedores as $fornecedor)
                            <tr>
                                <td>{{ $fornecedor->cnpj }}</td>
                                <td>{{ $fornecedor->nome }}</td>
                                <td>{{ $fornecedor->telefone }}</td>
                                <td>{{ $fornecedor->cidade }} / {{ $fornecedor->uf }}</td>
                                <td>
                                    {{-- <a href="{{ route('fornecedores.show', $fornecedor->id) }}" class="btn btn-sm btn-icon btn-outline-info" title="Ver Detalhes"><i class="bx bx-show"></i></a> --}}
                                    <a href="{{ route('fornecedores.edit', $fornecedor->id) }}" class="btn btn-sm btn-icon btn-outline-primary" title="Editar"><i class="bx bx-edit-alt"></i></a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">Nenhum fornecedor encontrado.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $fornecedores->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection