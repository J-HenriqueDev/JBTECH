@extends('layouts.layoutMaster')

@section('title', 'Solicitações de Serviço')

@section('content')

    <style>
        .stepper-wrapper {
            margin-top: auto;
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            min-width: 150px;
        }

        .stepper-item {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;

            @media (max-width: 768px) {
                font-size: 12px;
            }
        }

        .stepper-item::before {
            position: absolute;
            content: "";
            border-bottom: 2px solid #ccc;
            width: 100%;
            top: 6px;
            /* half of height */
            left: -50%;
            z-index: 2;
        }

        .stepper-item::after {
            position: absolute;
            content: "";
            border-bottom: 2px solid #ccc;
            width: 100%;
            top: 6px;
            left: 50%;
            z-index: 2;
        }

        .stepper-item .step-counter {
            position: relative;
            z-index: 5;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #ccc;
            margin-bottom: 2px;
        }

        .stepper-item.active .step-counter {
            background-color: #696cff;
            /* Primary color */
        }

        .stepper-item.completed .step-counter {
            background-color: #71dd37;
            /* Success color */
        }

        .stepper-item.completed::after {
            position: absolute;
            content: "";
            border-bottom: 2px solid #71dd37;
            width: 100%;
            top: 6px;
            left: 50%;
            z-index: 3;
        }

        .stepper-item:first-child::before {
            content: none;
        }

        .stepper-item:last-child::after {
            content: none;
        }

        .stepper-item .step-name {
            font-size: 0.75rem;
            color: #a1acb8;
        }

        .stepper-item.active .step-name {
            color: #696cff;
            font-weight: bold;
        }

        .stepper-item.completed .step-name {
            color: #71dd37;
            font-weight: bold;
        }
    </style>

    @if (session('success'))
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
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Atendente</th>
                            <th>Canal</th>
                            <th>Tipo</th>
                            <th>Progresso</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($solicitacoes as $solicitacao)
                            <tr>
                                <td>{{ $solicitacao->id }}</td>
                                <td>
                                    @if ($solicitacao->cliente)
                                        <a href="{{ route('clientes.show', $solicitacao->cliente->id) }}">
                                            <strong>{{ $solicitacao->cliente->nome }}</strong>
                                        </a>
                                        <br>
                                        <small
                                            class="text-muted">{{ $solicitacao->data_solicitacao->format('d/m/Y H:i') }}</small>
                                    @else
                                        <span class="text-muted">Cliente não encontrado</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($solicitacao->atendente)
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-xs me-2">
                                                <span class="avatar-initial rounded-circle bg-label-secondary">
                                                    {{ substr($solicitacao->atendente->name, 0, 2) }}
                                                </span>
                                            </div>
                                            <span>{{ $solicitacao->atendente->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @switch($solicitacao->canal_atendimento)
                                        @case('WhatsApp')
                                            <i class="fab fa-whatsapp text-success" title="WhatsApp"></i>
                                        @break

                                        @case('Ligação')
                                            <i class="fas fa-phone text-info" title="Ligação"></i>
                                        @break

                                        @case('Email')
                                            <i class="fas fa-envelope text-warning" title="Email"></i>
                                        @break

                                        @case('Balcão')
                                            <i class="fas fa-store text-primary" title="Balcão"></i>
                                        @break

                                        @default
                                            <i class="fas fa-comment"></i>
                                    @endswitch
                                </td>
                                <td>
                                    @if ($solicitacao->tipo_atendimento == 'Remoto')
                                        <span class="badge bg-label-info"><i class="fas fa-laptop-house"></i> Remoto</span>
                                    @else
                                        <span class="badge bg-label-warning"><i class="fas fa-user-tie"></i>
                                            Presencial</span>
                                    @endif
                                </td>
                                <td style="min-width: 200px;">
                                    @if ($solicitacao->status == 'cancelado')
                                        <span class="badge bg-danger w-100">CANCELADO</span>
                                    @else
                                        <div class="stepper-wrapper mb-0">
                                            <div class="stepper-item completed">
                                                <div class="step-counter" title="Recebido"></div>
                                            </div>
                                            <div
                                                class="stepper-item {{ $solicitacao->status == 'em_andamento' || $solicitacao->status == 'concluido' ? 'completed' : '' }}">
                                                <div class="step-counter" title="Em Andamento"></div>
                                            </div>
                                            <div
                                                class="stepper-item {{ $solicitacao->status == 'concluido' ? 'completed' : '' }}">
                                                <div class="step-counter" title="Finalizado"></div>
                                            </div>
                                        </div>
                                        <div class="text-center small mt-1">
                                            @switch($solicitacao->status)
                                                @case('pendente')
                                                    <span class="text-warning fw-bold">Pendente</span>
                                                @break

                                                @case('em_andamento')
                                                    <span class="text-primary fw-bold">Em Andamento</span>
                                                @break

                                                @case('concluido')
                                                    <span class="text-success fw-bold">Finalizado</span>
                                                @break
                                            @endswitch
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex">
                                        <!-- View Details Button -->
                                        <button type="button" class="btn btn-icon btn-label-info me-1 view-details-btn"
                                            data-bs-toggle="modal" data-bs-target="#modalDetalhesSolicitacao"
                                            data-details="{{ json_encode([
                                                'id' => $solicitacao->id,
                                                'cliente' => $solicitacao->cliente
                                                    ? [
                                                        'nome' => $solicitacao->cliente->nome,
                                                        'telefone' => $solicitacao->cliente->telefone,
                                                        'email' => $solicitacao->cliente->email,
                                                        'endereco' =>
                                                            $solicitacao->cliente->endereco && $solicitacao->cliente->endereco->endereco
                                                                ? $solicitacao->cliente->endereco->endereco .
                                                                    ', ' .
                                                                    $solicitacao->cliente->endereco->numero .
                                                                    ' - ' .
                                                                    $solicitacao->cliente->endereco->bairro .
                                                                    ', ' .
                                                                    $solicitacao->cliente->endereco->cidade .
                                                                    '/' .
                                                                    $solicitacao->cliente->endereco->estado
                                                                : null,
                                                    ]
                                                    : null,
                                                'atendente' => $solicitacao->atendente ? $solicitacao->atendente->name : null,
                                                'canal' => $solicitacao->canal_atendimento,
                                                'tipo' => $solicitacao->tipo_atendimento,
                                                'status' => $solicitacao->status,
                                                'descricao' => $solicitacao->descricao,
                                                'pendencias' => $solicitacao->pendencias,
                                                'data_solicitacao' => $solicitacao->data_solicitacao->format('d/m/Y H:i'),
                                            ]) }}">
                                            <i class="bx bx-show"></i>
                                        </button>

                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                data-bs-toggle="dropdown"><i
                                                    class="bx bx-dots-vertical-rounded"></i></button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item"
                                                    href="{{ route('solicitacoes.edit', $solicitacao->id) }}"><i
                                                        class="bx bx-edit-alt me-1"></i> Editar</a>
                                                <form action="{{ route('solicitacoes.destroy', $solicitacao->id) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('Tem certeza que deseja excluir?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item"><i
                                                            class="bx bx-trash me-1"></i> Excluir</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">Nenhuma solicitação encontrada</h5>
                                            <a href="{{ route('solicitacoes.create') }}" class="btn btn-primary mt-2">Criar
                                                Nova</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $solicitacoes->links() }}
            </div>
        </div>

        <!-- Modal Detalhes da Solicitação -->
        <div class="modal fade" id="modalDetalhesSolicitacao" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title text-white" id="modalDetalhesTitle">Detalhes da Solicitação</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <!-- Info Header -->
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center border-bottom pb-3">
                                    <div>
                                        <h6 class="text-muted mb-1">Solicitação #<span id="detailId"></span></h6>
                                        <h4 class="mb-0" id="detailCliente"></h4>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-label-primary fs-6" id="detailStatus"></span>
                                        <div class="text-muted small mt-1" id="detailData"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Cliente & Contato -->
                            <div class="col-md-6">
                                <div class="card shadow-none bg-transparent border border-secondary mb-3 h-100">
                                    <div class="card-body">
                                        <h6 class="card-title text-primary"><i class="bx bx-user me-2"></i>Informações do
                                            Cliente
                                        </h6>
                                        <p class="mb-1"><strong>Contato:</strong> <span id="detailContato"></span></p>
                                        <p class="mb-0"><strong>Endereço:</strong> <br> <span id="detailEndereco"
                                                class="text-muted"></span></p>
                                        <a href="#" target="_blank" id="btnGeoLocation"
                                            class="btn btn-sm btn-outline-primary mt-2 d-none">
                                            <i class="bx bx-map me-1"></i> Ver no Mapa
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Atendimento Info -->
                            <div class="col-md-6">
                                <div class="card shadow-none bg-transparent border border-secondary mb-3 h-100">
                                    <div class="card-body">
                                        <h6 class="card-title text-info"><i class="bx bx-support me-2"></i>Dados do
                                            Atendimento
                                        </h6>
                                        <p class="mb-1"><strong>Atendente:</strong> <span id="detailAtendente"></span></p>
                                        <p class="mb-1"><strong>Canal:</strong> <span id="detailCanal"></span></p>
                                        <p class="mb-0"><strong>Tipo:</strong> <span id="detailTipo"></span></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Descrição do Problema -->
                            <div class="col-12">
                                <div class="card shadow-none bg-transparent border border-warning mb-3">
                                    <div class="card-body">
                                        <h6 class="card-title text-warning"><i class="bx bx-error me-2"></i>Descrição do
                                            Problema
                                        </h6>
                                        <p class="mb-0 text-break" id="detailDescricao" style="white-space: pre-wrap;"></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Pendências (se houver) -->
                            <div class="col-12 d-none" id="divPendencias">
                                <div class="card shadow-none bg-transparent border border-danger">
                                    <div class="card-body">
                                        <h6 class="card-title text-danger"><i class="bx bx-list-ul me-2"></i>Pendências</h6>
                                        <p class="mb-0 text-break" id="detailPendencias" style="white-space: pre-wrap;"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Fechar</button>
                        <a href="#" id="btnEditarSolicitacao" class="btn btn-primary">Editar</a>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const modalDetalhes = document.getElementById('modalDetalhesSolicitacao');
                const viewButtons = document.querySelectorAll('.view-details-btn');

                viewButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const details = JSON.parse(this.dataset.details);

                        // Populate Modal
                        document.getElementById('detailId').textContent = details.id;
                        document.getElementById('detailCliente').textContent = details.cliente ? details
                            .cliente
                            .nome : 'Cliente não identificado';
                        document.getElementById('detailData').textContent = details.data_solicitacao;
                        document.getElementById('detailContato').textContent = details.cliente ? details
                            .cliente
                            .telefone + (details.cliente.email ? ' / ' + details.cliente.email : '') :
                            'N/A';

                        // Address & Geolocation
                        const enderecoElem = document.getElementById('detailEndereco');
                        const btnGeo = document.getElementById('btnGeoLocation');
                        if (details.cliente && details.cliente.endereco) {
                            enderecoElem.textContent = details.cliente.endereco;
                            const encodedAddress = encodeURIComponent(details.cliente.endereco);
                            btnGeo.href =
                                `https://www.google.com/maps/search/?api=1&query=${encodedAddress}`;
                            btnGeo.classList.remove('d-none');
                        } else {
                            enderecoElem.textContent = 'Endereço não cadastrado';
                            btnGeo.classList.add('d-none');
                        }

                        // Atendimento
                        document.getElementById('detailAtendente').textContent = details.atendente ||
                            'Não atribuído';
                        document.getElementById('detailCanal').textContent = details.canal;
                        document.getElementById('detailTipo').textContent = details.tipo;

                        // Status styling
                        const statusElem = document.getElementById('detailStatus');
                        let statusText = '';
                        let statusClass = 'bg-label-secondary';

                        switch (details.status) {
                            case 'pendente':
                                statusText = 'Pendente';
                                statusClass = 'bg-label-warning';
                                break;
                            case 'em_andamento':
                                statusText = 'Em Andamento';
                                statusClass = 'bg-label-primary';
                                break;
                            case 'concluido':
                                statusText = 'Finalizado';
                                statusClass = 'bg-label-success';
                                break;
                            case 'cancelado':
                                statusText = 'Cancelado';
                                statusClass = 'bg-label-danger';
                                break;
                            default:
                                statusText = details.status;
                        }
                        statusElem.textContent = statusText;
                        statusElem.className = `badge ${statusClass} fs-6`;

                        // Descrição
                        document.getElementById('detailDescricao').textContent = details.descricao ||
                            'Sem descrição.';

                        // Pendências
                        const pendenciasElem = document.getElementById('detailPendencias');
                        const divPendencias = document.getElementById('divPendencias');
                        if (details.pendencias) {
                            pendenciasElem.textContent = details.pendencias;
                            divPendencias.classList.remove('d-none');
                        } else {
                            divPendencias.classList.add('d-none');
                        }

                        // Edit Link
                        const btnEdit = document.getElementById('btnEditarSolicitacao');
                        btnEdit.href = `/solicitacoes/${details.id}/edit`;
                    });
                });
            });
        </script>
    @endsection
