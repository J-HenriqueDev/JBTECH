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


<div class="d-flex justify-content-between align-items-center">
    <h1>Lista de Clientes</h1>
    <a href="{{ route('clientes.create') }}" class="btn btn-primary">
        <i class="fas fa-plus-circle me-1"></i> Novo Cliente
    </a>
</div>


<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">Clientes Cadastrados</h5>
            </div>

            <div class="card-body">
              <!-- Formulário de pesquisa -->
                <form method="GET" class="mb-4">
                    <div class="input-group">
                        <input type="text" class="form-control" id="search-input" placeholder="Pesquisar por Nome ou CPF/CNPJ" aria-label="Pesquisar">
                    </div>
                </form>
                <div class="table-responsive text-nowrap">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>CPF/CNPJ</th>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Telefone</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody id="clientes-table" class="table-border-bottom-0">
                            <!-- Os clientes serão inseridos aqui via JavaScript -->
                            @foreach($clientes as $cliente)
                                <tr>
                                    <td>{{ formatarCpfCnpj($cliente->cpf_cnpj) }}</td>
                                    <td>{{ $cliente->nome }}</td>
                                    <td>{{ $cliente->email }}</td>
                                    <td>{{ $cliente->telefone }}</td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="{{ route('clientes.edit', $cliente->id) }}">
                                                    <i class="bx bx-edit-alt me-1"></i> Editar
                                                </a>
                                                <button class="dropdown-item btn-delete" data-bs-toggle="modal" data-bs-target="#confirmDelete" data-id="{{ $cliente->id }}">
                                                    <i class="bx bx-trash me-1"></i> Excluir
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
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
  document.addEventListener('DOMContentLoaded', function () {
    let searchInput = document.getElementById('search-input');

    searchInput.addEventListener('keyup', function () {
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
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="/dashboard/clientes/${cliente.id}/edit">
                                                    <i class="bx bx-edit-alt me-1"></i> Editar
                                                </a>
                                                <button class="dropdown-item btn-delete" data-bs-toggle="modal" data-bs-target="#confirmDelete" data-id="${cliente.id}">
                                                    <i class="bx bx-trash me-1"></i> Excluir
                                                </button>
                                            </div>
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
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="/dashboard/clientes/${cliente.id}/edit">
                                                    <i class="bx bx-edit-alt me-1"></i> Editar
                                                </a>
                                                <button class="dropdown-item btn-delete" data-bs-toggle="modal" data-bs-target="#confirmDelete" data-id="${cliente.id}">
                                                    <i class="bx bx-trash me-1"></i> Excluir
                                                </button>
                                            </div>
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
