@extends('layouts.layoutMaster')

@section('title', 'Lista de Orçamentos')

@section('content')

@if(session('success'))
<div class="alert alert-primary alert-dismissible" role="alert">
    <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">
        <i class="fas fa-check-circle me-1"></i> Sucesso!
    </h6>
    <p class="mb-0">{!! session('success') !!}</p>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0 text-primary" style="font-size: 2.5rem; font-weight: bold; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);">
        <i class="fas fa-file-alt"></i> Lista de Orçamentos
    </h1>
    <a href="{{ route('orcamentos.create') }}" class="btn btn-primary">
        <i class="fas fa-plus-circle me-1"></i> Novo Orçamento
    </a>
</div>

<!-- Estatísticas -->
@if(isset($stats))
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6 class="card-title">Total de Orçamentos</h6>
                <h3>{{ $stats['total'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h6 class="card-title">Pendentes</h6>
                <h3>{{ $stats['pendentes'] }}</h3>
                <small class="d-block mt-1">R$ {{ number_format($stats['valor_total_pendente'], 2, ',', '.') }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6 class="card-title">Autorizados</h6>
                <h3>{{ $stats['autorizados'] }}</h3>
                <small class="d-block mt-1">R$ {{ number_format($stats['valor_total_autorizado'], 2, ',', '.') }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h6 class="card-title">Vencidos</h6>
                <h3>{{ $stats['vencidos'] }}</h3>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Orçamentos Cadastrados ( {{ $orcamentos->total() }} )</h5>
                    <div class="d-flex align-items-center">
                        <!-- Dropdown de Filtros e Ordenação -->
                        <div class="dropdown">
                            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownFiltros" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-filter"></i> Filtrar/Ordenar
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownFiltros">
                                <!-- Opções de Ordenação -->
                                <li>
                                    <div class="dropdown-item">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="ordenacao" id="ordenacaoRecentes" value="recentes" {{ request('ordenacao', 'recentes') == 'recentes' ? 'checked' : '' }} onchange="aplicarFiltros()">
                                            <label class="form-check-label" for="ordenacaoRecentes">Mais recentes primeiro</label>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="dropdown-item">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="ordenacao" id="ordenacaoAntigos" value="antigos" {{ request('ordenacao') == 'antigos' ? 'checked' : '' }} onchange="aplicarFiltros()">
                                            <label class="form-check-label" for="ordenacaoAntigos">Mais antigos primeiro</label>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="dropdown-item">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="ordenacao" id="ordenacaoMaiorValor" value="maior_valor" {{ request('ordenacao') == 'maior_valor' ? 'checked' : '' }} onchange="aplicarFiltros()">
                                            <label class="form-check-label" for="ordenacaoMaiorValor">Maior valor primeiro</label>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="dropdown-item">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="ordenacao" id="ordenacaoMenorValor" value="menor_valor" {{ request('ordenacao') == 'menor_valor' ? 'checked' : '' }} onchange="aplicarFiltros()">
                                            <label class="form-check-label" for="ordenacaoMenorValor">Menor valor primeiro</label>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <!-- Seção de Filtros -->
                <div class="card border mb-3" style="background-color: #f8f9fa;">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-filter text-primary me-2"></i>
                            <h6 class="mb-0 fw-bold">Filtros de Busca</h6>
                        </div>
                        <form method="GET" action="{{ route('orcamentos.index') }}" id="filtrosForm">
                            <div class="row g-3">
                                <!-- Campo de Pesquisa -->
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-search text-muted me-1"></i> Pesquisar
                                    </label>
                                    <input type="text" name="search" id="search" class="form-control" placeholder="ID, Cliente ou CPF/CNPJ" value="{{ request('search') }}">
                                </div>
                                
                                <!-- Status -->
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-tag text-muted me-1"></i> Status
                                    </label>
                                    <select name="status[]" class="form-select" multiple size="4">
                                        <option value="pendente" {{ in_array('pendente', request('status', [])) ? 'selected' : '' }}>Pendente</option>
                                        <option value="autorizado" {{ in_array('autorizado', request('status', [])) ? 'selected' : '' }}>Autorizado</option>
                                        <option value="recusado" {{ in_array('recusado', request('status', [])) ? 'selected' : '' }}>Recusado</option>
                                        <option value="apagado" {{ in_array('apagado', request('status', [])) ? 'selected' : '' }}>Apagado</option>
                                    </select>
                                    <small class="text-muted">Segure Ctrl para múltipla seleção</small>
                                </div>
                                
                                <!-- Data Início -->
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-calendar-alt text-muted me-1"></i> Data Início
                                    </label>
                                    <input type="date" name="data_inicio" class="form-control" value="{{ request('data_inicio') }}">
                                </div>
                                
                                <!-- Data Fim -->
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-calendar-check text-muted me-1"></i> Data Fim
                                    </label>
                                    <input type="date" name="data_fim" class="form-control" value="{{ request('data_fim') }}">
                                </div>
                                
                                <!-- Botões -->
                                <div class="col-md-1 d-flex flex-column justify-content-end">
                                    <button type="submit" class="btn btn-primary mb-2" title="Aplicar Filtros">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <a href="{{ route('orcamentos.index') }}" class="btn btn-outline-secondary" title="Limpar Filtros">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Status Selecionados (Badges) -->
                            @php
                                $statusSelecionados = request('status', []);
                            @endphp
                            @if(!empty($statusSelecionados))
                            <div class="mt-3 pt-3 border-top">
                                <small class="text-muted d-block mb-2">
                                    <i class="fas fa-check-circle me-1"></i> Status selecionados:
                                </small>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($statusSelecionados as $status)
                                    @php
                                        $statusArray = $statusSelecionados;
                                        $key = array_search($status, $statusArray);
                                        unset($statusArray[$key]);
                                        $novoRequest = request()->except(['status']);
                                        if (!empty($statusArray)) {
                                            $novoRequest['status'] = array_values($statusArray);
                                        }
                                    @endphp
                                    <span class="badge bg-{{ $status == 'autorizado' ? 'success' : ($status == 'recusado' ? 'danger' : ($status == 'apagado' ? 'secondary' : 'warning')) }} d-inline-flex align-items-center">
                                        {{ ucfirst($status) }}
                                        <a href="{{ route('orcamentos.index', $novoRequest) }}" class="text-white ms-2" style="text-decoration: none; font-size: 1.1em; line-height: 1;" title="Remover filtro">×</a>
                                    </span>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                            
                            <input type="hidden" name="ordenacao" value="{{ request('ordenacao', 'recentes') }}">
                        </form>
                    </div>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-striped" id="orcamentosTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Data</th>
                                <th>Validade</th>
                                <th>Valor Total</th>
                                <th>Status</th>
                                <th>Criado por</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orcamentos as $orcamento)
                                <tr>
                                    <td><strong>#{{ $orcamento->id }}</strong></td>
                                    <td class="orcamento-cliente">
                                        <strong>{{ \Illuminate\Support\Str::limit($orcamento->cliente->nome ?? 'Cliente não encontrado', 40, '...') }}</strong>
                                    </td>
                                    <td class="orcamento-data">{{ \Carbon\Carbon::parse($orcamento->data)->format('d/m/Y') }}</td>
                                    <td class="orcamento-validade">
                                        {{ \Carbon\Carbon::parse($orcamento->validade)->format('d/m/Y') }}
                                        @if($orcamento->validade < now() && $orcamento->status == 'pendente')
                                        <span class="badge bg-danger ms-1">Vencido</span>
                                        @endif
                                    </td>
                                    <td class="orcamento-valor"><strong>R$ {{ number_format($orcamento->valor_total, 2, ',', '.') }}</strong></td>
                                    <td class="orcamento-status">
                                        <span class="badge bg-{{ $orcamento->status == 'autorizado' ? 'success' : ($orcamento->status == 'recusado' ? 'danger' : ($orcamento->status == 'apagado' ? 'secondary' : 'warning')) }}">
                                            {{ ucfirst($orcamento->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $orcamento->usuario->name ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="{{ route('orcamentos.show', $orcamento->id) }}">
                                                    <i class="bx bx-show me-1"></i> Ver Detalhes
                                                </a>
                                                <a class="dropdown-item" href="{{ route('orcamentos.edit', $orcamento->id) }}">
                                                    <i class="bx bx-edit-alt me-1"></i> Editar
                                                </a>
                                                <a class="dropdown-item" href="{{ route('orcamentos.gerarPdf', $orcamento->id) }}" target="_blank">
                                                    <i class="bx bx-file me-1"></i> Gerar PDF
                                                </a>
                                                <div class="dropdown-divider"></div>
                                                <form action="{{ route('orcamentos.destroy', $orcamento->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este orçamento?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="bx bx-trash me-1"></i> Excluir
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Links de Paginação -->
<div class="d-flex justify-content-center">
    {{ $orcamentos->appends(request()->query())->links() }}
</div>

<!-- Script para Aplicar Filtros -->
<script>
    function aplicarFiltros() {
        document.getElementById('filtrosForm').submit();
    }
</script>

@endsection
