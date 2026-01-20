@extends('layouts.layoutMaster')

@section('title', 'Editar Orçamento')
@section('vendor-style')
@vite([
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/typeahead-js/typeahead.scss',
'resources/assets/vendor/libs/swiper/swiper.scss'
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/swiper/swiper.js'
])
@endsection

@section('page-script')
@vite([
'resources/assets/js/forms-selects.js'
])
@endsection
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0 text-primary" style="font-size: 2.5rem; font-weight: bold; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);">
        <i class="fas fa-edit"></i> Editar Orçamento #{{ $orcamento->id }}
        <span class="badge bg-{{ $orcamento->status == 'autorizado' ? 'success' : ($orcamento->status == 'recusado' ? 'danger' : 'warning') }} ms-2">
            {{ ucfirst($orcamento->status) }}
        </span>
        @if($orcamento->validade < now() && $orcamento->status == 'pendente')
            <span class="badge bg-danger ms-2">Vencido</span>
            @endif
    </h1>
    <div class="btn-group">
        <a href="{{ route('orcamentos.show', $orcamento->id) }}" class="btn btn-info">
            <i class="fas fa-eye"></i> Ver Detalhes
        </a>
        <a href="{{ route('orcamentos.gerarPdf', $orcamento->id) }}" class="btn btn-danger" target="_blank">
            <i class="fas fa-file-pdf"></i> PDF
        </a>
        <a href="{{ route('orcamentos.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<!-- Alerta de Estoque Insuficiente -->
@php
$podeAutorizar = true;
$produtosSemEstoque = [];
foreach($orcamento->produtos as $produto) {
if ($produto->estoque < $produto->pivot->quantidade) {
    $podeAutorizar = false;
    $produtosSemEstoque[] = [
    'produto' => $produto,
    'estoque_disponivel' => $produto->estoque,
    'quantidade_solicitada' => $produto->pivot->quantidade,
    'faltam' => $produto->pivot->quantidade - $produto->estoque,
    ];
    }
    }
    @endphp

    @if(!$podeAutorizar && $orcamento->status == 'pendente')
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">
            <i class="fas fa-exclamation-triangle me-2"></i> Atenção: Estoque Insuficiente
        </h6>
        <p class="mb-2">Alguns produtos não possuem estoque suficiente para autorizar este orçamento:</p>
        <ul class="mb-0">
            @foreach($produtosSemEstoque as $item)
            <li>
                <strong>{{ $item['produto']->nome }}</strong> -
                Estoque: {{ $item['estoque_disponivel'] }} |
                Solicitado: {{ $item['quantidade_solicitada'] }} |
                Faltam: {{ $item['faltam'] }}
            </li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card mb-4">
        <form action="{{ route('orcamentos.update', $orcamento->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card-body">
                <!-- Cliente, Data e Validade -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="cliente_id" class="form-label">
                            <i class="bx bx-id-card"></i> Cliente
                        </label>
                        <select id="select2Basic" class="select2 form-select" name="cliente_id" required>
                            <option value="" disabled>Selecione um cliente</option>
                            @foreach ($clientes as $cliente)
                            @php
                            $endereco = '';
                            if ($cliente->endereco) {
                            $cepFormatado = $cliente->endereco->cep ?
                            (\Illuminate\Support\Str::substr($cliente->endereco->cep, 0, 5) . '-' . \Illuminate\Support\Str::substr($cliente->endereco->cep, 5)) :
                            '';
                            $endereco = ($cliente->endereco->endereco ?? '') . ', ' .
                            ($cliente->endereco->numero ?? '') . ', ' .
                            ($cliente->endereco->bairro ?? '') . ', ' .
                            ($cliente->endereco->cidade ?? '') . ', ' .
                            ($cliente->endereco->estado ?? '') .
                            ($cepFormatado ? ', CEP: ' . $cepFormatado : '');
                            }
                            @endphp
                            <option value="{{ $cliente->id }}"
                                data-endereco="{{ $endereco }}"
                                {{ $orcamento->cliente_id == $cliente->id ? 'selected' : '' }}>
                                #{{ $cliente->id }} - {{ $cliente->nome }} - {{ $cliente->cpf_cnpj }}
                            </option>
                            @endforeach
                        </select>


                        @error('cliente_id')
                        <small class="text-danger fw-bold">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="data" class="form-label">
                            <i class="bx bx-calendar"></i> Data
                        </label>
                        <input type="date" class="form-control" name="data" id="data" value="{{ $orcamento->data }}" required>
                        @error('data')
                        <small class="text-danger fw-bold">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="validade" class="form-label">
                            <i class="bx bx-calendar-check"></i> Validade do Orçamento
                        </label>
                        <input type="date" class="form-control" name="validade" id="validade" value="{{ $orcamento->validade }}" required>
                        @error('validade')
                        <small class="text-danger fw-bold">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <!-- Endereço do Cliente e Botão Calcular Distância -->
                <div class="row mb-3 align-items-end">
                    <div class="col-md-8">
                        <label for="endereco_cliente" class="form-label">Endereço do Cliente</label>
                        @php
                        $enderecoCliente = '';
                        if ($orcamento->cliente && $orcamento->cliente->endereco) {
                        $cepFormatado = $orcamento->cliente->endereco->cep ?
                        (\Illuminate\Support\Str::substr($orcamento->cliente->endereco->cep, 0, 5) . '-' . \Illuminate\Support\Str::substr($orcamento->cliente->endereco->cep, 5)) :
                        '';
                        $enderecoCliente = ($orcamento->cliente->endereco->endereco ?? '') . ', ' .
                        ($orcamento->cliente->endereco->numero ?? '') . ', ' .
                        ($orcamento->cliente->endereco->bairro ?? '') . ', ' .
                        ($orcamento->cliente->endereco->cidade ?? '') . ', ' .
                        ($orcamento->cliente->endereco->estado ?? '') .
                        ($cepFormatado ? ', CEP: ' . $cepFormatado : '');
                        }
                        @endphp
                        <input type="text" class="form-control" name="endereco_cliente" id="endereco_cliente" value="{{ $enderecoCliente }}" readonly>
                    </div>

                    <div class="col-md-4">
                        <button type="button" id="calcularDistancia" class="btn btn-success w-100">
                            <i class="bx bx-map"></i> Calcular Distância
                        </button>
                    </div>
                </div>

                <!-- Seção de Produtos -->
                <div class="divider my-6">
                    <div class="divider-text"><i class="fas fa-briefcase"></i>Produtos</div>
                </div>

                <div class="mb-3 d-flex align-items-start gap-2">
                    <div>
                        <div class="input-group input-group-sm" style="width: 100%; max-width: 300px;">
                            <span class="input-group-text"><i class="bx bx-barcode"></i></span>
                            <input type="text" id="barcode-input" class="form-control" placeholder="Qtd * Código * Preço" autocomplete="off">
                        </div>
                        <small class="text-muted d-block mt-1" style="font-size: 11px;">
                            Ex: 2*Código*1,99 ou 10*Código
                        </small>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAdicionarProduto">
                        <i class="bx bx-plus-circle me-1"></i> Adicionar Produto
                    </button>
                </div>

                <div class="table-responsive mb-3">
                    <table class="table table-bordered" id="tabelaProdutos">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th class="text-center" style="width: 10%;">Quantidade</th>
                                <th class="text-center" style="width: 15%;">Valor Unitário</th>
                                <th>Valor Total</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orcamento->produtos as $produto)
                            <tr class="{{ $produto->estoque < $produto->pivot->quantidade ? 'table-warning' : '' }}">
                                <td>{{ $produto->id }}</td>
                                <td>
                                    <strong>{{ $produto->nome }}</strong>
                                    @if($produto->estoque < $produto->pivot->quantidade)
                                        <br><small class="text-danger">⚠ Estoque: {{ $produto->estoque }} (Solicitado: {{ $produto->pivot->quantidade }})</small>
                                        @else
                                        <br><small class="text-muted">Estoque: {{ $produto->estoque }}</small>
                                        @endif
                                </td>
                                <td>
                                    <input type="number" class="form-control" name="produtos[{{ $produto->id }}][quantidade]" value="{{ $produto->pivot->quantidade }}" min="1" onchange="atualizarValorTotalTabela()">
                                </td>
                                <td>
                                    <input type="text" class="form-control" name="produtos[{{ $produto->id }}][valor_unitario]" value="R$ {{ number_format($produto->pivot->valor_unitario, 2, ',', '.') }}" oninput="formatCurrencyService(this); atualizarValorTotalTabela()">
                                </td>
                                <td class="valor-total" data-valor="{{ $produto->pivot->valor_total ?? ($produto->pivot->valor_unitario * $produto->pivot->quantidade) }}">
                                    <strong>R$ {{ number_format($produto->pivot->valor_total ?? ($produto->pivot->valor_unitario * $produto->pivot->quantidade), 2, ',', '.') }}</strong>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="removerProduto(this)">Remover</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-end fw-bold">Total</td>
                                <td id="valorTotalTabela" class="text-success fw-bold">R$ {{ number_format($orcamento->valor_total, 2, ',', '.') }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Dismissible Alert para Custo de Combustível -->
                <div id="alertCustoCombustivel" class="alert alert-warning alert-dismissible fade show d-none" role="alert">
                    <strong>Custo estimado de combustível: </strong><span id="valorCombustivelAlert">R$ 0,00</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>

                <!-- Campo Valor do Serviço -->
                <div class="mb-3">
                    <label for="valor_servico" class="form-label">
                        <i class="bx bx-dollar-circle"></i> Valor do Serviço
                    </label>
                    <div class="input-group">
                        <input type="text" class="form-control" name="valor_servico" id="valor_servico" placeholder="R$ 0,00" oninput="formatCurrencyService(this); validarValorServico()">
                        <button type="button" class="btn btn-primary" id="adicionarServico">Adicionar</button>
                    </div>
                    <small id="erro_valor_servico" class="text-danger fw-bold d-none">O valor do serviço deve ser maior ou igual ao custo de combustível.</small>
                    @error('valor_servico')
                    <small class="text-danger fw-bold">{{ $message }}</small>
                    @enderror
                </div>

                <!-- Observações -->
                <div class="mb-3">
                    <label for="observacoes" class="form-label">
                        <i class="bx bx-comment"></i> Observações
                    </label>
                    <textarea class="form-control" name="observacoes" id="observacoes" rows="3">{{ $orcamento->observacoes }}</textarea>
                </div>

                <!-- Botões -->
                <div class="card-footer d-flex justify-content-end">
                    <button type="submit" class="btn btn-md btn-primary fw-bold me-2">
                        <i class="bx bx-save"></i> Salvar Alterações
                    </button>
                    <a href="{{ route('orcamentos.gerarPdf', $orcamento->id) }}" target="_blank" class="btn btn-md btn-success fw-bold me-2">
                        <i class="bx bx-file"></i> Exibir PDF
                    </a>
                    <button type="button" class="btn btn-outline-secondary" onclick="history.back();">
                        <i class="bx bx-x"></i> Cancelar
                    </button>
                </div>

            </div>
        </form>
    </div>

    <!-- Modal para Adicionar Produtos -->
    <div class="modal fade" id="modalAdicionarProduto" tabindex="-1" aria-labelledby="modalAdicionarProdutoLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAdicionarProdutoLabel">Adicionar Produto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="produto_id" class="form-label">Selecionar Produto</label>
                            <select id="produto_id" class="select2 form-select" required>
                                <option value="" disabled selected>Selecione um produto</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="valor_unitario" class="form-label">Valor do Produto</label>
                            <input type="text" class="form-control" id="valor_unitario" placeholder="R$ 0,00">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="quantidade" class="form-label">Quantidade</label>
                        <input type="number" class="form-control" id="quantidade" value="1" min="1" required>
                        <small class="text-muted" id="estoqueInfo"></small>
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

    <!-- Modal de Confirmação -->
    <div class="modal fade" id="confirmarVendaSemEstoque" tabindex="-1" aria-labelledby="confirmarVendaSemEstoqueLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmarVendaSemEstoqueLabel">Estoque Insuficiente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Um ou mais produtos estão com estoque insuficiente. Deseja prosseguir com a venda mesmo assim?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="confirmarVenda" class="btn btn-primary">Prosseguir</button>
                </div>
            </div>
        </div>
    </div>

    {{-- @include('content.orcamentos.partials.modal_produto')  --}}
    {{-- @include('content.orcamentos.criar.partials.modal_produto')  --}}
    @include('content.orcamentos.scripts-edit')


    @endsection