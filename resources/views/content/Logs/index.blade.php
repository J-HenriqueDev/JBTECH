@extends('layouts.layoutMaster')

@section('title', 'Logs do Sistema')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Logs do Sistema</h4>
        <div>
            <a href="{{ route('logs.index') }}" class="btn btn-primary me-2">
                <i class="fas fa-sync"></i> Atualizar
            </a>
            <form action="{{ route('logs.clear') }}" method="POST" class="d-inline"
                onsubmit="return confirm('Tem certeza que deseja limpar todos os logs?');">
                @csrf
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Limpar Logs
                </button>
            </form>
        </div>
    </div>

    <!-- Console Logs Section (Retrátil) -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center" data-bs-toggle="collapse"
            data-bs-target="#consoleLogsCollapse" aria-expanded="false" style="cursor: pointer;">
            <h5 class="mb-0"><i class="fas fa-terminal me-2"></i>Logs do Console (Comandos)</h5>
            <i class="fas fa-chevron-down"></i>
        </div>
        <div id="consoleLogsCollapse" class="collapse">
            <div class="card-body p-0">
                <div
                    style="background-color: #000000; border: 1px solid #333333; border-radius: 4px; padding: 10px; max-height: 400px; overflow-y: auto; box-shadow: none;">
                    <pre class="m-0"
                        style="font-family: 'Consolas', 'Lucida Console', 'Courier New', monospace; font-size: 13px; line-height: 1.2; color: #cccccc; background-color: transparent; border: none; white-space: pre-wrap; text-shadow: none;">
