@extends('layouts.layoutMaster')

@section('title', 'Logs do Sistema')

@section('vendor-style')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .log-details-cell {
        max-width: 300px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        cursor: pointer;
    }
    .log-details-cell:hover {
        white-space: normal;
        word-break: break-word;
    }
    .badge-categoria {
        font-size: 0.85rem;
        padding: 0.4em 0.8em;
    }
    .table th {
        font-weight: 600;
        background-color: #f8f9fa;
    }
    .filters-section {
        background-color: #f8f9fa;
        border-radius: 0.375rem;
        padding: 1rem;
        margin-bottom: 1rem;
    }
    @media (max-width: 768px) {
        .filters-section .row > div {
            margin-bottom: 0.5rem;
        }
    }
</style>
@endsection

@section('vendor-script')
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endsection

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-0 text-primary">
            <i class="bx bx-list-ul"></i> Logs do Sistema
        </h1>
        <p class="text-muted mb-0">Visualize e filtre todas as atividades registradas no sistema</p>
    </div>
    <div class="text-end">
        <span class="badge bg-info">
            Total: {{ $logs->total() }} registro(s)
        </span>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bx bx-filter-alt"></i> Filtros de Busca
                </h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('logs.index') }}" id="filterForm">
                    <div class="row g-3">
                        <!-- Filtro por Usuário -->
                        <div class="col-md-3 col-sm-6">
                            <label class="form-label">
                                <i class="bx bx-user"></i> Usuário
                            </label>
                            <select name="usuario" class="form-select select2" id="usuarioSelect">
                                <option value="">Todos os Usuários</option>
                                @foreach ($usuarios as $usuario)
                                    <option value="{{ $usuario->id }}" {{ request('usuario') == $usuario->id ? 'selected' : '' }}>
                                        {{ $usuario->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filtro por Categoria -->
                        <div class="col-md-3 col-sm-6">
                            <label class="form-label">
                                <i class="bx bx-tag"></i> Categoria
                            </label>
                            <select name="categoria" class="form-select select2" id="categoriaSelect">
                                <option value="">Todas as Categorias</option>
                                @foreach ($categorias as $categoria)
                                    <option value="{{ $categoria }}" {{ request('categoria') == $categoria ? 'selected' : '' }}>
                                        {{ $categoria }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filtro por Ação -->
                        <div class="col-md-3 col-sm-6">
                            <label class="form-label">
                                <i class="bx bx-bolt-circle"></i> Ação
                            </label>
                            <select name="acao" class="form-select select2" id="acaoSelect">
                                <option value="">Todas as Ações</option>
                                @foreach ($acoes as $acao)
                                    <option value="{{ $acao }}" {{ request('acao') == $acao ? 'selected' : '' }}>
                                        {{ $acao }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filtro por Detalhes -->
                        <div class="col-md-3 col-sm-6">
                            <label class="form-label">
                                <i class="bx bx-search"></i> Buscar nos Detalhes
                            </label>
                            <input type="text" name="detalhes" class="form-control"
                                   placeholder="Digite para buscar..." value="{{ request('detalhes') }}">
                        </div>

                        <!-- Filtro por Data Inicial -->
                        <div class="col-md-3 col-sm-6">
                            <label class="form-label">
                                <i class="bx bx-calendar"></i> Data Inicial
                            </label>
                            <input type="date" name="data_inicial" class="form-control"
                                   value="{{ request('data_inicial') }}">
                        </div>

                        <!-- Filtro por Data Final -->
                        <div class="col-md-3 col-sm-6">
                            <label class="form-label">
                                <i class="bx bx-calendar-check"></i> Data Final
                            </label>
                            <input type="date" name="data_final" class="form-control"
                                   value="{{ request('data_final') }}">
                        </div>

                        <!-- Itens por Página -->
                        <div class="col-md-3 col-sm-6">
                            <label class="form-label">
                                <i class="bx bx-list-ul"></i> Itens por Página
                            </label>
                            <select name="perPage" class="form-select" id="perPage">
                                <option value="10" {{ request('perPage', 10) == 10 ? 'selected' : '' }}>10</option>
                                <option value="25" {{ request('perPage') == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('perPage') == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('perPage') == 100 ? 'selected' : '' }}>100</option>
                            </select>
                        </div>

                        <!-- Botões de Ação -->
                        <div class="col-md-3 col-sm-6 d-flex align-items-end">
                            <div class="d-flex gap-2 w-100">
                                <button type="submit" class="btn btn-primary flex-fill">
                                    <i class="bx bx-filter-alt"></i> Aplicar Filtros
                                </button>
                                <a href="{{ route('logs.index') }}" class="btn btn-outline-secondary">
                                    <i class="bx bx-x"></i> Limpar
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bx bx-list-ul"></i> Logs Registrados
                </h5>
                @if(request()->filled('usuario') || request()->filled('categoria') || request()->filled('acao') || request()->filled('detalhes') || request()->filled('data_inicial') || request()->filled('data_final'))
                    <span class="badge bg-warning">
                        <i class="bx bx-info-circle"></i> Filtros Ativos
                    </span>
                @endif
            </div>

            <div class="card-body">
                @if ($logs->isEmpty())
                    <div class="alert alert-info text-center py-4">
                        <i class="bx bx-info-circle fs-1 d-block mb-2"></i>
                        <h5>Nenhum log encontrado</h5>
                        <p class="mb-0">Tente ajustar os filtros de busca ou limpar os filtros ativos.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">ID</th>
                                    <th style="width: 150px;">Usuário</th>
                                    <th style="width: 120px;">Categoria</th>
                                    <th style="width: 150px;">Ação</th>
                                    <th>Detalhes</th>
                                    <th style="width: 160px;">Data/Hora</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($logs as $log)
                                    <tr>
                                        <td>
                                            <span class="badge bg-secondary">#{{ $log->id }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-2">
                                                    <span class="avatar-initial rounded-circle bg-primary">
                                                        {{ strtoupper(substr($log->user->name ?? 'N/A', 0, 1)) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <span class="fw-semibold">{{ $log->user->name ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $badgeColor = match(strtolower($log->categoria)) {
                                                    'erro', 'error' => 'danger',
                                                    'sucesso', 'success' => 'success',
                                                    'aviso', 'warning' => 'warning',
                                                    'info', 'informação' => 'info',
                                                    default => 'primary'
                                                };
                                            @endphp
                                            <span class="badge bg-{{ $badgeColor }} badge-categoria">
                                                <i class="bx bx-{{ strtolower($log->categoria) == 'erro' ? 'error-circle' : 'check-circle' }}"></i>
                                                {{ $log->categoria }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-nowrap">
                                                <i class="bx bx-{{ $log->acao == 'Criar' ? 'plus-circle' : ($log->acao == 'Atualizar' ? 'edit' : ($log->acao == 'Deletar' ? 'trash' : 'info-circle')) }} text-primary"></i>
                                                {{ $log->acao }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="log-details-cell"
                                                 data-bs-toggle="tooltip"
                                                 data-bs-placement="top"
                                                 title="{{ $log->detalhes }}">
                                                {{ \Illuminate\Support\Str::limit($log->detalhes, 80) }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="text-nowrap">
                                                    <i class="bx bx-calendar text-muted"></i>
                                                    {{ $log->created_at->format('d/m/Y') }}
                                                </span>
                                                <small class="text-muted">
                                                    <i class="bx bx-time"></i>
                                                    {{ $log->created_at->format('H:i:s') }}
                                                </small>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- Paginação -->
            @if($logs->hasPages())
                <div class="card-footer d-flex justify-content-between align-items-center flex-wrap">
                    <div class="text-muted">
                        Mostrando {{ $logs->firstItem() }} a {{ $logs->lastItem() }} de {{ $logs->total() }} registros
                    </div>
                    <div>
                        {{ $logs->appends(request()->query())->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Inicializa o Select2 para os campos
        $('#usuarioSelect').select2({
            placeholder: "Selecione um usuário",
            allowClear: true,
            width: '100%'
        });

        $('#categoriaSelect').select2({
            placeholder: "Selecione uma categoria",
            allowClear: true,
            width: '100%'
        });

        $('#acaoSelect').select2({
            placeholder: "Selecione uma ação",
            allowClear: true,
            width: '100%'
        });

        // Inicializa tooltips do Bootstrap
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Auto-submit quando perPage mudar
        $('#perPage').on('change', function() {
            $('#filterForm').submit();
        });
    });
</script>
@endpush
