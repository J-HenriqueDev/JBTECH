@extends('layouts.layoutMaster')

@section('title', 'Contas a Pagar')

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="text-primary" style="font-size: 2.5rem; font-weight: bold; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);">
        <i class="bx bx-money-withdraw"></i> Contas a Pagar
    </h1>
    <a href="{{ route('contas-pagar.create') }}" class="btn btn-primary">
        <i class="fas fa-plus-circle me-1"></i> Nova Conta
    </a>
</div>

<!-- Estatísticas -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h6 class="card-title text-white">Vence Hoje</h6>
                <h3>R$ {{ number_format($stats['total_hoje'], 2, ',', '.') }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h6 class="card-title text-white">Em Atraso</h6>
                <h3>R$ {{ number_format($stats['total_atrasado'], 2, ',', '.') }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Actions Sidebar -->
<div id="bulk-actions-sidebar" class="bg-white shadow-lg p-3 border-start" style="position: fixed; top: 100px; right: -300px; width: 280px; height: calc(100% - 100px); transition: right 0.3s ease; z-index: 1050; overflow-y: auto;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Ações em Massa</h5>
        <button type="button" class="btn-close" id="close-sidebar"></button>
    </div>

    <div class="mb-4">
        <div class="d-flex align-items-center mb-2">
            <span class="badge bg-label-primary fs-5 me-2" id="selected-count">0</span>
            <span class="text-muted">itens selecionados</span>
        </div>
        <div class="progress" style="height: 4px;">
            <div class="progress-bar" role="progressbar" style="width: 0%"></div>
        </div>
    </div>

    <div class="d-grid gap-2">
        <div class="mb-3">
            <label class="form-label small fw-bold">Data do Pagamento</label>
            <input type="date" class="form-control" id="bulk-data-pagamento" value="{{ date('Y-m-d') }}">
        </div>
        <div class="mb-3">
            <label class="form-label small fw-bold">Método de Pagamento</label>
            <select class="form-select" id="bulk-metodo-pagamento">
                <option value="pix">PIX</option>
                <option value="boleto">Boleto</option>
                <option value="transferencia">Transferência</option>
                <option value="dinheiro">Dinheiro</option>
                <option value="cartao_credito">Cartão de Crédito</option>
                <option value="cartao_debito">Cartão de Débito</option>
            </select>
        </div>
        <button type="button" class="btn btn-success" id="btn-bulk-pay">
            <i class="bx bx-check-double me-1"></i> Baixar Selecionados
        </button>
    </div>

    <hr class="my-4">

    <div class="alert alert-secondary mb-0 p-2">
        <small class="d-block fw-bold mb-1"><i class="bx bx-keyboard me-1"></i> Atalhos</small>
        <ul class="list-unstyled small mb-0 ps-3" style="list-style-type: disc;">
            <li><kbd>F4</kbd> Selecionar Tudo</li>
        </ul>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">Listagem de Contas</h5>
            </div>

            <div class="card-body">
                <!-- Filtros -->
                <form method="GET" class="mb-4">
                    <div class="row g-2">
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Data Inicial</label>
                            <input type="date" name="data_inicio" class="form-control" value="{{ $dataInicio ?? request('data_inicio') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Data Final</label>
                            <input type="date" name="data_fim" class="form-control" value="{{ $dataFim ?? request('data_fim') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Status</label>
                            <select name="status" class="form-select">
                                <option value="">Todos</option>
                                <option value="pendente" {{ request('status') == 'pendente' ? 'selected' : '' }}>Pendente</option>
                                <option value="pago" {{ request('status') == 'pago' ? 'selected' : '' }}>Pago</option>
                                <option value="cancelado" {{ request('status') == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Pesquisa</label>
                            <input type="text" name="search" class="form-control" placeholder="Descrição ou fornecedor" value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-50 me-2" title="Filtrar">
                                <i class="fas fa-search"></i>
                            </button>
                            <a href="{{ route('contas-pagar.exportar', request()->all()) }}" class="btn btn-outline-secondary w-50" title="Exportar CSV" target="_blank">
                                <i class="fas fa-file-export"></i>
                            </a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive text-nowrap">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th style="width: 40px;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="select-all">
                                    </div>
                                </th>
                                <th>Vencimento</th>
                                <th>Descrição</th>
                                <th>Fornecedor</th>
                                <th>Valor</th>
                                <th>Status</th>
                                <th>Origem</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($contas as $conta)
                            <tr class="conta-row" data-id="{{ $conta->id }}">
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input item-checkbox" type="checkbox" value="{{ $conta->id }}">
                                    </div>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($conta->data_vencimento)->format('d/m/Y') }}</td>
                                <td>{{ $conta->descricao }}</td>
                                <td>{{ $conta->fornecedor ? $conta->fornecedor->nome : 'N/A' }}</td>
                                <td>R$ {{ number_format($conta->valor, 2, ',', '.') }}</td>
                                <td>
                                    @if($conta->status == 'pendente')
                                    <span class="badge bg-warning">Pendente</span>
                                    @elseif($conta->status == 'pago')
                                    <span class="badge bg-success">Pago</span>
                                    @elseif($conta->status == 'atrasado')
                                    <span class="badge bg-danger">Atrasado</span>
                                    @else
                                    <span class="badge bg-secondary">{{ ucfirst($conta->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($conta->origem == 'importacao_nfe')
                                    <span class="badge bg-label-info">Importação NFe</span>
                                    @else
                                    <span class="badge bg-label-primary">Manual</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex">
                                        @if($conta->status != 'pago' && $conta->status != 'cancelado')
                                        <form action="{{ route('contas-pagar.marcar-paga', $conta->id) }}" method="POST" class="me-1">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-icon btn-success" title="Marcar como Pago" onclick="return confirm('Confirmar pagamento desta conta?')">
                                                <i class="bx bx-check"></i>
                                            </button>
                                        </form>
                                        @endif
                                        <a href="{{ route('contas-pagar.edit', $conta->id) }}" class="btn btn-sm btn-icon btn-outline-primary" title="Editar"><i class="bx bx-edit-alt"></i></a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">Nenhuma conta encontrada.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $contas->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('select-all');
        const itemCheckboxes = document.querySelectorAll('.item-checkbox');
        const sidebar = document.getElementById('bulk-actions-sidebar');
        const selectedCountSpan = document.getElementById('selected-count');
        const closeSidebarBtn = document.getElementById('close-sidebar');
        const btnBulkPay = document.getElementById('btn-bulk-pay');

        function updateSidebar() {
            const selected = document.querySelectorAll('.item-checkbox:checked');
            const count = selected.length;

            selectedCountSpan.textContent = count;

            if (count > 0) {
                sidebar.style.right = '0';
            } else {
                sidebar.style.right = '-300px';
            }
        }

        // Event Listeners for Checkboxes
        selectAllCheckbox.addEventListener('change', function() {
            itemCheckboxes.forEach(cb => cb.checked = this.checked);
            updateSidebar();
        });

        itemCheckboxes.forEach(cb => {
            cb.addEventListener('change', updateSidebar);
        });

        // Close Sidebar Button
        closeSidebarBtn.addEventListener('click', function() {
            sidebar.style.right = '-300px';
            // Optional: Uncheck all? The user might just want to hide it.
            // keeping selection for now.
        });

        // F4 Shortcut
        document.addEventListener('keydown', function(e) {
            if (e.key === 'F4') {
                e.preventDefault();
                const allChecked = Array.from(itemCheckboxes).every(cb => cb.checked);
                itemCheckboxes.forEach(cb => cb.checked = !allChecked);
                selectAllCheckbox.checked = !allChecked;
                updateSidebar();
            }
        });

        // Bulk Pay Action
        btnBulkPay.addEventListener('click', function() {
            const selectedIds = Array.from(document.querySelectorAll('.item-checkbox:checked')).map(cb => cb.value);
            const dataPagamento = document.getElementById('bulk-data-pagamento').value;
            const metodoPagamento = document.getElementById('bulk-metodo-pagamento').value;

            if (selectedIds.length === 0) return;
            if (!dataPagamento) {
                alert('Por favor, informe a data do pagamento.');
                return;
            }

            if (!confirm(`Confirma a baixa de ${selectedIds.length} contas selecionadas?`)) return;

            // Send AJAX request
            fetch('{{ route("contas-pagar.bulk-pay") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        ids: selectedIds,
                        data_pagamento: dataPagamento,
                        metodo_pagamento: metodoPagamento
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success toast or alert
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Erro ao processar baixa em massa.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Ocorreu um erro ao processar a solicitação.');
                });
        });
    });
</script>
@endsection