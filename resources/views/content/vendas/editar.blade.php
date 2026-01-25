@extends('layouts.layoutMaster')

@section('title', 'Ver/Editar Venda')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/typeahead-js/typeahead.scss', 'resources/assets/vendor/libs/swiper/swiper.scss'])
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/swiper/swiper.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/forms-selects.js'])
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0 text-primary"
            style="font-size: 2.5rem; font-weight: bold; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);">
            <i class="bx bx-edit"></i> Pedido de Venda #{{ $venda->id }}
            <span class="badge bg-success ms-2">
                <i class="bx bx-check-circle"></i> Ativa
            </span>
        </h1>
        <div>
            <!-- Botão para Emitir NF-e -->
            @if ($venda->bloqueado)
                <a href="{{ route('nfe.create', ['venda_id' => $venda->id]) }}" class="btn btn-secondary">
                    <i class="bx bx-receipt"></i> Ver Emissão NF-e
                </a>
            @else
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalPagamentoNfe">
                    <i class="bx bx-receipt"></i> Emitir NF-e
                </button>
            @endif
        </div>
    </div>

    <div class="card mb-4">
        @if ($venda->bloqueado)
            <div class="card-body pb-0">
                <div class="alert alert-warning mb-0">
                    <i class="bx bx-lock-alt"></i> <strong>Venda Bloqueada:</strong> Esta venda foi finalizada para emissão
                    de NF-e e não pode ser alterada.
                </div>
            </div>
        @endif
        <form action="{{ route('vendas.update', $venda->id) }}" method="POST" id="formEditarVenda">
            @csrf
            @method('PUT')
            <input type="hidden" id="vendaId" value="{{ $venda->id }}">
            <div id="produtosHidden"></div>
            <div class="card-body">
                <!-- Status da Venda -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <!-- Primeira Linha: Cliente e Data -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label for="select2Cliente" class="form-label">
                                        <i class="bx bx-id-card"></i> Cliente
                                    </label>
                                    <a href="{{ route('clientes.create') }}" class="small" target="_blank">
                                        <i class="bx bx-plus"></i> Novo
                                    </a>
                                </div>
                                <select id="select2Cliente" class="select2 form-select" name="cliente_id" required
                                    {{ $venda->bloqueado ? 'disabled' : '' }}>
                                    <option value="" disabled>Selecione um cliente</option>
                                    @foreach ($clientes as $cliente)
                                        <option value="{{ $cliente->id }}" data-email="{{ $cliente->email }}"
                                            {{ $venda->cliente_id == $cliente->id ? 'selected' : '' }}>
                                            #{{ $cliente->id }} - {{ $cliente->nome }} - {{ $cliente->cpf_cnpj }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="data_venda" class="form-label">
                                    <i class="bx bx-calendar"></i> Data da Venda
                                </label>
                                <input type="date" class="form-control" id="data_venda" name="data_venda"
                                    value="{{ $venda->data_venda }}" required {{ $venda->bloqueado ? 'disabled' : '' }}>
                            </div>
                        </div>

                        <!-- Segunda Linha: Pagamento -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="forma_pagamento_display" class="form-label">
                                    <i class="bx bx-money"></i> Forma de Pagamento
                                </label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="forma_pagamento_display"
                                        value="{{ ucfirst(str_replace('_', ' ', $venda->forma_pagamento ?? 'Selecione...')) }}"
                                        readonly
                                        style="background-color: #fff; cursor: {{ $venda->bloqueado ? 'not-allowed' : 'pointer' }};"
                                        @if (!$venda->bloqueado) onclick="new bootstrap.Modal(document.getElementById('modalSelecionarPagamento')).show()" @endif
                                        {{ $venda->bloqueado ? 'disabled' : '' }}>
                                    <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal"
                                        data-bs-target="#modalSelecionarPagamento"
                                        {{ $venda->bloqueado ? 'disabled' : '' }}>
                                        <i class="bx bx-list-ul"></i>
                                    </button>
                                </div>
                                <input type="hidden" name="forma_pagamento" id="forma_pagamento"
                                    value="{{ $venda->forma_pagamento }}">
                            </div>
                            <div class="col-md-6">
                                <label for="status_pagamento" class="form-label">
                                    <i class="bx bx-check-circle"></i> Status do Pagamento (Pedido)
                                </label>
                                <select id="status_pagamento" class="form-select" name="status"
                                    {{ $venda->bloqueado ? 'disabled' : '' }}>
                                    <option value="pendente" {{ $venda->status == 'pendente' ? 'selected' : '' }}>Pendente
                                    </option>
                                    <option value="pago" {{ $venda->status == 'pago' ? 'selected' : '' }}>Pago /
                                        Concluído</option>
                                    <option value="cancelado" {{ $venda->status == 'cancelado' ? 'selected' : '' }}>
                                        Cancelado</option>
                                    <option value="orcamento" {{ $venda->status == 'orcamento' ? 'selected' : '' }}>
                                        Orçamento</option>
                                </select>
                            </div>
                        </div>

                        <!-- Seção de Produtos -->
                        <div class="divider my-6">
                            <div class="divider-text"><i class="fas fa-briefcase"></i> Produtos</div>
                        </div>

                        <!-- Quick Add Section (Added for NFe alignment) -->
                        @if (!$venda->bloqueado)
                            <div class="mb-3 p-3 bg-light rounded border">
                                <label for="inputQuickAdd" class="form-label fw-bold text-primary">
                                    <i class="bx bx-bolt-circle"></i> Adição Rápida
                                </label>
                                <div class="input-group">
                                    <input type="text" id="inputQuickAdd" class="form-control"
                                        placeholder="Digite: Qtd * Código * Preço (ex: 2 * 789 * 10,00) ou Código de Barras"
                                        autofocus>
                                    <button type="button" id="btnQuickAdd" class="btn btn-primary">
                                        <i class="bx bx-plus"></i> Adicionar
                                    </button>
                                </div>
                                <div class="form-text">Pressione ENTER para adicionar automaticamente.</div>
                            </div>
                        @endif

                        <div class="mb-3">
                            @if (!$venda->bloqueado)
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#modalAdicionarProduto">
                                    <i class="bx bx-plus-circle"></i> Pesquisar Produto
                                </button>
                            @endif
                        </div>
                        <div class="table-responsive mb-3">
                            <table class="table table-bordered" id="tabelaProdutos">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nome</th>
                                        <th class="text-center">Quantidade</th>
                                        <th class="text-center">Valor Unitário</th>
                                        <th>Valor Total</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($venda->produtos->isEmpty())
                                        <tr id="tabelaVazia">
                                            <td colspan="6" class="text-center">
                                                <div class="alert alert-info" role="alert">
                                                    Nenhum produto adicionado.
                                                </div>
                                            </td>
                                        </tr>
                                    @else
                                        @foreach ($venda->produtos as $produto)
                                            <tr data-produto-id="{{ $produto->id }}">
                                                <td>{{ $produto->id }}</td>
                                                <td>{{ $produto->nome }}</td>
                                                <td class="text-center">
                                                    <input type="number" class="form-control quantidade"
                                                        value="{{ $produto->pivot->quantidade }}" min="1">
                                                </td>
                                                <td class="text-center">
                                                    <input type="text" class="form-control valor-unitario"
                                                        value="R$ {{ number_format($produto->pivot->valor_unitario, 2, ',', '.') }}">
                                                </td>
                                                <td class="valor-total">R$
                                                    {{ number_format($produto->pivot->valor_total, 2, ',', '.') }}</td>
                                                <td>
                                                    @if (!$venda->bloqueado)
                                                        <button type="button" class="btn btn-danger btn-remover-produto">
                                                            <i class="bx bx-trash"></i> Remover
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4" class="text-end fw-bold">Total</td>
                                        <td id="valorTotalTabela" class="text-success fw-bold">R$
                                            {{ number_format($venda->valor_total, 2, ',', '.') }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Seção de Documentos Fiscais -->
                        <div class="divider my-4">
                            <div class="divider-text"><i class="bx bx-file"></i> Documentos Fiscais</div>
                        </div>

                        <div class="row mb-3">
                            <!-- NF-e (Produtos) -->
                            <div class="col-md-6">
                                <h6 class="fw-bold">Nota Fiscal de Produto (NF-e)</h6>
                                @if ($venda->notasFiscais->isEmpty())
                                    <p class="text-muted small">Nenhuma NF-e emitida.</p>
                                @else
                                    <ul class="list-group">
                                        @foreach ($venda->notasFiscais as $nfe)
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    @php
                                                        $badgeColor = match ($nfe->status) {
                                                            'autorizada' => 'success',
                                                            'cancelada' => 'danger',
                                                            'rejeitada' => 'danger',
                                                            'processando' => 'info',
                                                            default => 'warning',
                                                        };
                                                    @endphp
                                                    <span class="badge bg-{{ $badgeColor }}">
                                                        {{ ucfirst($nfe->status) }}
                                                    </span>
                                                    <small class="ms-2">#{{ $nfe->numero_nfe ?? 'N/A' }}</small>
                                                </div>
                                                <div>
                                                    @if ($nfe->status == 'autorizada')
                                                        <a href="{{ route('nfe.gerarDanfe', $nfe->id) }}" target="_blank"
                                                            class="btn btn-sm btn-icon btn-outline-danger"
                                                            title="PDF DANFE">
                                                            <i class="bx bxs-file-pdf"></i>
                                                        </a>
                                                        <a href="{{ route('nfe.downloadXml', $nfe->id) }}"
                                                            target="_blank"
                                                            class="btn btn-sm btn-icon btn-outline-success"
                                                            title="XML">
                                                            <i class="bx bx-code-alt"></i>
                                                        </a>
                                                    @endif
                                                    <a href="{{ route('nfe.show', $nfe->id) }}"
                                                        class="btn btn-sm btn-icon btn-outline-info" title="Ver Detalhes">
                                                        <i class="bx bx-show"></i>
                                                    </a>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>

                            <!-- NFS-e (Serviços) -->
                            <div class="col-md-6">
                                <h6 class="fw-bold">Nota Fiscal de Serviço (NFS-e)</h6>
                                @if ($venda->notasFiscaisServico->isEmpty())
                                    <p class="text-muted small">Nenhuma NFS-e emitida.</p>
                                @else
                                    <ul class="list-group">
                                        @foreach ($venda->notasFiscaisServico as $nfse)
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    @php
                                                        $badgeColorServ = match ($nfse->status) {
                                                            'autorizada' => 'success',
                                                            'cancelada' => 'danger',
                                                            'rejeitada' => 'danger',
                                                            'processando' => 'info',
                                                            default => 'warning',
                                                        };
                                                    @endphp
                                                    <span class="badge bg-{{ $badgeColorServ }}">
                                                        {{ ucfirst($nfse->status) }}
                                                    </span>
                                                    <small class="ms-2">RPS
                                                        #{{ $nfse->numero_rps ?? $nfse->id }}</small>
                                                </div>
                                                <div>
                                                    @if ($nfse->status == 'autorizada' || $nfse->chave_acesso)
                                                        <a href="{{ route('nfse.pdf', $nfse->id) }}" target="_blank"
                                                            class="btn btn-sm btn-icon btn-outline-danger"
                                                            title="PDF NFS-e">
                                                            <i class="bx bxs-file-pdf"></i>
                                                        </a>
                                                        <a href="{{ route('nfse.xml', $nfse->id) }}" target="_blank"
                                                            class="btn btn-sm btn-icon btn-outline-success"
                                                            title="XML">
                                                            <i class="bx bx-code-alt"></i>
                                                        </a>
                                                    @endif
                                                    <a href="{{ route('nfse.show', $nfse->id) }}"
                                                        class="btn btn-sm btn-icon btn-outline-info" title="Ver Detalhes">
                                                        <i class="bx bx-show"></i>
                                                    </a>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>

                        <!-- Campo de Observação -->
                        <div class="mb-3">
                            <label for="observacoes" class="form-label">
                                <i class="bx bx-comment"></i> Observações
                            </label>
                            <textarea class="form-control" id="observacoes" name="observacoes" rows="3"
                                placeholder="Adicione observações sobre a venda...">{{ $venda->observacoes }}</textarea>
                        </div>

                        <!-- Botões de Ação -->
                        <div class="card-footer d-flex justify-content-end">
                            <a href="{{ route('vendas.exportarPdf', $venda->id) }}" class="btn btn-success me-2">
                                <i class="bx bx-download"></i> Exportar PDF
                            </a>
                            @if (!$venda->cobrancas->where('status', 'pago')->count())
                                <form action="{{ route('vendas.pagarNaHora', $venda->id) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-info me-2"
                                        onclick="return confirm('Confirmar pagamento imediato em Dinheiro?')">
                                        <i class="bx bx-dollar-circle"></i> Pagar na Hora
                                    </button>
                                </form>
                            @endif
                            <button type="button" class="btn btn-primary me-2" id="abrirModalCobranca">
                                <i class="bx bx-money"></i> Gerar Cobrança
                            </button>
                            <button type="button" class="btn btn-secondary me-2" onclick="window.history.back();">
                                <i class="bx bx-x"></i> Cancelar
                            </button>
                            @if (!$venda->bloqueado)
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-check"></i> Salvar Alterações
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Modal para Adicionar Produtos -->
    <div class="modal fade" id="modalAdicionarProduto" tabindex="-1" aria-labelledby="modalAdicionarProdutoLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAdicionarProdutoLabel">Adicionar Produto</h5>
                    <a href="{{ route('produtos.create') }}" class="btn btn-success btn-sm ms-auto me-2"
                        target="_blank">
                        <i class="fas fa-plus"></i> Adicionar Produto
                    </a>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Quick Add Section -->
                    <div class="mb-3 p-3 bg-light rounded border">
                        <label for="inputQuickAdd" class="form-label fw-bold text-primary">
                            <i class="bx bx-bolt-circle"></i> Adição Rápida
                        </label>
                        <div class="input-group">
                            <input type="text" id="inputQuickAdd" class="form-control"
                                placeholder="Digite: Quantidade * Código (ex: 2 * 789...) ou Leitor de Código de Barras"
                                autofocus>
                            <button type="button" id="btnQuickAdd" class="btn btn-primary">
                                <i class="bx bx-plus"></i> Adicionar
                            </button>
                        </div>
                        <div class="form-text">Pressione ENTER para adicionar automaticamente.</div>
                    </div>
                    <hr class="my-3">

                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="produto_id" class="form-label">Selecionar Produto</label>
                            <select id="produto_id" class="select2 form-select" required>
                                <option value="" disabled selected>Selecione um produto</option>
                                @foreach ($produtos as $produto)
                                    <option value="{{ $produto->id }}" data-preco="{{ $produto->preco_venda }}">
                                        {{ $produto->nome }} - R$ {{ number_format($produto->preco_venda, 2, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="valor_unitario" class="form-label">Valor do Produto</label>
                            <input type="text" class="form-control" id="valor_unitario" placeholder="R$ 0,00">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="quantidade" class="form-label">Quantidade</label>
                        <input type="number" class="form-control" id="quantidade" value="1" min="1"
                            required>
                    </div>
                    <div class="mb-3">
                        <label for="valor_total" class="form-label">Valor Total</label>
                        <input type="text" class="form-control" id="valor_total" placeholder="R$ 0,00" readonly>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="adicionarProduto">Adicionar Produto</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Cobrança -->
    <div class="modal fade" id="modalCobranca" tabindex="-1" aria-labelledby="modalCobrancaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCobrancaLabel">Gerar Cobrança</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Escolha o método de pagamento:</p>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary" id="gerarPix">
                            <i class="bx bx-qr"></i> PIX
                        </button>
                        <button type="button" class="btn btn-success" id="gerarBoleto">
                            <i class="bx bx-barcode"></i> Boleto
                        </button>
                    </div>
                    <div class="mt-3">
                        <label>
                            <input type="checkbox" id="enviarEmail"> Enviar cobrança por e-mail
                        </label>
                    </div>
                    <div class="mt-3">
                        <label>
                            <input type="checkbox" id="enviarWhatsapp"> Enviar cobrança por WhatsApp
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="confirmarCobranca">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    <form id="formCobranca" action="{{ route('vendas.gerarCobranca', $venda->id) }}" method="POST"
        style="display: none;">
        @csrf
        <input type="hidden" id="metodoPagamento" name="metodoPagamento">
    </form>

    <!-- Modal Selecionar Pagamento -->
    <div class="modal fade" id="modalSelecionarPagamento" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Selecione a Forma de Pagamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-primary w-100 p-3"
                                onclick="selectPayment('pix', 'PIX')">
                                <i class="bx bx-qr fs-1 mb-2"></i><br>PIX
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-primary w-100 p-3"
                                onclick="selectPayment('dinheiro', 'Dinheiro')">
                                <i class="bx bx-money fs-1 mb-2"></i><br>Dinheiro
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-primary w-100 p-3"
                                onclick="selectPayment('cartao_credito', 'Cartão de Crédito')">
                                <i class="bx bx-credit-card fs-1 mb-2"></i><br>Crédito
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-primary w-100 p-3"
                                onclick="selectPayment('cartao_debito', 'Cartão de Débito')">
                                <i class="bx bx-credit-card-front fs-1 mb-2"></i><br>Débito
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-primary w-100 p-3"
                                onclick="selectPayment('boleto', 'Boleto')">
                                <i class="bx bx-barcode fs-1 mb-2"></i><br>Boleto
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-primary w-100 p-3"
                                onclick="selectPayment('transferencia', 'Transferência')">
                                <i class="bx bx-transfer fs-1 mb-2"></i><br>Transferência
                            </button>
                        </div>
                        <div class="col-12">
                            <button type="button" class="btn btn-outline-secondary w-100 p-3"
                                onclick="selectPayment('outros', 'Outros')">
                                <i class="bx bx-dots-horizontal-rounded fs-1 mb-2"></i><br>Outros
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Confirmar NFe -->
    <div class="modal fade" id="modalPagamentoNfe" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Emissão de NF-e</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bx bx-lock-alt me-1"></i> Atenção! Após emitir a NF-e, esta venda será
                        <strong>bloqueada</strong> para edições.
                    </div>
                    <p>Verifique os dados antes de continuar:</p>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Cliente:</strong> <span id="confirmaCliente"></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Valor Total:</strong> <span id="confirmaValor" class="text-success fw-bold"></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Forma Pagamento:</strong> <span id="confirmaPagamento"></span>
                        </li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form action="{{ route('vendas.finalizarNfe', $venda->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary">Confirmar e Emitir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @include('content.vendas.scripts-edit')

@endsection
