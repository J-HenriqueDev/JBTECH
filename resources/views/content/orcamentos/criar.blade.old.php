@extends('layouts.layoutMaster')

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
    <i class="bx bx-file"></i> Criar Orçamento
</h1>

<div class="card mb-4">
    <form action="{{ route('orcamentos.store') }}" method="POST">
        @csrf
        <div class="card-body">
            <div class="row mb-3">
              <!-- Campo Cliente -->
              <div class="col-md-4">
                  <label for="cliente_id" class="form-label">
                      <i class="bx bx-id-card"></i> Cliente
                  </label>
                  <select id="select2Basic" class="select2 form-select" name="cliente_id" required>
                      <option value="" disabled selected>Selecione um cliente</option>
                      @foreach ($clientes as $cliente)
                      <option value="{{ $cliente->id }}" data-endereco="{{ $cliente->endereco->endereco }}, {{ $cliente->endereco->numero }}, {{ $cliente->endereco->bairro }}, {{ $cliente->endereco->cidade }}, {{ $cliente->endereco->estado }}, CEP: {{ $cliente->endereco->cep }}">{{ $cliente->nome }}</option>
                      @endforeach
                  </select>
                  @error('cliente_id')
                  <small class="text-danger fw-bold">{{ $message }}</small>
                  @enderror
              </div>

                <!-- Campo Data -->
                <div class="col-md-4">
                    <label for="data" class="form-label">
                        <i class="bx bx-calendar"></i> Data
                    </label>
                    <input type="date" class="form-control" name="data" id="data" value="{{ date('Y-m-d') }}" required>
                    @error('data')
                    <small class="text-danger fw-bold">{{ $message }}</small>
                    @enderror
                </div>

                <!-- Campo Validade do Orçamento -->
                <div class="col-md-4">
                    <label for="validade" class="form-label">
                        <i class="bx bx-calendar-check"></i> Validade do Orçamento
                    </label>
                    <input type="date" class="form-control" name="validade" id="validade" value="{{ date('Y-m-d', strtotime('+3 days')) }}" required>
                    @error('validade')
                    <small class="text-danger fw-bold">{{ $message }}</small>
                    @enderror
                </div>
            </div>
            <div class="row mb-3 align-items-end">
              <div class="col-md-8">
                  <label for="endereco_cliente" class="form-label">Endereço do Cliente</label>
                  <input type="text" class="form-control" name="endereco_cliente" id="endereco_cliente" >
              </div>

              <div class="col-md-4">
                  <button type="button" id="calcularDistancia" class="btn btn-success w-100">
                      <i class="bx bx-map"></i> Calcular Distância
                  </button>
              </div>
          </div>

          <div class="divider my-6">
            <div class="divider-text"><i class="fas fa-briefcase"></i>Produtos</div>
          </div>

            <!-- Botão para Adicionar Produtos -->
            <div class="mb-3">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAdicionarProduto">
                    <i class="bx bx-plus-circle"></i> Adicionar Produto
                </button>
            </div>

            <!-- Tabela de Produtos Inseridos -->
            <div class="table-responsive mb-3">
                <table class="table table-bordered" id="tabelaProdutos">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th class="text-center" style="width: 10%;">Quantidade</th> <!-- Largura reduzida -->
                            <th class="text-center" style="width: 15%;">Valor Unitário</th> <!-- Largura reduzida -->
                            <th>Valor Total</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr id="tabelaVazia">
                            <td colspan="6" class="text-center">
                                <div class="alert alert-info" role="alert">
                                    "Nenhuma Informação foi Inserida na tabela"
                                </div>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-end fw-bold">Total</td>
                            <td id="valorTotalTabela" class="text-success fw-bold">R$ 0,00</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <input type="hidden" name="produtos[{{ produto_id }}][quantidade]" value="1">
            <input type="hidden" name="produtos[{{ produto_id }}][valor_unitario]" value="150.00">


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
                  <input type="text" class="form-control" name="valor_servico" id="valor_servico" placeholder="R$ 0,00" required oninput="formatCurrencyService(this); validarValorServico()">
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
                <textarea class="form-control" name="observacoes" id="observacoes" rows="3" placeholder="Notas adicionais sobre o orçamento..."></textarea>
            </div>

            <!-- Checkbox para enviar o orçamento por e-mail -->
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="enviar_email" id="enviar_email">
                <label class="form-check-label" for="enviar_email">
                    Desejo enviar o orçamento para o e-mail do cliente
                </label>
            </div>

            <div class="card-footer d-flex justify-content-end">

              <button type="submit" class="btn btn-md btn-primary fw-bold me-2">
                  <i class="bx bx-plus"></i> Criar Orçamento
              </button>
              <button type="button" class="btn btn-outline-secondary" onclick="history.back();">
                  <i class="bx bx-x"></i> Cancelar
              </button>
              <a href="{{ route('orcamentos.exportarPdf', $orcamento->id ?? 1) }}" class="btn btn-md btn-danger fw-bold me-2" target="_blank">
                <i class="bx bx-download"></i> Exportar PDF
            </a>
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_GEOCODING_API_KEY') }}&libraries=geometry"></script>

