@extends('layouts.layoutMaster')

@section('title', 'Ver/Editar Venda')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/select2/select2.scss',
  'resources/assets/vendor/libs/typeahead-js/typeahead.scss',
  'resources/assets/vendor/libs/swiper/swiper.scss'
])

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
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
<h1 class="mb-4 text-primary" style="font-size: 2.5rem; font-weight: bold; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);">
    <i class="bx bx-cart"></i> Ver/Editar Venda
</h1>

<div class="card mb-4">
    <form action="{{ route('vendas.update', $venda->id) }}" method="POST" id="formEditarVenda">
      @csrf
      @method('PUT')
      <div id="produtosHidden"></div>
        <div class="card-body">
            <!-- Primeira Linha: Cliente e Data -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="cliente_id" class="form-label">
                        <i class="bx bx-id-card"></i> Cliente
                    </label>
                    <select id="select2Cliente" class="select2 form-select" name="cliente_id" required>
                        <option value="" disabled>Selecione um cliente</option>
                        @foreach ($clientes as $cliente)
                        <option value="{{ $cliente->id }}" data-email="{{ $cliente->email }}" {{ $venda->cliente_id == $cliente->id ? 'selected' : '' }}>
                            {{ $cliente->nome }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="data_venda" class="form-label">
                        <i class="bx bx-calendar"></i> Data da Venda
                    </label>
                    <input type="date" class="form-control" id="data_venda" name="data_venda" value="{{ $venda->data_venda }}" required>
                </div>
            </div>

            <!-- Seção de Produtos -->
            <div class="divider my-6">
                <div class="divider-text"><i class="fas fa-briefcase"></i> Produtos</div>
            </div>
            <div class="mb-3">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAdicionarProduto">
                    <i class="bx bx-plus-circle"></i> Adicionar Produto
                </button>
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
                                        <input type="number" class="form-control quantidade" value="{{ $produto->pivot->quantidade }}" min="1">
                                    </td>
                                    <td class="text-center">
                                        <input type="text" class="form-control valor-unitario" value="R$ {{ number_format($produto->pivot->valor_unitario, 2, ',', '.') }}">
                                    </td>
                                    <td class="valor-total">R$ {{ number_format($produto->pivot->valor_total, 2, ',', '.') }}</td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-remover-produto">
                                            <i class="bx bx-trash"></i> Remover
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-end fw-bold">Total</td>
                            <td id="valorTotalTabela" class="text-success fw-bold">R$ {{ number_format($venda->valor_total, 2, ',', '.') }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Campo de Observação -->
            <div class="mb-3">
                <label for="observacoes" class="form-label">
                    <i class="bx bx-comment"></i> Observações
                </label>
                <textarea class="form-control" id="observacoes" name="observacoes" rows="3" placeholder="Adicione observações sobre a venda...">{{ $venda->observacoes }}</textarea>
            </div>

            <!-- Botões de Ação -->
            <div class="card-footer d-flex justify-content-end">
                <button type="button" class="btn btn-secondary me-2" onclick="window.history.back();">
                    <i class="bx bx-x"></i> Cancelar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="bx bx-check"></i> Salvar Alterações
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
                <a href="{{ route('produtos.create') }}" class="btn btn-success btn-sm ms-auto me-2" target="_blank">
                    <i class="fas fa-plus"></i> Adicionar Produto
                </a>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
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
                    <input type="number" class="form-control" id="quantidade" value="1" min="1" required>
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

@include('content.vendas.scripts-edit')

@endsection
