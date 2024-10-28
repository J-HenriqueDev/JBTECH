@extends('layouts.layoutMaster')

@section('title', 'Ordens de Serviço')

@section('content')

@if(session('success'))
<div class="alert alert-primary alert-dismissible" role="alert">
  <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">
    <i class="fas fa-check-circle me-1"></i> Sucesso!
  </h6>
  <p class="mb-0">{!! session('success') !!}</p>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="d-flex justify-content-between align-items-center">
  <h1 class="mb-4 text-primary" style="font-size: 2.5rem; font-weight: bold; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);">
    <i class="fas fa-file-alt"></i> Ordens de Serviço
  </h1>
  <a href="{{ route('os.create') }}" class="btn btn-primary">
      <i class="fas fa-plus-circle me-1"></i> Nova OS
  </a>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">Ordens de Serviço Cadastradas</h5>
            </div>

            <div class="card-body">
                <!-- Formulário de pesquisa -->
                <form method="GET" action="{{ route('os.index') }}" class="mb-4">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" id="search-input" placeholder="Pesquisar por ID, Cliente ou Tipo" aria-label="Pesquisar">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
                <div class="table-responsive text-nowrap">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Tipo de Equipamento</th>
                                <th>Data de Entrada</th>
                                <th>Prazo de Entrega</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody id="os-table" class="table-border-bottom-0">
                            @foreach($ordens as $os)
                                <tr>
                                    <td><strong>{{ $os->id }}</strong></td>
                                    <td><strong>{{ $os->cliente->nome ?? 'Cliente não encontrado' }}</strong></td>
                                    <td>
                                        @switch($os->tipo_id)
                                            @case('COMPUTADOR')
                                                <i class="fas fa-desktop"></i> Computador
                                                @break
                                            @case('NOTEBOOK')
                                                <i class="fas fa-laptop"></i> Notebook
                                                @break
                                            @case('IMPRESSORA')
                                                <i class="fas fa-print"></i> Impressora
                                                @break
                                            @case('DVR')
                                                <i class="fas fa-video"></i> DVR
                                                @break
                                            @case('CAMERA')
                                                <i class="fas fa-camera"></i> Câmera
                                                @break
                                            @case('IMPRESSORA_TERMICA')
                                                <i class="fas fa-print"></i> Impressora Térmica
                                                @break
                                            @case('MACBOOK')
                                                <i class="fas fa-laptop"></i> MacBook
                                                @break
                                            @default
                                                <i class="fas fa-tools"></i> Outros
                                        @endswitch
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($os->data_de_entrada)->format('d/m/Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($os->prazo_entrega)->format('d/m/Y') }}</td>
                                    <td>{{ $os->status }}</td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="{{ route('os.edit', $os->id) }}">
                                                    <i class="bx bx-edit-alt me-1"></i> Editar
                                                </a>
                                                <button class="dropdown-item btn-delete" data-bs-toggle="modal" data-bs-target="#confirmDelete" data-id="{{ $os->id }}">
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
                <h5 class="modal-title" id="exampleModalLabel">Excluir OS</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Tem certeza de que deseja excluir esta Ordem de Serviço?
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

        fetch(`{{ route('dashboard.os.search') }}?search=${searchTerm}`)
            .then(response => response.json())
            .then(data => {
                let tableBody = document.getElementById('os-table');
                tableBody.innerHTML = ''; // Limpar a tabela antes de adicionar novos dados

                if (data.length > 0) {
                    data.forEach(os => {
                        tableBody.innerHTML += `
                            <tr>
                                <td><strong>${os.id}</strong></td>
                                <td><strong>${os.cliente.nome ?? 'Cliente não encontrado'}</strong></td>
                                <td>
                                    ${os.tipo_id == 'COMPUTADOR' ? '<i class="fas fa-desktop"></i> Computador' :
                                      os.tipo_id == 'NOTEBOOK' ? '<i class="fas fa-laptop"></i> Notebook' :
                                      os.tipo_id == 'IMPRESSORA' ? '<i class="fas fa-print"></i> Impressora' :
                                      os.tipo_id == 'DVR' ? '<i class="fas fa-video"></i> DVR' :
                                      os.tipo_id == 'CAMERA' ? '<i class="fas fa-camera"></i> Câmera' :
                                      os.tipo_id == 'IMPRESSORA_TERMICA' ? '<i class="fas fa-print"></i> Impressora Térmica' :
                                      os.tipo_id == 'MACBOOK' ? '<i class="fas fa-laptop"></i> MacBook' :
                                      '<i class="fas fa-tools"></i> Outros'}
                                </td>
                                <td>${new Date(os.data_de_entrada).toLocaleDateString('pt-BR')}</td>
                                <td>${new Date(os.prazo_entrega).toLocaleDateString('pt-BR')}</td>
                                <td>${os.status}</td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="/os/edit/${os.id}">
                                                <i class="bx bx-edit-alt me-1"></i> Editar
                                            </a>
                                            <button class="dropdown-item btn-delete" data-bs-toggle="modal" data-bs-target="#confirmDelete" data-id="${os.id}">
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
                            <td colspan="7" class="text-center">Nenhuma ordem de serviço encontrada.</td>
                        </tr>
                    `;
                }
            })
            .catch(error => console.error('Erro:', error));
    });

    // Para o modal de exclusão
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function () {
            const osId = this.getAttribute('data-id');
            document.getElementById('deleteForm').action = `/os/${osId}`;
        });
    });
});
</script>
@endsection
