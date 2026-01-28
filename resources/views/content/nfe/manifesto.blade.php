@extends('layouts.layoutMaster')

@section('title', 'Manifesto de Notas Fiscais')

@section('content')

    @if (isset($bloqueioMsg) && $bloqueioMsg)
        <div class="alert alert-warning d-flex align-items-center" role="alert">
            <i class="bx bx-time-five me-2"></i>
            <div>
                {{ $bloqueioMsg }}
            </div>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">
                <i class="bx bx-check-circle me-1"></i> Sucesso!
            </h6>
            <p class="mb-0">{!! session('success') !!}</p>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('warning'))
        <div class="alert alert-warning alert-dismissible" role="alert">
            <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">
                <i class="bx bx-error me-1"></i> Atenção!
            </h6>
            <p class="mb-0">{!! session('warning') !!}</p>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
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
                <button type="submit" class="btn btn-primary" {{ isset($bloqueioMsg) && $bloqueioMsg ? 'disabled' : '' }}>
                    <i class="bx bx-refresh me-1"></i> Buscar Novas Notas
                </button>
                <button type="submit" name="reset_nsu" value="1" class="btn btn-outline-secondary"
                    {{ isset($bloqueioMsg) && $bloqueioMsg ? 'disabled' : '' }}
                    title="Refazer busca completa (últimos 90 dias)">
                    <i class="bx bx-history me-1"></i> Resync Completo
                </button>
            </form>
            <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#modalAtalhos">
                <i class="bx bx-keyboard me-1"></i> Atalhos
            </button>
        </div>

    </div>


    <div class="alert alert-primary d-flex" role="alert">
        <span class="badge badge-center rounded-pill bg-primary border-label-primary p-3 me-2"><i
                class="bx bx-info-circle fs-6"></i></span>
        <div class="d-flex flex-column ps-1">
            <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">Qual devo usar?</h6>
            <span>Use <strong>Buscar Novas Notas</strong> todos os dias. Deixe o <strong>Resync Completo</strong> apenas
                para emergências.</span>
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary ms-auto" data-bs-toggle="modal"
            data-bs-target="#modalHelp">
            <i class="bx bx-help-circle me-1"></i> Detalhes
        </button>
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
                        <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="bx bx-check-double me-1"></i> Manifestar como...
                        </button>
                        <ul class="dropdown-menu">
                            <li><button class="dropdown-item" onclick="submitBulk('ciencia')"><i
                                        class="bx bx-show me-2"></i>Ciência da Operação</button></li>
                            <li><button class="dropdown-item" onclick="submitBulk('confirmada')"><i
                                        class="bx bx-check me-2"></i>Confirmação da Operação</button></li>
                            <li><button class="dropdown-item" onclick="submitBulk('desconhecida')"><i
                                        class="bx bx-question-mark me-2"></i>Desconhecimento da Operação</button></li>
                            <li><button class="dropdown-item" onclick="submitBulk('nao_realizada')"><i
                                        class="bx bx-x me-2"></i>Operação não Realizada</button></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive text-nowrap">
            <form id="bulk-form" action="{{ route('nfe.manifesto.manifestar') }}" method="POST"
                onsubmit="return validateBulkForm()">
                @csrf

                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="50">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="select-all">
                                </div>
                            </th>
                            <th>Data Emissão</th>
                            <th>Número NFe / Chave</th>
                            <th>Emitente</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th>Status Download</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($notas as $nota)
                            <tr>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input note-checkbox" type="checkbox" name="chaves[]"
                                            value="{{ $nota->chave_acesso }}" data-status="{{ $nota->manifestacao }}">
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $dataEmissao = 'N/A';
                                        if ($nota->data_emissao) {
                                            try {
                                                $dataEmissao = \Carbon\Carbon::parse($nota->data_emissao)->format(
                                                    'd/m/Y H:i',
                                                );
                                            } catch (\Exception $e) {
                                                $dataEmissao = $nota->data_emissao; // Mostra original se falhar o parse
                                            }
                                        }
                                    @endphp
                                    {{ $dataEmissao }}
                                </td>
                                <td>
                                    <span
                                        title="{{ $nota->chave_acesso }}">{{ substr($nota->chave_acesso, 0, 25) }}...</span>
                                    <button type="button" class="btn btn-sm btn-icon"
                                        onclick="navigator.clipboard.writeText('{{ $nota->chave_acesso }}')">
                                        <i class="bx bx-copy"></i>
                                    </button>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold" title="{{ $nota->emitente_nome ?? 'Desconhecido' }}">{{ \Illuminate\Support\Str::limit($nota->emitente_nome ?? 'Desconhecido', 40, '...') }}</span>
                                        <small class="text-muted">{{ $nota->emitente_cnpj }}</small>
                                    </div>
                                </td>
                                <td>R$ {{ number_format($nota->valor_total, 2, ',', '.') }}</td>
                                <td>
                                    @php
                                        $statusClass = match ($nota->manifestacao) {
                                            'ciencia' => 'bg-info',
                                            'confirmada' => 'bg-success',
                                            'desconhecida' => 'bg-warning',
                                            'nao_realizada' => 'bg-danger',
                                            default => 'bg-secondary',
                                        };
                                        $statusLabel = match ($nota->manifestacao) {
                                            'ciencia' => 'Ciência',
                                            'confirmada' => 'Confirmada',
                                            'desconhecida' => 'Desconhecida',
                                            'nao_realizada' => 'Não Realizada',
                                            default => 'Sem Manifestação',
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                </td>
                                <td>
                                    @if ($nota->xml_content)
                                        <span class="badge bg-success">XML Baixado</span>
                                    @else
                                        <span class="badge bg-warning">Pendente</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($nota->xml_content)
                                        <a href="{{ route('notas-entrada.processar', $nota->id) }}"
                                            class="btn btn-sm btn-primary" title="Importar Nota">
                                            <i class="bx bx-import"></i>
                                        </a>
                                    @else
                                        <button type="button" class="btn btn-sm btn-icon btn-info"
                                            data-bs-toggle="modal" data-bs-target="#modalDetalhes{{ $nota->id }}"
                                            title="Ver Detalhes (Resumo)">
                                            <i class="bx bx-show"></i>
                                        </button>
                                        <!-- Modal Detalhes -->
                                        <div class="modal fade" id="modalDetalhes{{ $nota->id }}" tabindex="-1"
                                            aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Detalhes da Nota (Resumo)</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="alert alert-info">
                                                            <small><i class="bx bx-info-circle"></i> Os detalhes completos
                                                                (itens, impostos)
                                                                só estarão disponíveis após a manifestação
                                                                e download do XML.</small>
                                                        </div>
                                                        <dl class="row">
                                                            <dt class="col-sm-4">Emitente</dt>
                                                            <dd class="col-sm-8">{{ $nota->emitente_nome }}</dd>

                                                            <dt class="col-sm-4">CNPJ</dt>
                                                            <dd class="col-sm-8">{{ $cnpjFormatado }}</dd>

                                                            <dt class="col-sm-4">Valor Total</dt>
                                                            <dd class="col-sm-8">R$
                                                                {{ number_format($nota->valor_total, 2, ',', '.') }}</dd>

                                                            <dt class="col-sm-4">Data Emissão</dt>
                                                            <dd class="col-sm-8">{{ $dataEmissao }}</dd>

                                                            <dt class="col-sm-4">Chave de Acesso</dt>
                                                            <dd class="col-sm-8 text-break">{{ $nota->chave_acesso }}</dd>

                                                            <dt class="col-sm-4">Status SEFAZ</dt>
                                                            <dd class="col-sm-8">{{ ucfirst($nota->status) }}</dd>
                                                        </dl>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Fechar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($nota->manifestacao != 'sem_manifestacao' && !$nota->xml_content)
                                        <a href="{{ route('nfe.manifesto.baixarXml', $nota->id) }}"
                                            class="btn btn-sm btn-icon btn-warning" data-bs-toggle="tooltip"
                                            title="Tentar baixar XML agora">
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
                <input type="hidden" name="tipo" id="bulk-tipo">
            </form>
        </div>
    </div>

    <!-- Modal Atalhos -->
    <div class="modal fade" id="modalAtalhos" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAtalhosTitle">Atalhos de Teclado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Marcar Tudo</span>
                        <span class="badge bg-label-primary">F4</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Desmarcar Tudo</span>
                        <span class="badge bg-label-secondary">F5</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAll = document.getElementById('select-all');
            const checkboxes = document.querySelectorAll('.note-checkbox');
            const bulkActions = document.getElementById('bulk-actions');
            const selectedCount = document.getElementById('selected-count');

            // Atalhos de teclado
            document.addEventListener('keydown', function(event) {
                // F4: Marca tudo
                if (event.key === 'F4' || event.keyCode === 115) {
                    event.preventDefault();
                    if (selectAll) {
                        selectAll.checked = true;
                        checkboxes.forEach(cb => cb.checked = true);
                        updateBulkActions();
                    }
                }
                // F5: Desmarca tudo (se não houver modal aberto)
                if (event.key === 'F5' || event.keyCode === 116) {
                    event.preventDefault();
                    if (selectAll) {
                        selectAll.checked = false;
                        checkboxes.forEach(cb => cb.checked = false);
                        updateBulkActions();
                    }
                }
            });

            function updateBulkActions() {
                const count = document.querySelectorAll('.note-checkbox:checked').length;

                if (bulkActions) {
                    if (count > 0) {
                        bulkActions.style.display = 'block';
                        if (selectedCount) {
                            selectedCount.textContent = count + ' selecionados';
                        }
                    } else {
                        bulkActions.style.display = 'none';
                    }
                }
            }

            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    checkboxes.forEach(cb => cb.checked = selectAll.checked);
                    updateBulkActions();
                });
            }

            checkboxes.forEach(cb => {
                cb.addEventListener('change', updateBulkActions);
            });
        });

        function validateBulkForm() {
            const tipoInput = document.getElementById('bulk-tipo');
            // Se o campo tipo não existir ou estiver vazio, impede o envio
            if (!tipoInput || !tipoInput.value) {
                return false;
            }
            return true;
        }

        function submitBulk(tipo) {
            const selectedCheckboxes = document.querySelectorAll('.note-checkbox:checked');
            const selected = selectedCheckboxes.length;
            if (selected === 0) {
                alert('Por favor, selecione pelo menos uma nota.');
                return;
            }

            let message = 'Tem certeza que deseja manifestar ' + selected + ' notas selecionadas como ' + tipo
                .toUpperCase() + '?';

            // Alerta extra se tentar desconhecer nota já confirmada
            if (tipo === 'desconhecida') {
                let hasConfirmed = false;
                selectedCheckboxes.forEach(cb => {
                    if (cb.dataset.status === 'confirmada') {
                        hasConfirmed = true;
                    }
                });

                if (hasConfirmed) {
                    message = "ATENÇÃO: Você selecionou notas que já estão CONFIRMADAS.\n\n" +
                        "Mudar para DESCONHECIMENTO anula a confirmação anterior, mas deve ser feito dentro do prazo legal (geralmente 180 dias).\n\n" +
                        "Deseja realmente continuar?";
                }
            }

            if (!confirm(message)) {
                return;
            }

            const form = document.getElementById('bulk-form');
            let tipoInput = document.getElementById('bulk-tipo');

            // Garante que o input existe e está dentro do form
            if (!tipoInput) {
                tipoInput = document.createElement('input');
                tipoInput.type = 'hidden';
                tipoInput.name = 'tipo';
                tipoInput.id = 'bulk-tipo';
                form.appendChild(tipoInput);
            }

            tipoInput.value = tipo;

            // Submete o formulário
            form.submit();
        }
    </script>

