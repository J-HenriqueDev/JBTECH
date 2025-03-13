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
                                <!-- Checkboxes de Filtro -->
                                <li>
                                    <div class="dropdown-item">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="filtroRecusado" value="recusado" {{ in_array('recusado', request('status', [])) ? 'checked' : '' }} onchange="aplicarFiltros()">
                                            <label class="form-check-label" for="filtroRecusado">Recusado</label>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="dropdown-item">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="filtroApagado" value="apagado" {{ in_array('apagado', request('status', [])) ? 'checked' : '' }} onchange="aplicarFiltros()">
                                            <label class="form-check-label" for="filtroApagado">Apagado</label>
                                        </div>
                                    </div>
                                </li>
                                <li><hr class="dropdown-divider"></li>
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
                <div class="mb-4">
                    <!-- Barra de Pesquisa -->
                    <input type="text" id="search" class="form-control" placeholder="Pesquisar orçamentos..." value="{{ request('search') }}" onkeyup="aplicarFiltros()">
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
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orcamentos as $orcamento)
                                <tr>
                                    <td>{{ $orcamento->id }}</td>
                                    <td class="orcamento-cliente">
                                        <strong>{{ \Illuminate\Support\Str::limit($orcamento->cliente->nome ?? 'Cliente não encontrado', 40, '...') }}</strong>
                                    </td>
                                    <td class="orcamento-data">{{ \Carbon\Carbon::parse($orcamento->data)->format('d/m/Y') }}</td>
                                    <td class="orcamento-validade">{{ \Carbon\Carbon::parse($orcamento->validade)->format('d/m/Y') }}</td>
                                    <td class="orcamento-valor"><strong>R$ {{ number_format($orcamento->valor_total, 2, ',', '.') }}</strong></td>
                                    <td class="orcamento-status">
                                        <span class="badge bg-{{ $orcamento->status == 'autorizado' ? 'success' : ($orcamento->status == 'recusado' ? 'danger' : ($orcamento->status == 'apagado' ? 'secondary' : 'warning')) }}">
                                            {{ ucfirst($orcamento->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('orcamentos.edit', $orcamento->id) }}" class="btn btn-info">
                                            <i class="fas fa-eye"></i> Ver / Editar
                                        </a>
                                        <form action="{{ route('orcamentos.destroy', $orcamento->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">Excluir</button>
                                        </form>
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
        const search = document.getElementById('search').value;
        const filtroRecusado = document.getElementById('filtroRecusado').checked;
        const filtroApagado = document.getElementById('filtroApagado').checked;
        const ordenacao = document.querySelector('input[name="ordenacao"]:checked')?.value;

        // Monta a URL com os parâmetros de filtro
        let url = '{{ route("orcamentos.index") }}?';
        if (search) url += `search=${search}&`;
        if (filtroRecusado) url += `status[]=recusado&`;
        if (filtroApagado) url += `status[]=apagado&`;
        if (ordenacao) url += `ordenacao=${ordenacao}&`;

        // Remove o último "&" se houver
        if (url.endsWith('&')) url = url.slice(0, -1);

        // Redireciona para a URL com os filtros aplicados
        window.location.href = url;
    }
</script>

@endsection
