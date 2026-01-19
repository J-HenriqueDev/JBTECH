@extends('layouts.layoutMaster')

@section('title', 'Nova NF-e Avulsa')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0 text-primary">
        <i class="bx bx-file"></i> Nova NF-e Avulsa
    </h1>
    <a href="{{ route('nfe.index') }}" class="btn btn-outline-secondary">
        <i class="bx bx-arrow-back"></i> Voltar
    </a>
</div>

@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form action="{{ route('nfe.store-avulsa') }}" method="POST" id="form-nfe">
    @csrf

    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Dados Gerais</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Natureza da Operação</label>
                            <select name="natureza_operacao" class="form-select select2" required>
                                <option value="">Selecione...</option>
                                @foreach($naturezas as $nat)
                                <option value="{{ $nat->descricao }}" @if($nat->padrao) selected @endif>{{ $nat->descricao }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tipo de Documento</label>
                            <select name="tipo_documento" class="form-select" required>
                                <option value="1">1 - Saída</option>
                                <option value="0">0 - Entrada</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Destino da Operação</label>
                            <select name="destino_operacao" class="form-select">
                                <option value="1">1 - Interna</option>
                                <option value="2">2 - Interestadual</option>
                                <option value="3">3 - Exterior</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Destinatário / Remetente</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label d-block">Tipo de Destinatário:</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="destinatario_tipo" id="tipo_cliente" value="cliente" checked onchange="toggleDestinatario()">
                            <label class="form-check-label" for="tipo_cliente">Cliente</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="destinatario_tipo" id="tipo_fornecedor" value="fornecedor" onchange="toggleDestinatario()">
                            <label class="form-check-label" for="tipo_fornecedor">Fornecedor</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="destinatario_tipo" id="tipo_proprio" value="proprio" onchange="toggleDestinatario()">
                            <label class="form-check-label" for="tipo_proprio">Próprio (Ajuste/Transferência)</label>
                        </div>
                    </div>

                    <div id="div-cliente" class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label">Selecione o Cliente</label>
                            <a href="{{ route('clientes.create') }}" class="small" target="_blank">
                                <i class="bx bx-plus"></i> Novo
                            </a>
                        </div>
                        <select name="destinatario_id_cliente" id="select_cliente" class="select2 form-select">
                            <option value="">Selecione um cliente...</option>
                            @foreach($clientes as $cliente)
                            <option value="{{ $cliente['id'] }}"
                                data-endereco="{{ json_encode($cliente['endereco']) }}"
                                data-cpfcnpj="{{ $cliente['cpf_cnpj'] }}">
                                #{{ $cliente['id'] }} - {{ $cliente['nome'] }} - {{ $cliente['cpf_cnpj'] }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div id="div-fornecedor" class="mb-3" style="display: none;">
                        <label class="form-label">Selecione o Fornecedor</label>
                        <select name="destinatario_id_fornecedor" id="select_fornecedor" class="select2 form-select">
                            <option value="">Selecione um fornecedor...</option>
                            @foreach($fornecedores as $fornecedor)
                            <option value="{{ $fornecedor->id }}"
                                data-cnpj="{{ $fornecedor->cnpj }}">
                                {{ $fornecedor->nome }} - {{ $fornecedor->cnpj }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <input type="hidden" name="destinatario_id" id="destinatario_id">

                    <hr>
                    <h6>Endereço do Destinatário (Preenchimento Automático ou Manual)</h6>
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label">CEP</label>
                            <div class="input-group">
                                <input type="text" name="destinatario_endereco_cep" id="dest_cep" class="form-control">
                                <button type="button" class="btn btn-outline-primary" onclick="buscarCep()">
                                    <i class="bx bx-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Logradouro</label>
                            <input type="text" name="destinatario_endereco_logradouro" id="dest_logradouro" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Número</label>
                            <input type="text" name="destinatario_endereco_numero" id="dest_numero" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Bairro</label>
                            <input type="text" name="destinatario_endereco_bairro" id="dest_bairro" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Cidade</label>
                            <input type="text" name="destinatario_endereco_cidade" id="dest_cidade" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">UF</label>
                            <input type="text" name="destinatario_endereco_uf" id="dest_uf" class="form-control" maxlength="2">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Produtos</h5>
                    <button type="button" class="btn btn-primary" onclick="abrirModalProduto()">
                        <i class="bx bx-plus"></i> Adicionar Produto
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="table-produtos">
                            <thead>
                                <tr>
                                    <th width="30%">Produto</th>
                                    <th width="10%">Qtd</th>
                                    <th width="15%">Valor Unit.</th>
                                    <th width="10%">CFOP</th>
                                    <th width="15%">Total</th>
                                    <th width="5%">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-produtos">
                                <!-- Preenchido via JS -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">Total da Nota:</td>
                                    <td colspan="2" class="fw-bold">
                                        <span id="total-nota">R$ 0,00</span>
                                        <input type="hidden" id="total_nota_valor" name="total_nota_valor" value="0">
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Pagamento</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Forma de Pagamento</label>
                            <select name="pagamento[forma]" class="form-select">
                                <option value="90">90 - Sem Pagamento</option>
                                <option value="01">01 - Dinheiro</option>
                                <option value="03">03 - Cartão de Crédito</option>
                                <option value="15">15 - Boleto Bancário</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Indicador de Pagamento</label>
                            <select name="pagamento[indicador]" class="form-select">
                                <option value="0">0 - Pagamento à Vista</option>
                                <option value="1">1 - Pagamento a Prazo</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Gerar Parcelas (Qtd. / Intervalo)</label>
                            <div class="input-group">
                                <input type="number" min="1" value="1" class="form-control" id="qtd_duplicatas" placeholder="Qtd">
                                <select class="form-select" id="intervalo_dias">
                                    <option value="30" selected>30 dias</option>
                                    <option value="15">15 dias</option>
                                    <option value="7">7 dias</option>
                                    <option value="0">Mesmo dia</option>
                                </select>
                                <button type="button" class="btn btn-outline-primary" id="btnGerarDuplicatas">Gerar</button>
                            </div>
                        </div>
                    </div>
                    <div id="area-duplicatas" style="display:none;">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th width="10%">#</th>
                                        <th width="40%">Data de Vencimento</th>
                                        <th width="50%">Valor</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-duplicatas">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Observações da Nota</h5>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="toggle-observacoes">
                        Mostrar / Ocultar
                    </button>
                </div>
                <div class="card-body" id="observacoes-container" style="display:none;">
                    <textarea name="infAdic[infCpl]" class="form-control" rows="3" placeholder="Digite observações adicionais para a nota (opcional)"></textarea>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bx bx-check"></i> Emitir NF-e
                </button>
            </div>
        </div>
    </div>
</form>

<!-- Modal para Adicionar Produtos (Custom Implementation without Bootstrap) -->
<div id="customModalProduto" class="custom-modal-overlay">
    <div class="custom-modal-content">
        <div class="custom-modal-header">
            <h5 class="custom-modal-title">Adicionar Produto</h5>
            <button type="button" class="custom-modal-close" onclick="fecharModalProduto()">&times;</button>
        </div>
        <div class="custom-modal-body">
            <div class="row mb-3">
                <div class="col-md-12">
                    <label for="modal_produto_id" class="form-label fw-bold">Produto</label>
                    <select id="modal_produto_id" class="select2 form-select" style="width: 100%">
                        <option value="">Selecione um produto...</option>
                        @foreach($produtos as $prod)
                        <option value="{{ $prod->id }}" data-price="{{ $prod->preco_venda }}" data-nome="{{ $prod->nome }}">
                            {{ $prod->nome }} - R$ {{ number_format($prod->preco_venda, 2, ',', '.') }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="modal_valor_unitario" class="form-label fw-bold">Valor Unitário</label>
                    <div class="input-group">
                        <span class="input-group-text">R$</span>
                        <input type="text" class="form-control" id="modal_valor_unitario" placeholder="0,00">
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="modal_quantidade" class="form-label fw-bold">Quantidade</label>
                    <div class="input-group">
                        <button class="btn btn-outline-secondary" type="button" id="btn-minus-qtd"><i class="bx bx-minus"></i></button>
                        <input type="number" class="form-control text-center" id="modal_quantidade" value="1" min="0.01" step="0.01">
                        <button class="btn btn-outline-secondary" type="button" id="btn-plus-qtd"><i class="bx bx-plus"></i></button>
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="modal_valor_total" class="form-label fw-bold">Subtotal</label>
                    <div class="input-group">
                        <span class="input-group-text">R$</span>
                        <input type="text" class="form-control bg-light" id="modal_valor_total" placeholder="0,00" readonly>
                    </div>
                </div>
            </div>
        </div>
        <div class="custom-modal-footer">
            <button type="button" class="btn btn-secondary" onclick="fecharModalProduto()">Fechar</button>
            <button type="button" class="btn btn-primary" id="btnAdicionarModal">
                <i class="bx bx-plus"></i> Adicionar
            </button>
        </div>
    </div>
</div>

<style>
    /* CSS Custom para Modal sem Bootstrap */
    .custom-modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }

    .custom-modal-content {
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        width: 90%;
        max-width: 800px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        position: relative;
    }

    .custom-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
    }

    .custom-modal-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #566a7f;
        margin: 0;
    }

    .custom-modal-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #566a7f;
    }

    .custom-modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 20px;
        border-top: 1px solid #eee;
        padding-top: 15px;
    }
