@extends('layouts.layoutMaster')

@section('title', 'Solicitações de Serviço')

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible" role="alert">
    <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">
        <i class="fas fa-check-circle me-1"></i> Sucesso!
    </h6>
    <p class="mb-0">{{ session('success') }}</p>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="text-primary" style="font-size: 2.5rem; font-weight: bold; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);">
        <i class="fas fa-headset"></i> Solicitações de Serviço
    </h1>
    <a href="{{ route('solicitacoes.create') }}" class="btn btn-primary">
        <i class="fas fa-plus-circle me-1"></i> Nova Solicitação
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Últimas Solicitações</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Canal</th>
                        <th>Tipo</th>
                        <th>Data/Hora</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($solicitacoes as $solicitacao)
                    <tr>
                        <td>{{ $solicitacao->id }}</td>
                        <td>
                            @if($solicitacao->cliente)
                                <a href="{{ route('clientes.show', $solicitacao->cliente->id) }}">
                                    <strong>{{ $solicitacao->cliente->nome }}</strong>
                                </a>
                            @else
                                <span class="text-muted">Cliente não encontrado</span>
                            @endif
                        </td>
                        <td>
                            @switch($solicitacao->canal_atendimento)
                                @case('WhatsApp') <i class="fab fa-whatsapp text-success"></i> @break
                                @case('Ligação') <i class="fas fa-phone"></i> @break
                                @case('Email') <i class="fas fa-envelope"></i> @break
                                @case('Balcão') <i class="fas fa-store"></i> @break
                                @default <i class="fas fa-comment"></i>
                            @endswitch
                            {{ $solicitacao->canal_atendimento }}
                        </td>
                        <td>
                            @if($solicitacao->tipo_atendimento == 'Remoto')
                                <span class="badge bg-info"><i class="fas fa-laptop-house"></i> Remoto</span>
                            @else
                                <span class="badge bg-warning"><i class="fas fa-user-tie"></i> Presencial</span>
                            @endif
                        </td>
                        <td>
                            {{ $solicitacao->data_solicitacao->format('d/m/Y H:i') }}
                        </td>
                        <td>
                            @switch($solicitacao->status)
                                @case('pendente') <span class="badge bg-warning">Pendente</span> @break
                                @case('em_andamento') <span class="badge bg-primary">Em Andamento</span> @break
                                @case('concluido') <span class="badge bg-success">Concluído</span> @break
                                @case('cancelado') <span class="badge bg-danger">Cancelado</span> @break
                                @default <span class="badge bg-secondary">{{ $solicitacao->status }}</span>
                            @endswitch
                        </td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('solicitacoes.edit', $solicitacao->id) }}"><i class="bx bx-edit-alt me-1"></i> Editar</a>
                                    <form action="{{ route('solicitacoes.destroy', $solicitacao->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item"><i class="bx bx-trash me-1"></i> Excluir</button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">Nenhuma solicitação encontrada.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-center mt-3">
            {{ $solicitacoes->links() }}
        </div>
    </div>
</div>
@endsection
