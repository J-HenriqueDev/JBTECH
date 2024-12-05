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

<h1 class="mb-4 text-primary" style="font-size: 2.5rem; font-weight: bold; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);">
    <i class="fas fa-edit"></i> Editar Orçamento #{{ $orcamento->id }}
</h1>

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
                          <option value="{{ $cliente->id }}"
                                  data-endereco="{{ $cliente->endereco->endereco }}, {{ $cliente->endereco->numero }}, {{ $cliente->endereco->bairro }}, {{ $cliente->endereco->cidade }}, {{ $cliente->endereco->estado }}, CEP: {{ $cliente->endereco->cep }}"
                                  {{ $orcamento->cliente_id == $cliente->id ? 'selected' : '' }}>
                              {{ $cliente->nome }}
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
                    <input type="text" class="form-control" name="endereco_cliente" id="endereco_cliente" value="{{ $orcamento->endereco_cliente }}" readonly>
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
                            <th class="text-center" style="width: 10%;">Quantidade</th>
                            <th class="text-center" style="width: 15%;">Valor Unitário</th>
                            <th>Valor Total</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orcamento->produtos as $produto)
                            <tr>
                                <td>{{ $produto->id }}</td>
                                <td>{{ $produto->nome }}</td>
                                <td>
                                    <input type="number" class="form-control" name="produtos[{{ $produto->id }}][quantidade]" value="{{ $produto->pivot->quantidade }}" min="1">
                                </td>
                                <td>
                                    <input type="text" class="form-control" name="produtos[{{ $produto->id }}][valor_unitario]" value="{{ number_format($produto->pivot->valor_unitario, 2, ',', '.') }}">
                                </td>
                                <td class="valor-total">{{ number_format($produto->pivot->quantidade * $produto->pivot->valor_unitario, 2, ',', '.') }}</td>
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
                            @foreach ($produtos as $produto)
                            <option value="{{ $produto->id }}" data-preco="{{ $produto->preco_venda }}">
                                {{ $produto->nome }} - R$ {{ number_format($produto->preco_venda, 2, ',', '.') }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="valor_unitario" class="form-label">Valor do Produto</label>
                        <input type="text" class="form-control" id="valor_unitario" placeholder="R$ 0,00" >
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

{{--  @include('content.orcamentos.partials.modal_produto')  --}}
{{--  @include('content.orcamentos.criar.partials.modal_produto')  --}}
@include('content.orcamentos.scripts')


<script>
  document.addEventListener('DOMContentLoaded', function () {
      const clienteSelect = document.getElementById('select2Basic');
      const enderecoInput = document.getElementById('endereco_cliente');

      // Atualizar o endereço do cliente automaticamente ao selecionar um cliente
      clienteSelect.addEventListener('change', function () {
          const endereco = clienteSelect.options[clienteSelect.selectedIndex]?.getAttribute('data-endereco');
          enderecoInput.value = endereco || ''; // Define o endereço ou deixa o campo vazio
      });

      // Preencher automaticamente o endereço ao carregar a página
      const selectedEndereco = clienteSelect.options[clienteSelect.selectedIndex]?.getAttribute('data-endereco');
      if (selectedEndereco) {
          enderecoInput.value = selectedEndereco;
      }

      // Inicializar Select2 para o modal de produtos
      $('#produto_id').select2({
          dropdownParent: $('#modalAdicionarProduto'),
          placeholder: 'Selecione um produto',
          width: '100%'
      });

      // Atualizar o valor_unitário e valor_total ao selecionar um produto
      $('#produto_id').on('change', function () {
          const preco = $(this).find(':selected').data('preco');
          if (preco) {
              $('#valor_unitario').val(preco.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }));
              const quantidade = parseInt($('#quantidade').val() || 1);
              $('#valor_total').val((preco * quantidade).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }));
          } else {
              $('#valor_unitario').val('');
              $('#valor_total').val('');
          }
      });

      // Atualizar o valor_total ao alterar a quantidade
      $('#quantidade').on('input', function () {
          const preco = parseCurrency($('#valor_unitario').val());
          const quantidade = parseInt($(this).val() || 1);
          $('#valor_total').val(formatCurrency(preco * quantidade));
      });

      // Adicionar produto à tabela
      $('#adicionarProduto').on('click', function () {
          const produtoId = $('#produto_id').val();
          const produtoNome = $('#produto_id option:selected').text().split(' - ')[0];
          const precoUnitario = parseCurrency($('#valor_unitario').val());
          const quantidade = parseInt($('#quantidade').val() || 1);
          const valorTotal = precoUnitario * quantidade;

          if (!produtoId || precoUnitario <= 0 || quantidade <= 0) {
              alert('Por favor, preencha todos os campos corretamente antes de adicionar um produto.');
              return;
          }

          // Adiciona uma linha à tabela
          $('#tabelaProdutos tbody').append(`
              <tr>
                  <td>${produtoId}</td>
                  <td>${produtoNome}</td>
                  <td>
                      <input type="number" class="form-control" name="produtos[${produtoId}][quantidade]" value="${quantidade}" min="1">
                  </td>
                  <td>
                      <input type="text" class="form-control" name="produtos[${produtoId}][valor_unitario]" value="${formatCurrency(precoUnitario)}">
                  </td>
                  <td class="valor-total">${formatCurrency(valorTotal)}</td>
                  <td>
                      <button type="button" class="btn btn-danger btn-sm" onclick="removerProduto(this)">Remover</button>
                  </td>
              </tr>
          `);

          $('#modalAdicionarProduto').modal('hide');
          limparCamposModal();
          atualizarMensagemTabela();
          atualizarValorTotalTabela();
      });

      // Limpar campos do modal
      function limparCamposModal() {
          $('#produto_id').val(null).trigger('change');
          $('#valor_unitario').val('');
          $('#quantidade').val(1);
          $('#valor_total').val('');
      }

      // Atualizar a mensagem "Nenhum produto adicionado"
      function atualizarMensagemTabela() {
          const linhasProdutos = $('#tabelaProdutos tbody tr').length;
          $('#tabelaVazia').toggleClass('d-none', linhasProdutos > 0);
      }

      function atualizarValorTotalTabela() {
        let total = 0;
        $('#tabelaProdutos tbody tr').each(function () {
            const valor = parseFloat($(this).find('.valor-total').text().replace('R$ ', '').replace('.', '').replace(',', '.') || 0);
            total += valor;
        });
        $('#valorTotalTabela').text(formatCurrency(total));
      }

      // Remover produto da tabela
      window.removerProduto = function (button) {
          $(button).closest('tr').remove();
          atualizarMensagemTabela();
          atualizarValorTotalTabela();
      };

      // Formatar valores como moeda brasileira
      function formatCurrency(value) {
          if (isNaN(value) || value === null) return 'R$ 0,00';
          value = Math.abs(parseFloat(value)).toFixed(2);
          return `R$ ${value.replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.')}`;
      }

      // Remover formatação de moeda e retornar número
      function parseCurrency(value) {
          return parseFloat(value.replace('R$', '').replace(/\./g, '').replace(',', '.').trim());
      }

      // Validar o valor do serviço
      $('#adicionarServico').on('click', function () {
          const valorServico = parseCurrency($('#valor_servico').val());

          if (!valorServico || valorServico <= 0) {
              alert('Por favor, insira um valor válido para o serviço.');
              return;
          }

          // Adicionar serviço à tabela
          $('#tabelaProdutos tbody').append(`
              <tr>
                  <td>1</td>
                  <td>Serviço</td>
                  <td>
                      <input type="number" class="form-control" name="produtos[1][quantidade]" value="1" readonly>
                  </td>
                  <td>
                      <input type="text" class="form-control" name="produtos[1][valor_unitario]" value="${formatCurrency(valorServico)}" readonly>
                  </td>
                  <td class="valor-total">${formatCurrency(valorServico)}</td>
                  <td>
                      <button type="button" class="btn btn-danger btn-sm" onclick="removerProduto(this)">Remover</button>
                  </td>
              </tr>
          `);

          atualizarMensagemTabela();
          atualizarValorTotalTabela();
      });
  });
</script>


@endsection
