@extends('layouts.layoutMaster')

@section('title', 'Manifesto de Notas Fiscais')

@section('content')

@if(isset($bloqueioMsg) && $bloqueioMsg)
<div class="alert alert-warning d-flex align-items-center" role="alert">
    <i class="bx bx-time-five me-2"></i>
    <div>
        {{ $bloqueioMsg }}
    </div>
</div>
@endif

@if(session('success'))
<div class="alert alert-success alert-dismissible" role="alert">
    <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">
        <i class="bx bx-check-circle me-1"></i> Sucesso!
    </h6>
    <p class="mb-0">{!! session('success') !!}</p>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('warning'))
<div class="alert alert-warning alert-dismissible" role="alert">
    <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">
        <i class="bx bx-error me-1"></i> Atenção!
    </h6>
    <p class="mb-0">{!! session('warning') !!}</p>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible" role="alert">
    <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">
        <i class="bx bx-x-circle me-1"></i> Erro!
    </h6>
    <p class="mb-0">{!! session('error') !!}</p>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-0 text-primary">
            <i class="bx bx-file-find"></i> Manifesto de Notas Fiscais
        </h1>
        <small class="text-muted">Gerencie e manifeste suas notas fiscais de entrada (Últimos 30 dias)</small>
    </div>
    <div class="d-flex gap-2">
        <form action="{{ route('nfe.manifesto.sincronizar') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary" {{ (isset($bloqueioMsg) && $bloqueioMsg) ? 'disabled' : '' }}>
                <i class="bx bx-refresh me-1"></i> Buscar Novas Notas
            </button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header border-bottom">
        <div class="d-flex justify-content-between align-items-center row pb-2 gap-3 gap-md-0">
            <div class="col-md-4 user_role"></div>
            <div class="col-md-4 user_plan"></div>
            <div class="col-md-4 user_status"></div>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <h5 class="card-title mb-0">Notas Encontradas ({{ $notas->count() }})</h5>
            <div id="bulk-actions" style="display: none;">
                <span class="me-2 fw-bold" id="selected-count">0 selecionados</span>
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bx bx-check-double me-1"></i> Manifestar como...
                    </button>
                    <ul class="dropdown-menu">
                        <li><button class="dropdown-item" onclick="submitBulk('ciencia')"><i class="bx bx-show me-2"></i>Ciência da Operação</button></li>
                        <li><button class="dropdown-item" onclick="submitBulk('confirmada')"><i class="bx bx-check me-2"></i>Confirmação da Operação</button></li>
                        <li><button class="dropdown-item" onclick="submitBulk('desconhecida')"><i class="bx bx-question-mark me-2"></i>Desconhecimento da Operação</button></li>
                        <li><button class="dropdown-item" onclick="submitBulk('nao_realizada')"><i class="bx bx-x me-2"></i>Operação não Realizada</button></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive text-nowrap">
        <form id="bulk-form" action="{{ route('nfe.manifesto.manifestar') }}" method="POST">
            @csrf
            <input type="hidden" name="tipo" id="bulk-tipo">

            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="50">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="select-all">
                            </div>
                        </th>
                        <th>Data Emissão</th>
                        <th>Chave de Acesso</th>
                        <th>Emitente</th>
                        <th>Valor</th>
                        <th>Status Manifestação</th>
                        <th>Status Download</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($notas as $nota)
                    <tr>
                        <td>
                            <div class="form-check">
                                <input class="form-check-input note-checkbox" type="checkbox" name="chaves[]" value="{{ $nota->chave_acesso }}">
                            </div>
                        </td>
                        <td>{{ $nota->data_emissao ? \Carbon\Carbon::parse($nota->data_emissao)->format('d/m/Y H:i') : 'N/A' }}</td>
                        <td>
                            <span title="{{ $nota->chave_acesso }}">{{ substr($nota->chave_acesso, 0, 25) }}...</span>
                            <button type="button" class="btn btn-sm btn-icon" onclick="navigator.clipboard.writeText('{{ $nota->chave_acesso }}')">
                                <i class="bx bx-copy"></i>
                            </button>
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="fw-bold">{{ $nota->emitente_nome ?? 'Desconhecido' }}</span>
                                <small class="text-muted">{{ $nota->emitente_cnpj }}</small>
                            </div>
                        </td>
                        <td>R$ {{ number_format($nota->valor_total, 2, ',', '.') }}</td>
                        <td>
                            @php
                            $statusClass = match($nota->manifestacao) {
                            'ciencia' => 'bg-info',
                            'confirmada' => 'bg-success',
                            'desconhecida' => 'bg-warning',
                            'nao_realizada' => 'bg-danger',
                            default => 'bg-secondary'
                            };
                            $statusLabel = match($nota->manifestacao) {
                            'ciencia' => 'Ciência',
                            'confirmada' => 'Confirmada',
                            'desconhecida' => 'Desconhecida',
                            'nao_realizada' => 'Não Realizada',
                            default => 'Sem Manifestação'
                            };
                            @endphp
                            <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                        </td>
                        <td>
                            @if($nota->xml_content)
                            <span class="badge bg-success">XML Baixado</span>
                            @else
                            <span class="badge bg-warning">Pendente</span>
                            @endif
                        </td>
                        <td>
                            @if($nota->xml_content)
                            <a href="{{ route('notas-entrada.processar', $nota->id) }}" class="btn btn-sm btn-primary">
                                <i class="bx bx-import me-1"></i> Importar
                            </a>
                            @else
                            <small class="text-muted">Aguarde download</small>
                            @endif

                            @if($nota->manifestacao != 'sem_manifestacao' && !$nota->xml_content)
                            <a href="{{ route('nfe.manifesto.baixarXml', $nota->id) }}" class="btn btn-sm btn-icon btn-warning" data-bs-toggle="tooltip" title="Tentar baixar XML agora">
                                <i class="bx bx-download"></i>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="bx bx-search fs-1 text-muted mb-2"></i>
                            <p class="text-muted">Nenhuma nota encontrada nos últimos 30 dias.</p>
                            <p class="text-muted small">Clique em "Buscar Novas Notas" para consultar na SEFAZ.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('select-all');
        const checkboxes = document.querySelectorAll('.note-checkbox');
        const bulkActions = document.getElementById('bulk-actions');
        const selectedCount = document.getElementById('selected-count');

        function updateBulkActions() {
            const count = document.querySelectorAll('.note-checkbox:checked').length;
            if (count > 0) {
                bulkActions.style.display = 'block';
                selectedCount.textContent = count + ' selecionados';
            } else {
                bulkActions.style.display = 'none';
            }
        }

        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
            updateBulkActions();
        });

        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateBulkActions);
        });
    });

    function submitBulk(tipo) {
        if (!confirm('Tem certeza que deseja manifestar as notas selecionadas como ' + tipo.toUpperCase() + '?')) {
            return;
        }
        document.getElementById('bulk-tipo').value = tipo;
        document.getElementById('bulk-form').submit();
    }
</script>

@endsection