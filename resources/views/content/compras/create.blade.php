@extends('layouts.layoutMaster')

@section('title', 'Nova Solicitação de Compra')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Registrar Nova Solicitação de Compra</h5>
                <small class="text-muted">Preencha os dados abaixo</small>
            </div>
            <div class="card-body">
                <form action="{{ route('compras.store') }}" method="POST">
                    @csrf

                    <!-- Seção 1: Informações Básicas -->
                    <h6 class="mb-3 text-primary"><i class="bx bx-info-circle me-1"></i> Dados da Solicitação</h6>
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold" for="tipo">Tipo de Solicitação</label>
                            <select class="form-select" id="tipo" name="tipo" required>
                                <option value="reposicao">Reposição de Estoque</option>
                                <option value="inovacao">Inovação / Novos Produtos</option>
                                <option value="uso_interno">Uso Interno / Material</option>
                            </select>
                            <div class="form-text">Qual o objetivo desta compra?</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold" for="prioridade">Prioridade</label>
                            <select class="form-select" id="prioridade" name="prioridade" required>
                                <option value="baixa">Baixa - Pode aguardar</option>
                                <option value="media" selected>Média - Necessário em breve</option>
                                <option value="alta">Alta - Urgente / Venda Travada</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold" for="data_compra">Data da Solicitação</label>
                            <input type="date" class="form-control" id="data_compra" name="data_compra" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <!-- Seção 2: Origem e Destino -->
                    <h6 class="mb-3 text-primary"><i class="bx bx-store me-1"></i> Origem e Destino</h6>
                    <div class="row mb-4 p-3 bg-lighter rounded border mx-1">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="fornecedor_id">Fornecedor Preferencial (Opcional)</label>
                            <select class="form-select select2" id="fornecedor_id" name="fornecedor_id">
                                <option value="">Selecione se souber...</option>
                                @foreach($fornecedores as $fornecedor)
                                <option value="{{ $fornecedor->id }}">{{ $fornecedor->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="local_compra">Local de Compra (Alternativo)</label>
                            <input type="text" class="form-control" id="local_compra" name="local_compra" placeholder="Ex: Amazon, Mercado Livre, Loja Física..." value="{{ old('local_compra') }}">
                            <div class="form-text">Preencha caso não seja um fornecedor cadastrado.</div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="check_encomenda" onchange="toggleCliente()">
                                <label class="form-check-label fw-bold" for="check_encomenda">Esta compra é uma encomenda para um cliente?</label>
                            </div>
                            <div id="div_cliente" class="d-none animate__animated animate__fadeIn">
                                <label class="form-label" for="cliente_id">Cliente Solicitante</label>
                                <select class="form-select select2" id="cliente_id" name="cliente_id">
                                    <option value="">Selecione o cliente...</option>
                                    @foreach($clientes as $cliente)
                                    <option value="{{ $cliente->id }}">{{ $cliente->nome }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden Status (Default Solicitado) -->
                    <input type="hidden" name="status" value="solicitado">

                    <!-- Seção 3: Itens -->
                    <h6 class="mb-3 text-primary"><i class="bx bx-list-ul me-1"></i> Itens da Compra</h6>
                    <div class="card bg-light border mb-4">
                        <div class="card-body p-2">
                            <div id="items-container">
                                <div class="row mb-2 item-row bg-white p-3 rounded shadow-sm mx-1 align-items-end">
                                    <div class="col-12 mb-2 d-flex justify-content-between">
                                        <span class="badge bg-label-primary item-number">Item #1</span>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input item-type-switch" type="checkbox" role="switch">
                                            <label class="form-check-label">Item Manual (Não Cadastrado)</label>
                                        </div>
                                    </div>

                                    <div class="col-md-5 product-select-container mb-2">
                                        <label class="form-label">Produto do Sistema</label>
                                        <select class="form-select item-produto" name="items[0][produto_id]">
                                            <option value="">Buscar produto...</option>
                                            @foreach($produtos as $produto)
                                            <option value="{{ $produto->id }}">{{ $produto->nome }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-5 product-text-container d-none mb-2">
                                        <label class="form-label">Descrição do Item</label>
                                        <input type="text" class="form-control item-descricao" name="items[0][descricao_livre]" placeholder="Ex: Novo modelo de teclado...">
                                    </div>

                                    <div class="col-md-2 mb-2">
                                        <label class="form-label">Quantidade</label>
                                        <input type="number" class="form-control item-qtd" name="items[0][quantidade]" min="1" value="1" required>
                                    </div>

                                    <div class="col-md-3 mb-2">
                                        <label class="form-label">Valor Estimado (Un.)</label>
                                        <div class="input-group">
                                            <span class="input-group-text">R$</span>
                                            <input type="number" step="0.01" class="form-control item-valor" name="items[0][valor_unitario]" min="0">
                                        </div>
                                    </div>

                                    <div class="col-md-2 mb-2 text-end">
                                        <button type="button" class="btn btn-label-danger btn-remove-item" disabled>
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center mt-3">
                                <button type="button" class="btn btn-outline-primary" id="btn-add-item">
                                    <i class="bx bx-plus me-1"></i> Adicionar Outro Item
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold" for="observacoes">Observações Gerais</label>
                        <textarea class="form-control" id="observacoes" name="observacoes" rows="3" placeholder="Detalhes adicionais, justificativas, links de referência..."></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('compras.index') }}" class="btn btn-label-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bx bx-save me-1"></i> Registrar Solicitação
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleCliente() {
        const check = document.getElementById('check_encomenda');
        const div = document.getElementById('div_cliente');
        const select = document.getElementById('cliente_id');

        if (check.checked) {
            div.classList.remove('d-none');
        } else {
            div.classList.add('d-none');
            select.value = '';
            // If using select2, trigger change
            $(select).trigger('change');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        let itemIndex = 1;
        const container = document.getElementById('items-container');
        const btnAdd = document.getElementById('btn-add-item');

        // Function to initialize row events
        function initRowEvents(row) {
            const switchInput = row.querySelector('.item-type-switch');
            const selectContainer = row.querySelector('.product-select-container');
            const textContainer = row.querySelector('.product-text-container');
            const select = row.querySelector('.item-produto');
            const input = row.querySelector('.item-descricao');

            switchInput.addEventListener('change', function() {
                if (this.checked) {
                    selectContainer.classList.add('d-none');
                    textContainer.classList.remove('d-none');
                    select.value = ''; // Reset select
                    input.required = true;
                    select.required = false;
                } else {
                    selectContainer.classList.remove('d-none');
                    textContainer.classList.add('d-none');
                    input.value = ''; // Reset text
                    input.required = false;
                    select.required = true;
                }
            });

            // Initialize required state based on initial switch state (unchecked by default)
            select.required = true;
            input.required = false;

            const removeBtn = row.querySelector('.btn-remove-item');
            removeBtn.addEventListener('click', function() {
                if (container.querySelectorAll('.item-row').length > 1) {
                    row.remove();
                    updateItemNumbers();
                }
            });
        }

        function updateItemNumbers() {
            container.querySelectorAll('.item-row').forEach((row, index) => {
                row.querySelector('.item-number').textContent = `Item #${index + 1}`;
            });
        }

        // Initialize first row
        initRowEvents(container.querySelector('.item-row'));

        btnAdd.addEventListener('click', function() {
            const firstRow = container.querySelector('.item-row');
            const newRow = firstRow.cloneNode(true);

            // Reset values
            newRow.querySelectorAll('input:not([type="checkbox"]), select').forEach(el => el.value = '');
            newRow.querySelector('.item-type-switch').checked = false; // Reset switch

            // Reset visibility
            newRow.querySelector('.product-select-container').classList.remove('d-none');
            newRow.querySelector('.product-text-container').classList.add('d-none');

            // Update names
            newRow.querySelector('.item-produto').name = `items[${itemIndex}][produto_id]`;
            newRow.querySelector('.item-descricao').name = `items[${itemIndex}][descricao_livre]`;
            newRow.querySelector('.item-qtd').name = `items[${itemIndex}][quantidade]`;
            newRow.querySelector('.item-valor').name = `items[${itemIndex}][valor_unitario]`;

            // Enable remove button
            newRow.querySelector('.btn-remove-item').disabled = false;

            container.appendChild(newRow);
            initRowEvents(newRow);
            updateItemNumbers();

            itemIndex++;
        });
    });
</script>
@endsection