<script>
    let custoCombustivel = 0; // Variável global para armazenar o custo do combustível

    // Formata valores em moeda brasileira
    function formatCurrency(value) {
        if (isNaN(value) || value === null) return 'R$ 0,00';
        value = Math.abs(parseFloat(value)).toFixed(2);
        return `R$ ${value.replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.')}`;
    }

    // Remove a formatação de moeda e retorna um número
    function parseCurrency(value) {
        return parseFloat(value.replace('R$', '').replace(/\./g, '').replace(',', '.').trim());
    }

    // Formata o valor do campo de serviço
    function formatCurrencyService(input) {
        let value = input.value.replace(/\D/g, ''); // Remove caracteres não numéricos
        if (value === '') {
            input.value = 'R$ 0,00';
            return;
        }
        let intValue = parseInt(value, 10) / 100;
        input.value = intValue.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    }

    // Valida se o valor de serviço é maior ou igual ao custo de combustível
    function validarValorServico() {
        const valorServicoInput = $('#valor_servico');
        const valorServico = parseCurrency(valorServicoInput.val());

        if (valorServico < custoCombustivel) {
            valorServicoInput.addClass('is-invalid');
            $('#alertCustoCombustivel').removeClass('d-none').addClass('alert-danger').html(`
                <strong>O valor do serviço deve ser maior ou igual ao custo de combustível (${formatCurrency(custoCombustivel)}).</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `);
            return false;
        } else {
            valorServicoInput.removeClass('is-invalid');
            $('#alertCustoCombustivel').addClass('d-none');
            return true;
        }
    }

    $(document).ready(function () {
        // Inicializa o Select2 para o campo de produtos
        $('#produto_id').select2({
            dropdownParent: $('#modalAdicionarProduto'),
            placeholder: 'Selecione um produto',
            width: '100%'
        });

        // Atualiza o valor_unitário e valor_total ao selecionar um produto
        $('#produto_id').on('change', function () {
            const preco = $(this).find(':selected').data('preco');
            if (preco) {
                $('#valor_unitario').val(formatCurrency(preco));
                const quantidade = parseInt($('#quantidade').val() || 1);
                $('#valor_total').val(formatCurrency(preco * quantidade));
            } else {
                $('#valor_unitario').val('');
                $('#valor_total').val('');
            }
        });

        // Atualiza o valor_total ao alterar a quantidade
        $('#quantidade').on('input', function () {
            const preco = parseCurrency($('#valor_unitario').val());
            const quantidade = parseInt($(this).val() || 1);
            $('#valor_total').val(formatCurrency(preco * quantidade));
        });

        // Adiciona o produto na tabela
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
                        <input type="number" class="form-control" value="${quantidade}" min="1" readonly>
                    </td>
                    <td>
                        <input type="text" class="form-control" value="${formatCurrency(precoUnitario)}" readonly>
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

        // Atualiza o endereço do cliente ao selecionar
        $('#select2Basic').on('change', function () {
            const enderecoCliente = $(this).find(':selected').data('endereco');
            $('#endereco_cliente').val(enderecoCliente || '');
        });

        // Valida e adiciona o valor de serviço à tabela
        $('#adicionarServico').on('click', function () {
            if (!validarValorServico()) return;

            const valorServico = parseCurrency($('#valor_servico').val());

            $('#tabelaProdutos tbody').append(`
                <tr>
                    <td>Serviço</td>
                    <td>Serviço</td>
                    <td>
                        <input type="number" class="form-control" value="1" readonly>
                    </td>
                    <td>
                        <input type="text" class="form-control" value="${formatCurrency(valorServico)}" readonly>
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

        // Calcula distância do cliente e custo de combustível
        $('#calcularDistancia').on('click', function () {
            const enderecoCliente = $('#endereco_cliente').val();

            if (!enderecoCliente) {
                alert('Por favor, selecione um cliente para calcular a distância.');
                return;
            }

            $.ajax({
                url: '{{ route("orcamentos.obterCoordenadas") }}',
                method: 'POST',
                data: {
                    endereco_cliente: enderecoCliente,
                    _token: '{{ csrf_token() }}',
                },
                success: function (response) {
                    if (response.error) {
                        alert('Erro: ' + response.error);
                    } else {
                        calcularDistanciaDaLoja(response.lat, response.lng);
                    }
                },
            });
        });

        function calcularDistanciaDaLoja(lat, lng) {
            const lojaLat = -22.4807496;
            const lojaLng = -44.5047416;

            const origem = new google.maps.LatLng(lojaLat, lojaLng);
            const destino = new google.maps.LatLng(lat, lng);

            const distancia = google.maps.geometry.spherical.computeDistanceBetween(origem, destino);
            const distanciaIdaVolta = distancia * 2;
            const distanciaKm = (distanciaIdaVolta / 1000).toFixed(2);

            const consumoPorLitro = 9;
            const precoGasolina = 6.20;
            const litrosNecessarios = distanciaIdaVolta / 1000 / consumoPorLitro;
            const custoCombustivelCalculado = litrosNecessarios * precoGasolina;

            custoCombustivel = custoCombustivelCalculado;

            $('#alertCustoCombustivel').removeClass('d-none').html(`
                <strong>Custo estimado de combustível: ${formatCurrency(custoCombustivelCalculado)}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `);
            $('#valorCombustivelAlert').text(formatCurrency(custoCombustivel));
            $('#alertCustoCombustivel').removeClass('d-none'); // Mostra o alerta

            alert(`Distância até o cliente (ida e volta): ${distanciaKm} km\nCusto estimado de combustível: ${formatCurrency(custoCombustivel)}`);
                }
    });

    // Atualiza a mensagem "Nenhum produto adicionado" na tabela
    function atualizarMensagemTabela() {
        const linhasProdutos = $('#tabelaProdutos tbody tr').length;
        $('#tabelaVazia').toggleClass('d-none', linhasProdutos > 0);
    }

   // Atualiza o valor total na linha de total
   function atualizarValorTotalTabela() {
    let total = 0;
    $('#tabelaProdutos tbody tr').each(function () {
        const valor = parseFloat($(this).find('.valor-total').text().replace('R$ ', '').replace('.', '').replace(',', '.') || 0);
        total += valor;
    });
    $('#valorTotalTabela').text(formatCurrency(total));
}

    // Limpa os campos do modal de produtos
    function limparCamposModal() {
        $('#produto_id').val(null).trigger('change');
        $('#valor_unitario').val('');
        $('#quantidade').val(1);
        $('#valor_total').val('R$ 0,00');
    }

    // Remove uma linha da tabela
    window.removerProduto = function (button) {
        $(button).closest('tr').remove();
        atualizarMensagemTabela();
        atualizarValorTotalTabela();
    };
</script>

@endsection