@if (isset($consoleLogs) && count($consoleLogs) > 0)
{{ implode("\n", $consoleLogs) }}
@else
Nenhum log de console encontrado.
@endif
</pre>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Filtros</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('logs.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="usuario" class="form-label">Usuário</label>
                    <select name="usuario" id="usuario" class="form-select">
                        <option value="">Todos</option>
                        @foreach ($usuarios as $usuario)
                            <option value="{{ $usuario->id }}" {{ request('usuario') == $usuario->id ? 'selected' : '' }}>
                                {{ $usuario->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="categoria" class="form-label">Categoria</label>
                    <select name="categoria" id="categoria" class="form-select">
                        <option value="">Todas</option>
                        @foreach ($categorias as $cat)
                            <option value="{{ $cat }}" {{ request('categoria') == $cat ? 'selected' : '' }}>
                                {{ $cat }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="acao" class="form-label">Ação</label>
                    <select name="acao" id="acao" class="form-select">
                        <option value="">Todas</option>
                        @foreach ($acoes as $ac)
                            <option value="{{ $ac }}" {{ request('acao') == $ac ? 'selected' : '' }}>
                                {{ $ac }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filtrar</button>
                    <a href="{{ route('logs.index') }}" class="btn btn-secondary">Limpar</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela de Logs do Sistema -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Histórico de Ações</h5>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Usuário</th>
                        <th>Categoria</th>
                        <th>Ação</th>
                        <th>Detalhes</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                            <td>
                                @if ($log->user)
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-xs me-2">
                                            <span class="avatar-initial rounded-circle bg-label-primary">
                                                {{ substr($log->user->name, 0, 2) }}
                                            </span>
                                        </div>
                                        <span>{{ $log->user->name }}</span>
                                    </div>
                                @else
                                    <span class="text-muted">Sistema/Desconhecido</span>
                                @endif
                            </td>
                            <td><span class="badge bg-label-info">{{ $log->categoria }}</span></td>
                            <td>{{ $log->acao }}</td>
                            <td style="white-space: normal; max-width: 400px;">
                                {{ \Illuminate\Support\Str::limit($log->detalhes, 100) }}
                                @if (strlen($log->detalhes) > 100 || $log->ip)
                                    <button type="button" class="btn btn-sm btn-link text-primary p-0"
                                        data-bs-toggle="modal" data-bs-target="#logDetailsModal"
                                        data-details="{{ $log->detalhes }}" data-action="{{ $log->acao }}"
                                        data-category="{{ $log->categoria }}"
                                        data-user="{{ $log->user ? $log->user->name : 'Sistema/Desconhecido' }}"
                                        data-date="{{ $log->created_at->format('d/m/Y H:i:s') }}"
                                        data-ip="{{ $log->ip ?? 'N/A' }}" data-agent="{{ $log->user_agent ?? 'N/A' }}"
                                        title="Ver detalhes completos">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Nenhum registro encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $logs->links() }}
        </div>
    </div>

    <!-- Modal Detalhes do Log -->
    <div class="modal fade" id="logDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white" id="logDetailsModalTitle">
                        <i class="fas fa-info-circle me-2"></i> Detalhes do Registro
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body bg-light">
                    <div class="row g-3">
                        <!-- Card Principal: Info Básica -->
                        <div class="col-12">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar avatar-md me-3">
                                            <span class="avatar-initial rounded-circle bg-label-primary">
                                                <i class="fas fa-user"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0" id="modalUser">Usuário</h6>
                                            <small class="text-muted" id="modalDate">Data</small>
                                        </div>
                                        <div class="ms-auto">
                                            <span class="badge bg-label-info fs-6" id="modalCategory">Categoria</span>
                                        </div>
                                    </div>
                                    <h5 class="mb-1 text-primary" id="modalAction">Ação Realizada</h5>
                                </div>
                            </div>
                        </div>

                        <!-- Card Técnico: IP e Sistema -->
                        <div class="col-md-6">
                            <div class="card shadow-sm h-100">
                                <div class="card-body">
                                    <h6 class="card-title text-muted mb-3"><i class="fas fa-globe me-2"></i>Rastreamento
                                    </h6>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-bold">Endereço IP:</span>
                                        <span class="font-monospace" id="modalIP">0.0.0.0</span>
                                    </div>
                                    <div class="d-grid gap-2">
                                        <a href="#" id="btnGeoLocation" target="_blank"
                                            class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-map-marker-alt me-1"></i> Ver Localização Aproximada
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card shadow-sm h-100">
                                <div class="card-body">
                                    <h6 class="card-title text-muted mb-3"><i class="fas fa-desktop me-2"></i>Dispositivo
                                    </h6>
                                    <p class="small text-muted mb-0" id="modalAgent" style="word-break: break-all;">
                                        User Agent String...
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Detalhes Completos -->
                        <div class="col-12">
                            <div class="card shadow-sm">
                                <div class="card-header bg-white pb-0">
                                    <h6 class="card-title mb-0"><i class="fas fa-align-left me-2"></i>Conteúdo do Log</h6>
                                </div>
                                <div class="card-body pt-3">
                                    <pre id="logDetailsContent" class="bg-dark text-light p-3 rounded mb-0"
                                        style="white-space: pre-wrap; word-wrap: break-word; max-height: 300px; overflow-y: auto; font-family: 'Consolas', monospace; font-size: 0.85rem;"></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const logDetailsModal = document.getElementById('logDetailsModal');
            if (logDetailsModal) {
                logDetailsModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;

                    // Extrair dados
                    const details = button.getAttribute('data-details') || 'Sem detalhes.';
                    const action = button.getAttribute('data-action') || 'N/A';
                    const category = button.getAttribute('data-category') || 'N/A';
                    const user = button.getAttribute('data-user') || 'Desconhecido';
                    const date = button.getAttribute('data-date') || 'N/A';
                    const ip = button.getAttribute('data-ip') || 'N/A';
                    const agent = button.getAttribute('data-agent') || 'N/A';

                    // Preencher Modal
                    logDetailsModal.querySelector('#logDetailsContent').textContent = details;
                    logDetailsModal.querySelector('#modalAction').textContent = action;
                    logDetailsModal.querySelector('#modalCategory').textContent = category;
                    logDetailsModal.querySelector('#modalUser').textContent = user;
                    logDetailsModal.querySelector('#modalDate').textContent = date;
                    logDetailsModal.querySelector('#modalIP').textContent = ip;
                    logDetailsModal.querySelector('#modalAgent').textContent = agent;

                    // Configurar botão de Geo
                    const btnGeo = logDetailsModal.querySelector('#btnGeoLocation');
                    if (ip && ip !== 'N/A' && ip !== '127.0.0.1' && ip !== '::1') {
                        btnGeo.href = `https://whatismyipaddress.com/ip/${ip}`;
                        btnGeo.classList.remove('disabled');
                    } else {
                        btnGeo.href = '#';
                        btnGeo.classList.add('disabled');
                    }
                });
            }
        });
    </script>
@endsection