</style>

<script>
    const emitenteEndereco = @json($emitenteEndereco);

    function buscarCep() {
        let cep = document.getElementById('dest_cep').value.replace(/\D/g, '');
        if (cep.length !== 8) {
            alert('CEP deve conter 8 dígitos.');
            return;
        }

        fetch(`https://viacep.com.br/ws/${cep}/json/`)
            .then(response => response.json())
            .then(data => {
                if (data.erro) {
                    alert('CEP não encontrado.');
                    return;
                }
                document.getElementById('dest_logradouro').value = data.logradouro;
                document.getElementById('dest_bairro').value = data.bairro;
                document.getElementById('dest_cidade').value = data.localidade;
                document.getElementById('dest_uf').value = data.uf;
                document.getElementById('dest_numero').focus();
            })
            .catch(error => {
                console.error('Erro ao buscar CEP:', error);
                alert('Erro ao buscar CEP.');
            });
    }

    let produtoIndex = 0;

    function formatMoney(value) {
        if (value === undefined || value === null || value === '') return 'R$ 0,00';
        const numValue = parseFloat(value);
        if (isNaN(numValue)) return 'R$ 0,00';
        const formatted = Math.abs(numValue).toFixed(2);
        return `R$ ${formatted.replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.')}`;
    }

    function parseCurrency(value) {
        if (!value || value === undefined || value === null) return 0;
        if (typeof value === 'number') return value;
        const cleaned = value.toString().replace('R$', '').replace(/\./g, '').replace(',', '.').trim();
        return parseFloat(cleaned) || 0;
    }

    function formatCurrencyInput(input) {
        let value = input.value.replace(/\D/g, '');
        if (value === '') {
            input.value = '';
            return;
        }
        let intValue = parseInt(value, 10) / 100;
        input.value = intValue.toLocaleString('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        });
    }

    function fecharModalProduto() {
        $('#customModalProduto').css('display', 'none');
    }

    function abrirModalProduto() {
        $('#customModalProduto').css('display', 'flex');
    }

    function updateModalTotal() {
        const qtd = parseFloat($('#modal_quantidade').val()) || 0;
        let valStr = $('#modal_valor_unitario').val();
        const val = parseCurrency(valStr);
        const total = qtd * val;
        $('#modal_valor_total').val(formatMoney(total));
    }

    function adicionarProdutoTabela() {
        const produtoId = $('#modal_produto_id').val();
        const dataArr = $('#modal_produto_id').select2('data');
        const produtoData = dataArr && dataArr.length > 0 ? dataArr[0] : null;

        if (!produtoId || !produtoData) {
            alert('Selecione um produto.');
            return;
        }

        const nomeCompleto = produtoData.text;
        const nome = nomeCompleto.split(' - ')[0];

        // Define default CFOP if not set (fixing the initialization error)
        const cfop = '5102';

        const quantidade = parseFloat($('#modal_quantidade').val()) || 1;
        const valorUnitarioRaw = $('#modal_valor_unitario').val();
        const valorUnitario = parseCurrency(valorUnitarioRaw);
        const subtotal = quantidade * valorUnitario;

        adicionarLinhaTabela(produtoIndex, produtoId, nome, quantidade, valorUnitario, cfop, subtotal);
        produtoIndex++;
        calcTotalNota();

        fecharModalProduto();
        $('#modal_produto_id').val('').trigger('change');
        $('#modal_quantidade').val(1);
        $('#modal_valor_unitario').val('');
        $('#modal_valor_total').val('');
    }

    function adicionarLinhaTabela(index, id, nome, qtd, valor, cfop, total) {
        const tbody = document.getElementById('tbody-produtos');
        const row = document.createElement('tr');
        row.id = `row-${index}`;

        row.innerHTML = `
            <td>
                ${nome}
                <input type="hidden" name="produtos[${index}][id]" value="${id}">
                <input type="hidden" name="produtos[${index}][nome]" value="${nome}">
            </td>
            <td>
                <input type="number" name="produtos[${index}][quantidade]" class="form-control form-control-sm" step="0.01" min="0.01" value="${qtd}" onchange="calcTotal(${index})" required>
            </td>
            <td>
                <input type="number" name="produtos[${index}][valor_unitario]" class="form-control form-control-sm" step="0.01" min="0.01" value="${valor.toFixed(2)}" onchange="calcTotal(${index})" required>
            </td>
            <td>
                <input type="text" name="produtos[${index}][cfop]" class="form-control form-control-sm" maxlength="4" value="${cfop}" required>
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" id="total-${index}" readonly value="${total.toFixed(2)}">
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm p-1" onclick="removeRow('${index}')"><i class="bx bx-trash"></i></button>
            </td>
        `;
        tbody.appendChild(row);
    }

    function calcTotal(index) {
        const qtd = parseFloat($(`input[name="produtos[${index}][quantidade]"]`).val()) || 0;
        const val = parseFloat($(`input[name="produtos[${index}][valor_unitario]"]`).val()) || 0;
        const total = qtd * val;
        document.getElementById(`total-${index}`).value = total.toFixed(2);
        calcTotalNota();
    }

    function removeRow(index) {
        const row = document.getElementById(`row-${index}`);
        if (row) row.remove();
        calcTotalNota();
    }

    function calcTotalNota() {
        let total = 0;
        document.querySelectorAll('[id^="total-"]').forEach(el => {
            total += parseFloat(el.value) || 0;
        });
        document.getElementById('total-nota').innerText = 'R$ ' + total.toLocaleString('pt-BR', {
            minimumFractionDigits: 2
        });
        const hidden = document.getElementById('total_nota_valor');
        if (hidden) {
            hidden.value = total.toFixed(2);
        }
    }

    function toggleDestinatario() {
        const tipo = document.querySelector('input[name="destinatario_tipo"]:checked').value;
        document.getElementById('div-cliente').style.display = tipo === 'cliente' ? 'block' : 'none';
        document.getElementById('div-fornecedor').style.display = tipo === 'fornecedor' ? 'block' : 'none';

        if (tipo === 'cliente') {
            const val = $('#select_cliente').val();
            $('#destinatario_id').val(val || '');
            // Trigger change to populate address if client is already selected
            if (val) $('#select_cliente').trigger('change');
        } else if (tipo === 'fornecedor') {
            const val = $('#select_fornecedor').val();
            $('#destinatario_id').val(val || '');
        } else if (tipo === 'proprio') {
            $('#destinatario_id').val('');

            // Auto-fill address from emitente config
            if (emitenteEndereco) {
                $('#dest_cep').val(emitenteEndereco.cep || '');
                $('#dest_logradouro').val(emitenteEndereco.logradouro || '');
                $('#dest_numero').val(emitenteEndereco.numero || '');
                $('#dest_bairro').val(emitenteEndereco.bairro || '');
                $('#dest_cidade').val(emitenteEndereco.cidade || '');
                $('#dest_uf').val(emitenteEndereco.uf || '');
            }
        } else {
            $('#destinatario_id').val('');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Select2
        $('.select2').select2({
            width: '100%'
        });

        // Initialize Select2 for modal specifically
        $('#modal_produto_id').select2({
            tags: false,
            dropdownParent: $('#customModalProduto'),
            placeholder: 'Selecione um produto...',
            width: '100%',
            allowClear: true
        });

        $('#modal_produto_id').on('change', function() {
            const element = $('#modal_produto_id option:selected');
            const price = element.data('price');
            if (price !== undefined) {
                $('#modal_valor_unitario').val(formatMoney(price));
                updateModalTotal();
            }
        });

        $('#modal_quantidade').on('input', function() {
            updateModalTotal();
        });

        $('#modal_valor_unitario').on('input', function() {
            formatCurrencyInput(this);
            updateModalTotal();
        });

        $('#btn-minus-qtd').click(function() {
            let qtd = parseFloat($('#modal_quantidade').val()) || 0;
            if (qtd > 1) {
                $('#modal_quantidade').val(qtd - 1);
                updateModalTotal();
            }
        });

        $('#btn-plus-qtd').click(function() {
            let qtd = parseFloat($('#modal_quantidade').val()) || 0;
            $('#modal_quantidade').val(qtd + 1);
            updateModalTotal();
        });

        $('#btnAdicionarModal').click(function() {
            adicionarProdutoTabela();
        });

        // Atualiza destinatario_id ao trocar cliente
        $('#select_cliente').on('change', function() {
            const tipo = $('input[name="destinatario_tipo"]:checked').val();
            if (tipo === 'cliente') {
                $('#destinatario_id').val($(this).val() || '');
            }
        });

        // Atualiza destinatario_id ao trocar fornecedor
        $('#select_fornecedor').on('change', function() {
            const tipo = $('input[name="destinatario_tipo"]:checked').val();
            if (tipo === 'fornecedor') {
                $('#destinatario_id').val($(this).val() || '');
            }
        });

        // Populate Address on Client Select
        $('#select_cliente').on('change', function() {
            const selected = $(this).find(':selected');
            const endereco = selected.data('endereco');

            if (endereco) {
                $('#dest_cep').val(endereco.cep || '');
                $('#dest_logradouro').val(endereco.endereco || '');
                $('#dest_numero').val(endereco.numero || '');
                $('#dest_bairro').val(endereco.bairro || '');
                $('#dest_cidade').val(endereco.cidade || '');
                $('#dest_uf').val(endereco.estado || '');
            } else {
                // Clear fields if no address
                $('#dest_cep').val('');
                $('#dest_logradouro').val('');
                $('#dest_numero').val('');
                $('#dest_bairro').val('');
                $('#dest_cidade').val('');
                $('#dest_uf').val('');
            }
        });

        $('#toggle-observacoes').on('click', function() {
            $('#observacoes-container').slideToggle();
        });

        $('#btnGerarDuplicatas').on('click', function() {
            const qtd = parseInt($('#qtd_duplicatas').val(), 10) || 1;
            const intervalo = parseInt($('#intervalo_dias').val(), 10) || 0;
            const totalStr = $('#total_nota_valor').val() || '0';
            const total = parseFloat(totalStr.replace(',', '.')) || 0;

            if (total <= 0) {
                alert('Informe pelo menos um produto para gerar as duplicatas.');
                return;
            }

            $('#area-duplicatas').show();
            const tbody = $('#tbody-duplicatas');
            tbody.empty();

            const base = Math.floor((total / qtd) * 100) / 100;
            let acumulado = 0;

            const dataBase = new Date();

            for (let i = 0; i < qtd; i++) {
                let valorParcela = (i === qtd - 1) ? (total - acumulado) : base;
                valorParcela = parseFloat(valorParcela.toFixed(2));
                acumulado += valorParcela;

                const dataVencimento = new Date(dataBase);
                if (intervalo > 0) {
                    dataVencimento.setDate(dataBase.getDate() + ((i + 1) * intervalo));
                }

                const year = dataVencimento.getFullYear();
                const month = String(dataVencimento.getMonth() + 1).padStart(2, '0');
                const day = String(dataVencimento.getDate()).padStart(2, '0');
                const dataISO = `${year}-${month}-${day}`;

                const tr = $(`
                    <tr>
                        <td>${i + 1}</td>
                        <td>
                            <input type="date" name="pagamento[parcelas][${i}][data]" class="form-control" value="${dataISO}">
                        </td>
                        <td>
                            <input type="number" step="0.01" min="0" name="pagamento[parcelas][${i}][valor]" class="form-control" value="${valorParcela.toFixed(2)}">
                        </td>
                    </tr>
                `);
                tbody.append(tr);
            }
        });

        toggleDestinatario();
    });
</script>
@endsection