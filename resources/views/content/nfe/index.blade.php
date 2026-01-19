@extends('layouts.layoutMaster')

@section('title', 'Notas Fiscais Eletrônicas')

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible" role="alert">
    <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">
        <i class="bx bx-check-circle me-1"></i> Sucesso!
    </h6>
    <p class="mb-0">{!! session('success') !!}</p>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('info'))
<div class="alert alert-info alert-dismissible" role="alert">
    <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">
        <i class="bx bx-info-circle me-1"></i> Informação!
    </h6>
    <p class="mb-0">{!! session('info') !!}</p>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0 text-primary">
        <i class="bx bx-receipt"></i> Notas Fiscais Eletrônicas
    </h1>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalInutilizar">
            <i class="bx bx-block me-1"></i> Inutilizar Numeração
        </button>
        <a href="{{ route('nfe.create-avulsa') }}" class="btn btn-secondary">
            <i class="bx bx-file me-1"></i> Emitir Avulsa
        </a>
        <a href="{{ route('nfe.create') }}" class="btn btn-primary">
            <i class="bx bx-plus-circle me-1"></i> Emitir NF-e
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">NF-e Emitidas ({{ $notasFiscais->total() }})</h5>
            </div>
            <div class="card-body">
                @if($notasFiscais->isEmpty())
                <div class="alert alert-info text-center">
                    <i class="bx bx-info-circle fs-1 d-block mb-2"></i>
                    <h5>Nenhuma NF-e encontrada</h5>
                    <p class="mb-0">Comece emitindo uma NF-e a partir de uma venda.</p>
                </div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Número</th>
                                <th>Chave de Acesso</th>
                                <th>Cliente</th>
                                <th>Venda</th>
                                <th>Valor Total</th>
                                <th>Data Emissão</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($notasFiscais as $nfe)
                            <tr>
                                <td>
                                    <span class="badge bg-secondary">#{{ $nfe->numero_nfe ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $nfe->chave_acesso ?? 'Pendente' }}</small>
                                </td>
                                <td>{{ $nfe->cliente->nome ?? 'N/A' }}</td>
                                <td>
                                    @if($nfe->venda)
                                    <a href="{{ route('vendas.edit', $nfe->venda->id) }}" class="text-primary">
                                        Venda #{{ $nfe->venda->id }}
                                    </a>
                                    @else
                                    N/A
                                    @endif
                                </td>
                                <td><strong>R$ {{ number_format($nfe->valor_total, 2, ',', '.') }}</strong></td>
                                <td>{{ $nfe->data_emissao ? $nfe->data_emissao->format('d/m/Y') : 'N/A' }}</td>
                                <td>
                                    @php
                                    $badgeColor = match($nfe->status) {
                                    'autorizada' => 'success',
                                    'rejeitada' => 'danger',
                                    'cancelada' => 'warning',
                                    'processando' => 'info',
                                    default => 'secondary'
                                    };
                                    @endphp
                                    <span class="badge bg-{{ $badgeColor }}">
                                        {{ ucfirst($nfe->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        @if(in_array($nfe->status, ['digitacao', 'rejeitada', 'erro']))
                                        <a href="{{ route('nfe.edit', $nfe->id) }}" class="btn btn-primary btn-sm" title="Editar NF-e">
                                            <i class="bx bx-edit"></i> Editar
                                        </a>
                                        @endif

                                        <a href="{{ route('nfe.show', $nfe->id) }}" class="btn btn-info btn-sm">
                                            <i class="bx bx-show"></i> Ver
                                        </a>

                                        @if($nfe->xml)
                                        <a href="{{ route('nfe.downloadXml', $nfe->id) }}" class="btn btn-success btn-sm" target="_blank">
                                            <i class="bx bx-download"></i> XML
                                        </a>
                                        @endif

                                        @if($nfe->status == 'autorizada')
                                        <a href="{{ route('nfe.gerarDanfe', $nfe->id) }}" class="btn btn-secondary btn-sm" target="_blank" title="DANFE">
                                            <i class="bx bxs-file-pdf"></i>
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
                    {{ $notasFiscais->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Inutilizar Numeração -->
<div class="modal fade" id="modalInutilizar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('nfe.inutilizar') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Inutilizar Numeração de NF-e</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <small>A inutilização de numeração serve para comunicar à SEFAZ que determinados números de notas não serão utilizados, por motivos de quebra de sequência ou erro.</small>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="serie" class="form-label">Série *</label>
                            <input type="number" class="form-control" id="serie" name="serie" required value="1">
                        </div>
                        <div class="col-md-4">
                            <label for="numero_inicial" class="form-label">Nº Inicial *</label>
                            <input type="number" class="form-control" id="numero_inicial" name="numero_inicial" required>
                        </div>
                        <div class="col-md-4">
                            <label for="numero_final" class="form-label">Nº Final *</label>
                            <input type="number" class="form-control" id="numero_final" name="numero_final" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="justificativa" class="form-label">Justificativa *</label>
                        <textarea class="form-control" id="justificativa" name="justificativa" rows="3" minlength="15" required placeholder="Justifique a inutilização (mínimo 15 caracteres)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-danger">Inutilizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection