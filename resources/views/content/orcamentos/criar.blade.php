@extends('layouts.layoutMaster')

@section('title', 'Criar Orçamento')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/typeahead-js/typeahead.scss', 'resources/assets/vendor/libs/swiper/swiper.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/swiper/swiper.js'])

    @if (env('GOOGLE_GEOCODING_API_KEY'))
        <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_GEOCODING_API_KEY') }}&libraries=geometry">
        </script>
    @endif

    @php
        $empresaEndereco =
            \App\Models\Configuracao::get('empresa_endereco') .
            ', ' .
            \App\Models\Configuracao::get('empresa_numero') .
            ', ' .
            \App\Models\Configuracao::get('empresa_bairro') .
            ', ' .
            \App\Models\Configuracao::get('empresa_cidade') .
            ' - ' .
            \App\Models\Configuracao::get('empresa_estado', 'RJ');
        $custoPorKm = \App\Models\Configuracao::get('vendas_custo_km', '1.50');
    @endphp

    <script>
        window.empresaEndereco = "{{ $empresaEndereco }}";
        window.custoPorKm = {
            {
                $custoPorKm ?? 1.50
            }
        };
    </script>


    @include('content.orcamentos.scripts')
@endsection


@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">Orçamentos /</span> Criar Novo
        </h4>
        <a href="{{ route('orcamentos.index') }}" class="btn btn-secondary">
            <i class="bx bx-arrow-back me-1"></i> Voltar
        </a>
    </div>

    <form action="{{ route('orcamentos.store') }}" method="POST">
        @csrf

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Dados do Orçamento</h5>
            </div>
            <div class="card-body">
                <!-- Dados Principais -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="cliente_id" class="form-label fw-bold">Cliente</label>
                        <select id="select2Basic" class="select2 form-select" name="cliente_id" required>
                            <option value="" disabled selected>Selecione um cliente</option>
                            @foreach ($clientes as $cliente)
                                @php
                                    $endereco = '';
                                    if ($cliente->endereco) {
                                        $cepFormatado = $cliente->endereco->cep
                                            ? \Illuminate\Support\Str::substr($cliente->endereco->cep, 0, 5) .
                                                '-' .
                                                \Illuminate\Support\Str::substr($cliente->endereco->cep, 5)
                                            : '';
                                        $endereco =
                                            ($cliente->endereco->endereco ?? '') .
                                            ', ' .
                                            ($cliente->endereco->numero ?? '') .
                                            ', ' .
                                            ($cliente->endereco->bairro ?? '') .
                                            ', ' .
                                            ($cliente->endereco->cidade ?? '') .
                                            ', ' .
                                            ($cliente->endereco->estado ?? '') .
                                            ($cepFormatado ? ', CEP: ' . $cepFormatado : '');
                                    }
                                @endphp
                                <option value="{{ $cliente->id }}" data-endereco="{{ $endereco }}">#{{ $cliente->id }}
                                    - {{ $cliente->nome }} - {{ $cliente->cpf_cnpj }}</option>
                            @endforeach
                        </select>
                        @error('cliente_id')
                            <small class="text-danger fw-bold">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="data" class="form-label fw-bold">Data de Emissão</label>
                        <input type="date" class="form-control" name="data" id="data"
                            value="{{ date('Y-m-d') }}" required>
                        @error('data')
                            <small class="text-danger fw-bold">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="validade" class="form-label fw-bold">Validade</label>
                        <input type="date" class="form-control" name="validade" id="validade"
                            value="{{ date('Y-m-d', strtotime('+3 days')) }}" required>
                        @error('validade')
                            <small class="text-danger fw-bold">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3 align-items-end">
                    <div class="col-md-9">
                        <label for="endereco_cliente" class="form-label fw-bold">Endereço (Cálculo de Deslocamento)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-map"></i></span>
                            <input type="text" class="form-control" name="endereco_cliente" id="endereco_cliente"
                                placeholder="Endereço do cliente">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <button type="button" id="calcularDistancia" class="btn btn-outline-primary w-100">
                            <i class="bx bx-trip me-1"></i> Calcular Distância
                        </button>
                    </div>
                </div>

                <!-- Alert para Custo de Combustível -->
                <div id="alertCustoCombustivel" class="alert alert-warning alert-dismissible fade show d-none mt-3"
                    role="alert">
                    <i class="bx bx-gas-pump me-2"></i>
                    <strong>Custo estimado de combustível: </strong><span id="valorCombustivelAlert">R$ 0,00</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>

                <hr class="my-4">

                <!-- Itens do Orçamento -->
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <h5 class="mb-0">Itens do Orçamento</h5>
                    <div class="d-flex align-items-start gap-2">
                        <div>
                            <div class="input-group input-group-sm" style="width: 100%; max-width: 300px;">
                                <span class="input-group-text"><i class="bx bx-barcode"></i></span>
                                <input type="text" id="barcode-input" class="form-control"
                                    placeholder="Qtd * Código * Preço" autocomplete="off">
                            </div>
                            <small class="text-muted d-block mt-1" style="font-size: 11px;">
                                Ex: 2*Código*1,99 ou 10*Código
                            </small>
                        </div>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#modalAdicionarProduto">
                            <i class="bx bx-plus-circle me-1"></i> Adicionar Produto
                        </button>
                    </div>
                </div>

                <div class="table-responsive mb-4 border rounded p-2">
                    <table class="table table-hover table-striped" id="tabelaProdutos">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%;">ID</th>
                                <th style="width: 35%;">Descrição</th>
                                <th class="text-center" style="width: 15%;">Qtd.</th>
                                <th class="text-center" style="width: 20%;">Valor Unit.</th>
                                <th class="text-center" style="width: 20%;">Total</th>
                                <th style="width: 5%;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr id="tabelaVazia">
                                <td colspan="6" class="text-center py-4 text-muted">
                                    Nenhum item adicionado.
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="4" class="text-end fw-bold fs-5">Total Geral:</td>
                                <td id="valorTotalTabela" class="text-center fw-bold fs-5 text-success">R$ 0,00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Serviço Avulso -->
                <div class="row align-items-end bg-light p-3 rounded mb-3 mx-1">
                    <div class="col-md-8">
                        <label for="valor_servico" class="form-label fw-bold">Serviço / Mão de Obra</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="text" class="form-control" name="valor_servico" id="valor_servico"
                                placeholder="0,00" oninput="formatCurrencyService(this); validarValorServico()">
                            <button type="button" class="btn btn-primary" id="adicionarServico">
                                <i class="bx bx-plus me-1"></i> Incluir
                            </button>
                        </div>
                        <small id="erro_valor_servico" class="text-danger fw-bold d-none mt-1">O valor do serviço deve ser
                            maior ou igual ao custo de combustível.</small>
                        @error('valor_servico')
                            <small class="text-danger fw-bold">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <!-- Formas de Pagamento -->
                <div class="row mb-4">
                    <h5 class="mb-3"><i class="bx bx-wallet-alt me-2"></i>Formas de Pagamento</h5>
                    <div class="col-md-12">
                        <div class="row g-3">
                            <!-- À Vista -->
                            <div class="col-md-4">
                                <label class="card h-100 border cursor-pointer position-relative shadow-sm hover-effect"
                                    for="pagamento_avista" style="cursor: pointer;">
                                    <div class="card-body d-flex align-items-center p-3">
                                        <div class="form-check me-3">
                                            <input class="form-check-input" type="checkbox" name="formas_pagamento[]"
                                                value="avista" id="pagamento_avista" checked>
                                        </div>
                                        <div>
                                            <h6 class="mb-1 fw-bold text-dark"><i
                                                    class="bx bx-money text-success fs-4 align-middle me-1"></i> À Vista
                                            </h6>
                                            <small class="text-muted">Dinheiro ou PIX</small>
                                        </div>
                                    </div>
                                </label>
                            </div>

                            <!-- Cartão de Crédito -->
                            <div class="col-md-4">
                                <label class="card h-100 border cursor-pointer position-relative shadow-sm hover-effect"
                                    for="pagamento_cartao" style="cursor: pointer;">
                                    <div class="card-body d-flex align-items-center p-3">
                                        <div class="form-check me-3">
                                            <input class="form-check-input" type="checkbox" name="formas_pagamento[]"
                                                value="cartao" id="pagamento_cartao" checked>
                                        </div>
                                        <div>
                                            <h6 class="mb-1 fw-bold text-dark"><i
                                                    class="bx bx-credit-card text-primary fs-4 align-middle me-1"></i>
                                                Cartão de Crédito</h6>
                                            <small class="text-muted">Parcelamento até 12x</small>
                                        </div>
                                    </div>
                                </label>
                            </div>

                            <!-- Boleto Parcelado -->
                            <div class="col-md-4">
                                <div class="card h-100 border shadow-sm hover-effect">
                                    <div class="card-body p-3">
                                        <label class="d-flex align-items-center cursor-pointer mb-0 w-100"
                                            for="pagamento_boleto" style="cursor: pointer;">
                                            <div class="form-check me-3">
                                                <input class="form-check-input" type="checkbox" name="formas_pagamento[]"
                                                    value="boleto" id="pagamento_boleto">
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark"><i
                                                        class="bx bx-barcode text-warning fs-4 align-middle me-1"></i>
                                                    Boleto Parcelado</h6>
                                            </div>
                                        </label>

                                        <div id="div_parcelas_boleto" style="display: none;"
                                            class="mt-3 pt-2 border-top">
                                            <div class="row g-2">
                                                <div class="col-12 col-sm-6">
                                                    <label class="form-label mb-1 text-muted small">Parcelas:</label>
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" name="parcelas_boleto" class="form-control"
                                                            placeholder="Qtd" min="1" max="48">
                                                        <span class="input-group-text">x</span>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-sm-6">
                                                    <label class="form-label mb-1 text-muted small">Intervalo:</label>
                                                    <select name="periodicidade_boleto"
                                                        class="form-select form-select-sm">
                                                        <option value="Mensal">Mensal</option>
                                                        <option value="Quinzenal">Quinzenal</option>
                                                        <option value="Semanal">Semanal</option>
                                                        <option value="7 Dias" selected>A cada 7 Dias</option>
                                                        <option value="15 Dias">A cada 15 Dias</option>
                                                        <option value="30 Dias">A cada 30 Dias</option>
                                                        <option value="45 Dias">A cada 45 Dias</option>
                                                        <option value="60 Dias">A cada 60 Dias</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <style>
                    .hover-effect:hover {
                        border-color: #696cff !important;
                        background-color: #f8f9fa;
                    }
                </style>

                <hr class="my-4">

                <!-- Finalização -->
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="observacoes" class="form-label fw-bold">Observações</label>
                        <textarea class="form-control" name="observacoes" id="observacoes" rows="4"
                            placeholder="Observações, condições de pagamento..."></textarea>
                    </div>

                    <div class="col-md-12 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="enviar_email" id="enviar_email"
                                checked>
                            <label class="form-check-label" for="enviar_email">
                                Enviar orçamento por e-mail para o cliente
                            </label>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <button type="button" class="btn btn-outline-secondary me-2"
                        onclick="history.back();">Cancelar</button>
                    <button type="submit" class="btn btn-success fw-bold">
                        <i class="bx bx-check me-1"></i> Salvar Orçamento
                    </button>
                </div>

            </div>
        </div>
    </form>

    <!-- Modal para Adicionar Produtos -->
    <div class="modal fade" id="modalAdicionarProduto" tabindex="-1" aria-labelledby="modalAdicionarProdutoLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white" id="modalAdicionarProdutoLabel">Adicionar Produto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info" role="alert">
                        <i class="bx bx-info-circle me-1"></i>
                        Se o produto não estiver na lista, <a href="{{ route('produtos.create') }}" target="_blank"
                            class="fw-bold text-decoration-underline">cadastre-o aqui</a> e atualize a lista.
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="produto_id" class="form-label fw-bold">Produto</label>
                            <select id="produto_id" class="select2 form-select" required>
                                <!-- Options carregadas via AJAX -->
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="valor_unitario" class="form-label fw-bold">Valor Unitário</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="text" class="form-control" id="valor_unitario" placeholder="0,00"
                                    oninput="formatCurrencyService(this)">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="quantidade" class="form-label fw-bold">Quantidade</label>
                            <div class="input-group">
                                <button class="btn btn-outline-secondary" type="button" id="btn-minus-qtd"><i
                                        class="bx bx-minus"></i></button>
                                <input type="number" class="form-control text-center" id="quantidade" value="1"
                                    min="1" required>
                                <button class="btn btn-outline-secondary" type="button" id="btn-plus-qtd"><i
                                        class="bx bx-plus"></i></button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="valor_total" class="form-label fw-bold">Subtotal</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="text" class="form-control bg-light" id="valor_total" placeholder="0,00"
                                    readonly>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-primary" id="adicionarProduto">
                        <i class="bx bx-plus"></i> Adicionar
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection
