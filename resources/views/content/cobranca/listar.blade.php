@extends('layouts.layoutMaster')

@section('title', 'Lista de Cobranças')

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
        <i class="fas fa-money-check-alt"></i> Lista de Cobranças
    </h1>
    <a href="{{ route('cobrancas.create') }}" class="btn btn-primary">
        <i class="fas fa-plus-circle me-1"></i> Nova Cobrança
    </a>
</div>

@if(isset($stats))
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6 class="card-title">Total</h6>
                <h3>{{ $stats['total'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h6 class="card-title">Pendentes</h6>
                <h3>{{ $stats['pendentes'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6 class="card-title">Pagos</h6>
                <h3>{{ $stats['pagos'] }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h6 class="card-title">Valor Pendente</h6>
                <h3>R$ {{ number_format($stats['valor_total'], 2, ',', '.') }}</h3>
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
                    <h5 class="card-title mb-0">Cobranças Cadastradas</h5>
                </div>
            </div>
            <div class="card-body">
                <!-- Filtros -->
                <form method="GET" action="{{ route('cobrancas.index') }}" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <input type="text" name="search" class="form-control" placeholder="Buscar por cliente ou ID..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-select">
                                <option value="">Todos os status</option>
                                <option value="pendente" {{ request('status') == 'pendente' ? 'selected' : '' }}>Pendente</option>
                                <option value="pago" {{ request('status') == 'pago' ? 'selected' : '' }}>Pago</option>
                                <option value="cancelado" {{ request('status') == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="metodo_pagamento" class="form-select">
                                <option value="">Todos os métodos</option>
                                <option value="pix" {{ request('metodo_pagamento') == 'pix' ? 'selected' : '' }}>PIX</option>
                                <option value="boleto" {{ request('metodo_pagamento') == 'boleto' ? 'selected' : '' }}>Boleto</option>
                                <option value="cartao_credito" {{ request('metodo_pagamento') == 'cartao_credito' ? 'selected' : '' }}>Cartão</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="data_inicio" class="form-control" value="{{ request('data_inicio') }}" placeholder="Data início">
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="data_fim" class="form-control" value="{{ request('data_fim') }}" placeholder="Data fim">
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
                <div class="table-responsive text-nowrap">
                    <table class="table table-striped" id="cobrancasTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Venda</th>
                                <th>Método</th>
                                <th>Valor</th>
                                <th>Status</th>
                                <th>Vencimento</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($cobrancas as $cobranca)
                            <tr>
                                <td>{{ $cobranca->id }}</td>
                                <td>
                                    <strong>{{ $cobranca->venda->cliente->nome ?? 'N/A' }}</strong>
                                </td>
                                <td class="cobranca-venda">
                                    <strong>#{{ $cobranca->venda->id ?? 'N/A' }}</strong>
                                </td>
                                <td class="cobranca-metodo">
                                    @if($cobranca->metodo_pagamento == 'pix')
                                        <span class="badge bg-info">PIX</span>
                                    @elseif($cobranca->metodo_pagamento == 'boleto')
                                        <span class="badge bg-warning">Boleto</span>
                                    @else
                                        <span class="badge bg-primary">Cartão</span>
                                    @endif
                                </td>
                                <td class="cobranca-valor"><strong>R$ {{ number_format($cobranca->valor, 2, ',', '.') }}</strong></td>
                                <td class="cobranca-status">
                                    <span class="badge bg-{{ $cobranca->status == 'pago' ? 'success' : ($cobranca->status == 'cancelado' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($cobranca->status) }}
                                    </span>
                                </td>
                                <td class="cobranca-vencimento">
                                    {{ $cobranca->data_vencimento ? \Carbon\Carbon::parse($cobranca->data_vencimento)->format('d/m/Y') : 'N/A' }}
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('cobrancas.show', $cobranca->id) }}" class="btn btn-sm btn-icon btn-outline-info" title="Ver detalhes">
                                            <i class="bx bx-show"></i>
                                        </a>
                                        <a href="{{ route('cobrancas.edit', $cobranca->id) }}" class="btn btn-sm btn-icon btn-outline-primary" title="Editar">
                                            <i class="bx bx-edit-alt"></i>
                                        </a>
                                        @if($cobranca->status == 'pendente')
                                        <form action="{{ route('cobrancas.marcar-paga', $cobranca->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-icon btn-outline-success" title="Marcar como paga">
                                                <i class="bx bx-check"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('cobrancas.cancelar', $cobranca->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-icon btn-outline-secondary" title="Cancelar">
                                                <i class="bx bx-x"></i>
                                            </button>
                                        </form>
                                        @endif
                                        <a href="{{ route('cobrancas.pdf', $cobranca->id) }}" class="btn btn-sm btn-icon btn-outline-danger" title="Baixar PDF" target="_blank">
                                            <i class="bx bxs-file-pdf"></i>
                                        </a>
                                        @if($cobranca->status != 'pago')
                                        <form action="{{ route('cobrancas.destroy', $cobranca->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir esta cobrança?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Excluir">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">Nenhuma cobrança encontrada.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $cobrancas->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Ordena a tabela ao carregar a página
    document.addEventListener('DOMContentLoaded', function () {
        ordenarCobrancas();
    });

    // Filtra as cobranças conforme o texto digitado
    function filterCobrancas() {
        const input = document.getElementById('search');
        const filter = input.value.toLowerCase();
        const table = document.getElementById('cobrancasTable');
        const tr = table.getElementsByTagName('tr');

        for (let i = 1; i < tr.length; i++) { // Começa em 1 para ignorar o cabeçalho
            const tdId = tr[i].getElementsByTagName("td")[0]; // Coluna ID
            const tdVenda = tr[i].getElementsByClassName("cobranca-venda")[0];
            const tdMetodo = tr[i].getElementsByClassName("cobranca-metodo")[0];
            const tdValor = tr[i].getElementsByClassName("cobranca-valor")[0];
            const tdStatus = tr[i].getElementsByClassName("cobranca-status")[0];
            const tdVencimento = tr[i].getElementsByClassName("cobranca-vencimento")[0];

            if (tdId && tdVenda && tdMetodo && tdValor && tdStatus && tdVencimento) {
                const idValue = tdId.textContent || tdId.innerText;
                const vendaValue = tdVenda.textContent || tdVenda.innerText;
                const metodoValue = tdMetodo.textContent || tdMetodo.innerText;
                const valorValue = tdValor.textContent || tdValor.innerText;
                const statusValue = tdStatus.textContent || tdStatus.innerText;
                const vencimentoValue = tdVencimento.textContent || tdVencimento.innerText;

                if (idValue.toLowerCase().indexOf(filter) > -1 ||
                    vendaValue.toLowerCase().indexOf(filter) > -1 ||
                    metodoValue.toLowerCase().indexOf(filter) > -1 ||
                    valorValue.toLowerCase().indexOf(filter) > -1 ||
                    statusValue.toLowerCase().indexOf(filter) > -1 ||
                    vencimentoValue.toLowerCase().indexOf(filter) > -1) {
                    tr[i].style.display = ""; // Exibe a linha
                } else {
                    tr[i].style.display = "none"; // Oculta a linha
                }
            }
        }
    }

    // Ordena as cobranças conforme a opção selecionada
    function ordenarCobrancas() {
        const ordenacao = document.getElementById('ordenacao').value;
        const table = document.getElementById('cobrancasTable');
        const tbody = table.getElementsByTagName('tbody')[0];
        const rows = Array.from(tbody.getElementsByTagName('tr'));

        rows.sort((a, b) => {
            const idA = parseInt(a.getElementsByTagName('td')[0].textContent);
            const idB = parseInt(b.getElementsByTagName('td')[0].textContent);
            const valorA = parseFloat(a.getElementsByClassName('cobranca-valor')[0].textContent.replace('R$ ', '').replace('.', '').replace(',', '.'));
            const valorB = parseFloat(b.getElementsByClassName('cobranca-valor')[0].textContent.replace('R$ ', '').replace('.', '').replace(',', '.'));
            const updatedAtA = new Date(a.getElementsByClassName('cobranca-updated-at')[0].textContent);
            const updatedAtB = new Date(b.getElementsByClassName('cobranca-updated-at')[0].textContent);

            switch (ordenacao) {
                case 'recentes':
                    return updatedAtB - updatedAtA; // Mais recentes primeiro (considerando updated_at)
                case 'antigos':
                    return updatedAtA - updatedAtB; // Mais antigos primeiro (considerando updated_at)
                case 'maior_valor':
                    return valorB - valorA; // Maior valor primeiro
                case 'menor_valor':
                    return valorA - valorB; // Menor valor primeiro
                default:
                    return 0;
            }
        });

        // Remove as linhas atuais da tabela
        while (tbody.firstChild) {
            tbody.removeChild(tbody.firstChild);
        }

        // Adiciona as linhas ordenadas
        rows.forEach(row => tbody.appendChild(row));
    }
</script>

@endsection
