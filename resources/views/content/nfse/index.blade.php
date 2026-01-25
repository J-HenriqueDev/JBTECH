@extends('layouts.layoutMaster')

@section('title', 'Notas Fiscais de Serviço (NFS-e)')

@section('content')

    @if (session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">
                <i class="bx bx-check-circle me-1"></i> Sucesso!
            </h6>
            <p class="mb-0">{!! session('success') !!}</p>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible" role="alert">
            <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">
                <i class="bx bx-error-circle me-1"></i> Erro!
            </h6>
            <p class="mb-0">{!! session('error') !!}</p>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0 text-primary">
            <i class="bx bx-receipt"></i> Notas Fiscais de Serviço (NFS-e)
        </h1>
        <a href="{{ route('nfse.create') }}" class="btn btn-primary">
            <i class="bx bx-plus-circle me-1"></i> Nova NFS-e
        </a>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">NFS-e Emitidas ({{ $notas->total() }})</h5>
                </div>
                <div class="card-body">
                    @if ($notas->isEmpty())
                        <div class="alert alert-info text-center">
                            <i class="bx bx-info-circle fs-1 d-block mb-2"></i>
                            <h5>Nenhuma NFS-e encontrada</h5>
                            <p class="mb-0">Clique em "Nova NFS-e" para começar.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Número RPS/NFS-e</th>
                                        <th>Cliente</th>
                                        <th>Valor Serviço</th>
                                        <th>ISS</th>
                                        <th>Data Emissão</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($notas as $nfse)
                                        <tr>
                                            <td>
                                                @if ($nfse->numero_nfse)
                                                    <span class="badge bg-label-primary">NFS-e:
                                                        {{ $nfse->numero_nfse }}</span>
                                                @else
                                                    <span class="badge bg-label-secondary">RPS: {{ $nfse->id }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $nfse->cliente->nome ?? 'N/A' }}</td>
                                            <td>R$ {{ number_format($nfse->valor_servico, 2, ',', '.') }}</td>
                                            <td>R$ {{ number_format($nfse->valor_iss, 2, ',', '.') }}</td>
                                            <td>{{ $nfse->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                @php
                                                    $statusColor = match ($nfse->status) {
                                                        'autorizada' => 'success',
                                                        'rejeitada' => 'danger',
                                                        'cancelada' => 'warning',
                                                        'processando' => 'info',
                                                        default => 'secondary',
                                                    };
                                                @endphp
                                                <span class="badge bg-{{ $statusColor }}">
                                                    {{ ucfirst($nfse->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="{{ route('nfse.show', $nfse->id) }}"
                                                        class="btn btn-icon btn-sm btn-info" title="Detalhes">
                                                        <i class="bx bx-show-alt"></i>
                                                    </a>

                                                    @if ($nfse->status != 'autorizada' && $nfse->status != 'cancelada' && $nfse->status != 'processando')
                                                        <a href="{{ route('nfse.edit', $nfse->id) }}"
                                                            class="btn btn-icon btn-sm btn-primary" title="Editar">
                                                            <i class="bx bx-edit-alt"></i>
                                                        </a>
                                                    @endif

                                                    @if ($nfse->status == 'pendente' || $nfse->status == 'rejeitada')
                                                        <form action="{{ route('nfse.emitir', $nfse->id) }}" method="POST"
                                                            class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-icon btn-sm btn-success"
                                                                title="Emitir Agora">
                                                                <i class="bx bx-send"></i>
                                                            </button>
                                                        </form>
                                                    @endif

                                                    @if ($nfse->status == 'autorizada')
                                                        <a href="{{ route('nfse.pdf', $nfse->id) }}" target="_blank"
                                                            class="btn btn-icon btn-sm btn-secondary" title="Baixar PDF">
                                                            <i class="bx bx-file-pdf"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-icon btn-sm btn-danger"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#modalCancelar{{ $nfse->id }}"
                                                            title="Cancelar NFS-e">
                                                            <i class="bx bx-x-circle"></i>
                                                        </button>
                                                    @endif

                                                    @if ($nfse->link_nfse)
                                                        <a href="{{ $nfse->link_nfse }}" target="_blank"
                                                            class="btn btn-icon btn-sm btn-outline-secondary"
                                                            title="Link Prefeitura">
                                                            <i class="bx bx-link-external"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $notas->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('modals')
        {{-- Modais de Cancelamento --}}
        @foreach ($notas as $nfse)
            @if ($nfse->status == 'autorizada')
                <div class="modal fade" id="modalCancelar{{ $nfse->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Cancelar NFS-e {{ $nfse->numero_nfse }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="{{ route('nfse.cancelar', $nfse->id) }}" method="POST">
                                @csrf
                                <div class="modal-body">
                                    <div class="alert alert-warning">
                                        <i class="bx bx-error me-1"></i> Atenção: O cancelamento é irreversível.
                                    </div>
                                    <div class="mb-3">
                                        <label for="codigo_cancelamento" class="form-label">Código de Cancelamento</label>
                                        <select class="form-select" name="codigo_cancelamento" required>
                                            <option value="1">1 - Erro na emissão</option>
                                            <option value="2">2 - Serviço não prestado</option>
                                            <option value="4">4 - Duplicidade da nota</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="motivo" class="form-label">Motivo do Cancelamento</label>
                                        <textarea class="form-control" name="motivo" rows="3" required minlength="15"
                                            placeholder="Descreva o motivo do cancelamento (mínimo 15 caracteres)"></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary"
                                        data-bs-dismiss="modal">Fechar</button>
                                    <button type="submit" class="btn btn-danger">Confirmar Cancelamento</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    @endpush

@endsection
