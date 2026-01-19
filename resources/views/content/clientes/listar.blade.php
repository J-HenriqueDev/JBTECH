@extends('layouts.layoutMaster')

@section('title', 'Clientes')

@section('content')

@if(session('noti'))
<div class="alert alert-primary alert-dismissible" role="alert">
    <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">
        <i class="fas fa-check-circle me-1"></i> Sucesso!
    </h6>
    <p class="mb-0">{!! session('noti') !!}</p>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif


<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="text-primary" style="font-size: 2.5rem; font-weight: bold; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);">
        <i class="fas fa-users"></i> Clientes
    </h1>
    <a href="{{ route('clientes.create') }}" class="btn btn-primary">
        <i class="fas fa-plus-circle me-1"></i> Novo Cliente
    </a>
</div>

<!-- Estatísticas -->
@if(isset($stats))
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6 class="card-title">Total de Clientes</h6>
                <h3>{{ $stats['total'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6 class="card-title">Total em Vendas</h6>
                <h3>R$ {{ number_format($stats['total_vendas'], 2, ',', '.') }}</h3>
            </div>
        </div>
    </div>
</div>
@endif


<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">Clientes Cadastrados</h5>
            </div>

            <div class="card-body">
                <!-- Formulário de pesquisa e filtros -->
                <form method="GET" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" id="search-input" placeholder="Pesquisar por Nome, CPF/CNPJ, Email ou Telefone" value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="cidade" class="form-control" placeholder="Filtrar por Cidade" value="{{ request('cidade') }}">
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
                                <th>ID</th>
                                <th>CPF/CNPJ</th>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Telefone</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody id="clientes-table" class="table-border-bottom-0">
                            @forelse($clientes as $cliente)
                            <tr>
                                <td>{{ $cliente->id }}</td>
                                <td><strong>{{ formatarCpfCnpj($cliente->cpf_cnpj) }}</strong></td>
                                <td>
                                    <strong>{{\Illuminate\Support\Str::limit($cliente->nome, 40, '...') }}</strong>
                                </td>
                                <td>{{ $cliente->email }}</td>
                                <td>{{ $cliente->telefone }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('clientes.show', $cliente->id) }}" class="btn btn-sm btn-icon btn-outline-info" title="Ver Detalhes">
                                            <i class="bx bx-show"></i>
                                        </a>
                                        <a href="{{ route('clientes.edit', $cliente->id) }}" class="btn btn-sm btn-icon btn-outline-primary" title="Editar">
                                            <i class="bx bx-edit-alt"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-icon btn-outline-danger btn-delete" data-bs-toggle="modal" data-bs-target="#confirmDelete" data-id="{{ $cliente->id }}" title="Excluir">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">Nenhum cliente encontrado.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginação -->
                <div class="mt-4">
                    {{ $clientes->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmação para exclusão -->
<div class="modal fade" id="confirmDelete" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Excluir Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Tem certeza de que deseja excluir este cliente?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>

                <!-- Formulário de exclusão -->
                <form id="deleteForm" action="" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let searchInput = document.getElementById('search-input');

        searchInput.addEventListener('keyup', function() {
            let searchTerm = searchInput.value.trim();

            if (searchTerm.length === 0) {
                fetch(`{{ route('dashboard.clientes.search') }}?search=all`)
                    .then(response => response.json())
                    .then(data => {
                        let tableBody = document.getElementById('clientes-table');
                        tableBody.innerHTML = ''; // Limpar a tabela

                        if (data.length > 0) {
                            data.forEach(cliente => {
                                tableBody.innerHTML += `
                                <tr>
                                    <td>${cliente.cpf_cnpj}</td>
                                    <td>${cliente.nome}</td>
                                    <td>${cliente.email}</td>
                                    <td>${cliente.telefone}</td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="/dashboard/clientes/${cliente.id}" class="btn btn-sm btn-icon btn-outline-info" title="Ver Detalhes">
                                                <i class="bx bx-show"></i>
                                            </a>
                                            <a href="/dashboard/clientes/${cliente.id}/edit" class="btn btn-sm btn-icon btn-outline-primary" title="Editar">
                                                <i class="bx bx-edit-alt"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-icon btn-outline-danger btn-delete" data-bs-toggle="modal" data-bs-target="#confirmDelete" data-id="${cliente.id}" title="Excluir">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            `;
                            });
                        } else {
                            tableBody.innerHTML = `
                            <tr>
                                <td colspan="5" class="text-center">Nenhum cliente encontrado.</td>
                            </tr>
                        `;
                        }
                    })
                    .catch(error => console.error('Erro na busca:', error));
            } else {
                fetch(`{{ route('dashboard.clientes.search') }}?search=${searchTerm}`)
                    .then(response => response.json())
                    .then(data => {
                        let tableBody = document.getElementById('clientes-table');
                        tableBody.innerHTML = ''; // Limpar a tabela antes de adicionar novos dados

                        if (data.length > 0) {
                            data.forEach(cliente => {
                                tableBody.innerHTML += `
                                <tr>
                                    <td>${cliente.cpf_cnpj}</td>
                                    <td>${cliente.nome}</td>
                                    <td>${cliente.email}</td>
                                    <td>${cliente.telefone}</td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="/dashboard/clientes/${cliente.id}" class="btn btn-sm btn-icon btn-outline-info" title="Ver Detalhes">
                                                <i class="bx bx-show"></i>
                                            </a>
                                            <a href="/dashboard/clientes/${cliente.id}/edit" class="btn btn-sm btn-icon btn-outline-primary" title="Editar">
                                                <i class="bx bx-edit-alt"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-icon btn-outline-danger btn-delete" data-bs-toggle="modal" data-bs-target="#confirmDelete" data-id="${cliente.id}" title="Excluir">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            `;
                            });
                        } else {
                            tableBody.innerHTML = `
                            <tr>
                                <td colspan="5" class="text-center">Nenhum cliente encontrado.</td>
                            </tr>
                        `;
                        }
                    })
                    .catch(error => console.error('Erro na busca:', error));
            }
        });
    });
</script>

@endsection