@endsection

<!-- Modal de Ajuda Sincronização -->
<div class="modal fade" id="modalHelp" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bx bx-help-circle me-2"></i>Entenda as Opções de Sincronização</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-primary d-flex" role="alert">
                    <span class="badge badge-center rounded-pill bg-primary border-label-primary p-3 me-2"><i
                            class="bx bx-info-circle fs-6"></i></span>
                    <div class="d-flex flex-column ps-1">
                        <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">Qual devo usar?</h6>
                        <span>Use "Buscar Novas Notas" todos os dias. Deixe o "Resync Completo" apenas para
                            emergências.</span>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <div class="card shadow-none bg-label-primary h-100">
                            <div class="card-body p-3">
                                <h6 class="card-title fw-bold text-primary mb-2">
                                    <i class="bx bx-refresh me-1"></i> Buscar Novas Notas
                                </h6>
                                <p class="card-text small mb-0">
                                    <strong>O que faz:</strong> Busca apenas o que foi emitido <u>após</u> a última
                                    sincronização.<br>
                                    <strong>Quando usar:</strong> No dia a dia, para ver se chegaram notas novas
                                    hoje.<br>
                                    <strong>Vantagem:</strong> É muito mais rápido e evita bloqueios da SEFAZ.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card shadow-none bg-label-secondary h-100">
                            <div class="card-body p-3">
                                <h6 class="card-title fw-bold text-secondary mb-2">
                                    <i class="bx bx-history me-1"></i> Resync Completo
                                </h6>
                                <p class="card-text small mb-0">
                                    <strong>O que faz:</strong> Ignora o histórico e varre a SEFAZ buscando <u>todas</u>
                                    as notas dos últimos 90 dias.<br>
                                    <strong>Quando usar:</strong> Apenas se notar que <u>faltam notas antigas</u> na
                                    lista ou se o sistema ficou semanas desligado.<br>
                                    <strong>Atenção:</strong> Pode demorar mais para processar.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Entendi, obrigado!</button>
            </div>
        </div>
    </div>
</div>
