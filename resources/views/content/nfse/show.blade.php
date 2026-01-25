@extends('layouts.layoutMaster')

@section('title', 'Detalhes da NFS-e')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0 text-primary">
            <i class="bx bx-file"></i> Detalhes da NFS-e #{{ $nfse->numero_nfse ?? $nfse->id }}
        </h1>
        <div>
            <a href="{{ route('nfse.index') }}" class="btn btn-outline-secondary me-2">
                <i class="bx bx-arrow-back"></i> Voltar
            </a>

            @if ($nfse->status != 'autorizada' && $nfse->status != 'cancelada' && $nfse->status != 'processando')
                <a href="{{ route('nfse.edit', $nfse->id) }}" class="btn btn-outline-primary me-2">
                    <i class="bx bx-edit-alt me-1"></i> Editar
                </a>
            @endif

            @if ($nfse->status == 'pendente' || $nfse->status == 'rejeitada')
                <form action="{{ route('nfse.emitir', $nfse->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-send me-1"></i> Emitir Agora
                    </button>
                </form>
            @endif

            @if ($nfse->status == 'autorizada')
                <a href="{{ route('nfse.pdf', $nfse->id) }}" target="_blank" class="btn btn-outline-primary me-2">
                    <i class="bx bx-file-pdf me-1"></i> Baixar PDF
                </a>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalCancelarShow">
                    <i class="bx bx-block me-1"></i> Cancelar
                </button>
            @endif
        </div>
    </div>

    {{-- Modal de Cancelamento --}}
    @if ($nfse->status == 'autorizada')
        <div class="modal fade" id="modalCancelarShow" tabindex="-1" aria-hidden="true">
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
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
                            <button type="submit" class="btn btn-danger">Confirmar Cancelamento</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Informações Gerais</h5>
                    @php
                        $statusColor = match ($nfse->status) {
                            'autorizada' => 'success',
                            'rejeitada' => 'danger',
                            'cancelada' => 'warning',
                            'processando' => 'info',
                            default => 'secondary',
                        };
                    @endphp
                    <span class="badge bg-{{ $statusColor }} fs-6">{{ ucfirst($nfse->status) }}</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="fw-bold">Tomador (Cliente):</label>
                            <p>{{ $nfse->cliente->nome ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold">CPF/CNPJ:</label>
                            <p>{{ $nfse->cliente->cpf_cnpj ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold">Número NFS-e:</label>
                            <p>{{ $nfse->numero_nfse ?? 'Não gerado' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold">Código de Verificação:</label>
                            <p>{{ $nfse->chave_acesso ?? 'Não gerado' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold">Data de Emissão:</label>
                            <p>{{ $nfse->data_emissao ? $nfse->data_emissao->format('d/m/Y H:i:s') : $nfse->created_at->format('d/m/Y H:i:s') }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold">RPS:</label>
                            <p>{{ $nfse->numero_rps ?? $nfse->id }}</p>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h6 class="mb-3">Serviço</h6>
                    <div class="row">
                        <div class="col-12">
                            <label class="fw-bold">Discriminação:</label>
                            <p class="text-justify" style="white-space: pre-wrap;">{{ $nfse->discriminacao }}</p>
                        </div>
                        <div class="col-md-6 mt-3">
                            <label class="fw-bold">Código Serviço (LC 116):</label>
                            <p>{{ $nfse->codigo_servico }}</p>
                        </div>
                    </div>
                </div>
            </div>

            @if ($nfse->motivo_rejeicao)
                <div class="alert alert-danger">
                    <h6 class="alert-heading fw-bold">Motivo da Rejeição:</h6>
                    <p class="mb-0">{{ $nfse->motivo_rejeicao }}</p>
                </div>
            @endif
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Valores</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            Valor Serviço
                            <span>R$ {{ number_format($nfse->valor_servico, 2, ',', '.') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            Alíquota ISS
                            <span>{{ number_format($nfse->aliquota_iss, 2, ',', '.') }}%</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            Valor ISS
                            <span>R$ {{ number_format($nfse->valor_iss, 2, ',', '.') }}</span>
                        </li>
                        @if ($nfse->iss_retido)
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 text-danger">
                                ISS Retido
                                <span>Sim</span>
                            </li>
                        @endif
                        <li
                            class="list-group-item d-flex justify-content-between align-items-center px-0 fw-bold fs-5 mt-2">
                            Valor Total
                            <span>R$ {{ number_format($nfse->valor_total, 2, ',', '.') }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            @if ($nfse->link_nfse)
                <div class="card mb-4">
                    <div class="card-body">
                        <a href="{{ $nfse->link_nfse }}" target="_blank" class="btn btn-label-primary w-100">
                            <i class="bx bx-download me-1"></i> Baixar/Imprimir NFS-e (Link Prefeitura)
                        </a>
                    </div>
                </div>
            @endif

            <div class="card mb-4">
                <div class="card-body">
                    <a href="{{ route('nfse.pdf', $nfse->id) }}" target="_blank" class="btn btn-label-secondary w-100">
                        <i class="bx bx-file-pdf me-1"></i> Baixar PDF (Interno)
                    </a>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Logs de Eventos</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Ação</th>
                                <th>Detalhes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                    <td>{{ $log->acao }}</td>
                                    <td>{{ $log->detalhes }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">Nenhum log encontrado para esta NFS-e.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


@endsection
