@extends('layouts.layoutMaster')

@section('title', 'Importação de Notas (Entrada)')

@section('content')

    @if (session('success'))
        <div class="alert alert-primary alert-dismissible" role="alert">
            <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">
                <i class="fas fa-check-circle me-1"></i> Sucesso!
            </h6>
            <p class="mb-0">{!! session('success') !!}</p>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible" role="alert">
            <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">
                <i class="fas fa-exclamation-circle me-1"></i> Erro!
            </h6>
            <p class="mb-0">{!! session('error') !!}</p>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-warning alert-dismissible" role="alert">
            <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">
                <i class="fas fa-exclamation-triangle me-1"></i> Atenção!
            </h6>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0 text-primary"
            style="font-size: 2.5rem; font-weight: bold; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);">
            <i class="fas fa-file-invoice me-2"></i> Importação de Notas
        </h1>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalUpload">
                <i class="fas fa-upload me-1"></i> Importar XML
            </button>
            <form action="{{ route('notas-entrada.buscar') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-primary"
                    {{ isset($bloqueioMsg) && $bloqueioMsg ? 'disabled' : '' }}>
                    <i class="fas fa-sync-alt me-1"></i> Buscar na SEFAZ
                </button>
            </form>
        </div>
    </div>

    <!-- Banner Soneca (Minimalista) -->
    @if ($healthStats['sonecaMinutos'] > 0)
        <div class="alert alert-info d-flex align-items-center justify-content-between mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bx bx-sleep-y me-2"></i>
                <span>💤 Robô em espera técnica. Retorno em <strong
                        id="soneca-timer">{{ $healthStats['sonecaMinutos'] }}</strong> min.</span>
            </div>
            <span class="badge bg-info">SEFAZ</span>
        </div>
    @endif

    <!-- Removido: Cards de estatísticas (movidos para Dashboard Fiscal) -->

    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('notas-entrada.baixar-por-chave') }}" method="POST">
                @csrf
                <label for="chave" class="form-label fs-5 fw-bold text-primary">Ler Código de Barras / Chave de
                    Acesso</label>
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-primary text-white"><i class="fas fa-barcode"></i></span>
                    <input type="text" class="form-control" id="chave" name="chave"
                        placeholder="Aponte o leitor aqui ou digite a chave de 44 dígitos..." required autofocus>
                    <button class="btn btn-primary" type="submit">Baixar Nota</button>
                </div>
                <div class="form-text mt-2"><i class="fas fa-info-circle me-1"></i> O sistema detectará automaticamente o
                    "Enter" do leitor de código de barras.</div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header border-bottom">
            <div class="d-flex justify-content-between align-items-center mt-3">
                <h5 class="card-title mb-0">Notas Destinadas ({{ $notas->total() }})</h5>
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
                                            value="{{ $nota->chave_acesso }}">
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
                                                $dataEmissao = $nota->data_emissao;
                                            }
                                        }
                                    @endphp
                                    {{ $dataEmissao }}
                                </td>
                                <td>
                                    @php
                                        $temXml = !empty($nota->xml_content);
                                        $temNumeroSerie = !empty($nota->numero_nfe) && !empty($nota->serie);
                                    @endphp
                                    <div class="d-flex flex-column">
                                        @if ($temXml && $temNumeroSerie)
                                            <span class="fw-bold fs-6">{{ $nota->numero_nfe }}</span>
                                            <small class="text-muted">Série: {{ $nota->serie }}</small>
                                        @else
                                            <span class="badge bg-label-secondary">Aguardando XML...</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <small
                                            class="text-muted me-2 font-monospace">{{ substr($nota->chave_acesso, 0, 4) }}...{{ substr($nota->chave_acesso, -4) }}</small>
                                        <button type="button" class="btn btn-sm btn-label-primary btn-icon"
                                            onclick="navigator.clipboard.writeText('{{ $nota->chave_acesso }}'); alert('Chave copiada para a área de transferência!')"
                                            title="Copiar Chave Completa">
                                            <i class="bx bx-clipboard"></i>
                                        </button>
                                    </div>
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
                                        <a href="{{ route('notas-entrada.processar', $nota->id) }}"
                                            class="btn btn-sm btn-success w-100">
                                            <i class="bx bx-check-circle me-1"></i> Dar Entrada
                                        </a>
                                    @else
                                        <form action="{{ route('notas-entrada.baixar-por-chave') }}" method="POST"
                                            class="d-inline w-100">
                                            @csrf
                                            <input type="hidden" name="chave" value="{{ $nota->chave_acesso }}">
                                            <button type="submit" class="btn btn-sm btn-warning w-100"
                                                title="Clique para baixar o XML da SEFAZ">
                                                <i class="bx bx-cloud-download me-1"></i> Baixar Pendente
                                            </button>
                                        </form>
                                    @endif
                                </td>
                                <td>
                                    @if ($nota->xml_content)
                                        <a href="{{ route('notas-entrada.processar', $nota->id) }}"
                                            class="btn btn-sm btn-primary" title="Importar Nota">
                                            <i class="bx bx-import"></i>
                                        </a>
                                    @else
                                        <form action="{{ route('notas-entrada.baixar-por-chave') }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            <input type="hidden" name="chave" value="{{ $nota->chave_acesso }}">
                                            <button type="submit" class="btn btn-sm btn-warning"
                                                title="Tentar baixar XML agora">
                                                <i class="bx bx-download"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="bx bx-search fs-1 text-muted mb-2"></i>
                                    <p class="text-muted">Nenhuma nota encontrada.</p>
                                    <p class="text-muted small">Clique em "Buscar na SEFAZ" para consultar notas emitidas
                                        para seu CNPJ.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </form>
        </div>
        <div class="card-footer">
            {{ $notas->links() }}
        </div>
    </div>

    <!-- Modal Upload XML -->
    <div class="modal fade" id="modalUpload" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('notas-entrada.upload-xml') }}" method="POST" enctype="multipart/form-data"
                class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Importar XML Manualmente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="xml_file" class="form-label">Arquivo XML</label>
                        <input type="file" class="form-control" id="xml_file" name="xml_file" accept=".xml"
                            required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Importar</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
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

            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    checkboxes.forEach(cb => cb.checked = selectAll.checked);
                    updateBulkActions();
                });

                checkboxes.forEach(cb => {
                    cb.addEventListener('change', updateBulkActions);
                });
            }
        });

        function submitBulk(tipo) {
            if (!confirm('Tem certeza que deseja manifestar as notas selecionadas como ' + tipo.toUpperCase() + '?')) {
                return;
            }
            document.getElementById('bulk-tipo').value = tipo;
            document.getElementById('bulk-form').submit();
        }
    </script>
@endpush
