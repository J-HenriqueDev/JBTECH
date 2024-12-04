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
                            <th>Quantidade</th>
                            <th>Valor Unitário</th>
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
                    <input type="text" class="form-control" name="valor_servico" id="valor_servico" placeholder="R$ 0,00" required oninput="formatCurrencyService(this);">
                    <button type="button" class="btn btn-primary" id="adicionarServico">Adicionar</button>
                </div>
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
  // Função para formatar valores em formato de moeda brasileira
  function formatCurrencyService(input) {
   let value = input.value; // Garante que o valor seja uma string

   // Remove tudo que não seja número
   value = value.replace(/\D/g, '');

   // Se o valor estiver vazio, define como zero
   if (value === '') {
       input.value = 'R$ 0,00';
       return;
   }

   // Converte o valor para decimal, divide por 100 para as casas decimais
   let intValue = parseInt(value, 10) / 100;

   // Formata o valor no estilo brasileiro (2 casas decimais, vírgula como separador decimal)
   let formattedValue = intValue.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

   // Define o valor formatado no campo de entrada
   input.value = formattedValue;
}

// Função para remover a formatação do valor (uso ao adicionar na tabela)
function parseCurrency(value) {
   return parseFloat(value.replace('R$', '').replace(/\./g, '').replace(',', '.').trim());
}

// Função genérica para formatar valores numéricos para moeda (uso em tabelas e cálculos)
function formatCurrency(value) {
 if (isNaN(value) || value === null) {
     return 'R$ 0,00'; // Retorna um valor padrão se a entrada não for válida
 }
 value = Math.abs(parseFloat(value)).toFixed(2);
 return `R$ ${value.replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.')}`;
}

$(document).ready(function () {
   // Atualiza o endereço do cliente ao selecionar um cliente
   $('#select2Basic').on('change', function () {
       const enderecoCliente = $(this).find(':selected').data('endereco');
       $('#endereco_cliente').val(enderecoCliente); // Atualiza o campo de endereço
   });

   // Inicializa o Select2 para o produto
   $('#produto_id').select2({
       width: '100%',
       dropdownParent: $('#modalAdicionarProduto'),
       placeholder: 'Selecione um produto',
   });

   // Formatação do valor do serviço enquanto digita
   $('#valor_servico').on('input', function () {
       formatCurrencyService(this);
   });

   // Adiciona o valor do serviço como um item na tabela
   $('#adicionarServico').on('click', function () {
       const valorServico = $('#valor_servico').val(); // Valor formatado no campo
       const valorServicoNumerico = parseCurrency(valorServico); // Remove a formatação e converte para número

       // Valida se o valor é maior que 0
       if (valorServicoNumerico <= 0 || isNaN(valorServicoNumerico)) {
           alert('Por favor, insira um valor de serviço válido.');
           return;
       }

       // Adiciona a linha na tabela
       $('#tabelaProdutos tbody').append(`
           <tr>
               <td>Serviço</td>
               <td>Serviço</td>
               <td>
                   <input type="number" class="form-control" name="produtos[servico][quantidade]" value="1" min="1" onchange="atualizarQuantidade(this, 'servico')">
               </td>
               <td>
                   <input type="text" class="form-control" name="produtos[servico][valor_unitario]" value="${formatCurrency(valorServicoNumerico)}" onchange="atualizarValorUnitario(this, 'servico')">
               </td>
               <td class="valor-total">${formatCurrency(valorServicoNumerico)}</td>
               <td>
                   <button type="button" class="btn btn-danger btn-sm" onclick="removerProduto(this)">Remover</button>
               </td>
           </tr>
       `);

       // Limpa o campo de valor do serviço
       $('#valor_servico').val('R$ 0,00');

       // Atualiza a mensagem "Nenhum produto adicionado"
       atualizarMensagemTabela();

       // Atualiza o valor total na linha de total
       atualizarValorTotalTabela();
   });


    // Atualiza o valor do produto e o valor total ao selecionar um produto
    $('#produto_id').on('change', function () {
      const preco = parseFloat($(this).find(':selected').data('preco') || 0);
      $('#valor_unitario').val(preco > 0 ? formatCurrency(preco) : '');
      calcularValorTotal(preco);
  });

  // Atualiza o valor total ao alterar a quantidade
  $('#quantidade').on('input', function () {
      const preco = parseFloat($('#valor_unitario').val().replace('R$ ', '').replace('.', '').replace(',', '.') || 0);
      calcularValorTotal(preco);
  });

  // Atualiza o valor total ao editar o valor unitário
  $('#valor_unitario').on('input', function () {
      const preco = parseFloat($(this).val().replace('R$ ', '').replace('.', '').replace(',', '.') || 0);
      calcularValorTotal(preco);
  });

  // Função para calcular o valor total
  function calcularValorTotal(preco) {
      const quantidade = parseInt($('#quantidade').val() || 1);
      const valorTotal = preco * quantidade;
      $('#valor_total').val(formatCurrency(valorTotal));
  }

   // Adicionar Produto à tabela
   $('#adicionarProduto').on('click', function () {
       const produtoId = $('#produto_id').val();
       const produtoNome = $('#produto_id option:selected').text().split(' - ')[0];
       const precoUnitario = parseFloat($('#valor_unitario').val().replace('R$ ', '').replace('.', '').replace(',', '.') || 0);
       const quantidade = parseInt($('#quantidade').val() || 1);
       const valorTotal = precoUnitario * quantidade;

       if (!produtoId || precoUnitario <= 0 || quantidade <= 0) {
           alert('Por favor, preencha todos os campos corretamente antes de adicionar um produto.');
           return;
       }

       // Adiciona a linha na tabela
       $('#tabelaProdutos tbody').append(`
           <tr>
               <td>${produtoId}</td>
               <td>${produtoNome}</td>
               <td>
                   <input type="number" class="form-control" name="produtos[${produtoId}][quantidade]" value="${quantidade}" min="1" onchange="atualizarQuantidade(this, '${produtoId}')">
               </td>
               <td>
                   <input type="text" class="form-control" name="produtos[${produtoId}][valor_unitario]" value="${formatCurrency(precoUnitario)}" onchange="atualizarValorUnitario(this, '${produtoId}')">
               </td>
               <td class="valor-total">${formatCurrency(valorTotal)}</td>
               <td>
                   <button type="button" class="btn btn-danger btn-sm" onclick="removerProduto(this)">Remover</button>
               </td>
           </tr>
       `);

       // Limpa os campos e fecha o modal
       $('#modalAdicionarProduto').modal('hide');
       limparCamposModal();

       // Atualiza a mensagem "Nenhum produto adicionado"
       atualizarMensagemTabela();

       // Atualiza o valor total na linha de total
       atualizarValorTotalTabela();
   });

   // Atualiza a mensagem da tabela
   function atualizarMensagemTabela() {
       const linhasProdutos = $('#tabelaProdutos tbody tr').length - $('#tabelaProdutos tbody #tabelaVazia').length;
       if (linhasProdutos > 0) {
           $('#tabelaVazia').hide();
       } else {
           $('#tabelaVazia').show();
       }
   }

   // Atualiza o valor total ao alterar a quantidade diretamente na tabela
   window.atualizarQuantidade = function (input) {
       const quantidade = parseInt($(input).val() || 1);
       const precoUnitario = parseFloat($(input).closest('tr').find('td:nth-child(4) input').val().replace('R$ ', '').replace('.', '').replace(',', '.') || 0);
       const valorTotal = precoUnitario * quantidade;

       $(input).closest('tr').find('.valor-total').text(formatCurrency(valorTotal));
       atualizarValorTotalTabela();
   };

   // Atualiza o valor total ao alterar o valor unitário diretamente na tabela
   window.atualizarValorUnitario = function (input) {
       const precoUnitario = parseFloat($(input).val().replace('R$ ', '').replace('.', '').replace(',', '.') || 0);
       const quantidade = parseInt($(input).closest('tr').find('td:nth-child(3) input').val() || 1);
       const valorTotal = precoUnitario * quantidade;

       $(input).closest('tr').find('.valor-total').text(formatCurrency(valorTotal));
       atualizarValorTotalTabela();
   };

   // Remove uma linha da tabela
   window.removerProduto = function (button) {
       $(button).closest('tr').remove();
       atualizarMensagemTabela();
       atualizarValorTotalTabela();
   };

   // Limpa os campos do modal
   function limparCamposModal() {
       $('#produto_id').val(null).trigger('change');
       $('#valor_unitario').val('');
       $('#quantidade').val(1);
       $('#valor_total').val('R$ 0,00');
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

    // Atualiza o valor do produto e o valor total ao selecionar um produto
    $('#produto_id').on('change', function () {
      const preco = parseFloat($(this).find(':selected').data('preco') || 0);
      $('#valor_unitario').val(preco > 0 ? formatCurrency(preco) : '');
      calcularValorTotal(preco);
    });

    // Atualiza o valor total ao alterar a quantidade
    $('#quantidade').on('input', function () {
      const preco = parseFloat($('#valor_unitario').val().replace('R$ ', '').replace('.', '').replace(',', '.') || 0);
      calcularValorTotal(preco);
    });

    // Atualiza o valor total ao editar o valor unitário
    $('#valor_unitario').on('input', function () {
      const preco = parseFloat($(this).val().replace('R$ ', '').replace('.', '').replace(',', '.') || 0);
      calcularValorTotal(preco);
    });


   // Cálculo da distância
   $('#calcularDistancia').on('click', function () {
       const enderecoCliente = $('#endereco_cliente').val();

       if (!enderecoCliente) {
           alert('Por favor, selecione um cliente para calcular a distância.');
           return;
       }

       $.ajax({
           url: '{{ route("orcamentos.obterCoordenadas") }}', // Rota para obter coordenadas
           method: 'POST',
           data: {
               endereco_cliente: enderecoCliente,
               _token: '{{ csrf_token() }}', // Envia o token CSRF
           },
           success: function (response) {
               if (response.error) {
                   alert('Erro: ' + response.error);
               } else {
                   const latitude = response.lat;
                   const longitude = response.lng;
                   calcularDistanciaDaLoja(latitude, longitude);
               }
           }
       });
   });

   // Função para calcular a distância da loja
   function calcularDistanciaDaLoja(lat, lng) {
       const lojaLat = -22.4807496; // Latitude da loja
       const lojaLng = -44.5047416; // Longitude da loja

       const origem = new google.maps.LatLng(lojaLat, lojaLng);
       const destino = new google.maps.LatLng(lat, lng);

       const distancia = google.maps.geometry.spherical.computeDistanceBetween(origem, destino); // Distância em metros
       const distanciaIdaVolta = distancia * 2; // Distância de ida e volta
       const distanciaKm = (distanciaIdaVolta / 1000).toFixed(2); // Distância em quilômetros

       // Cálculo do custo de combustível
       const consumoPorLitro = 9; // km por litro
       const precoGasolina = 6.20; // Preço da gasolina por litro
       const litrosNecessarios = distanciaIdaVolta / 1000 / consumoPorLitro; // Litros necessários
       const custoCombustivel = litrosNecessarios * precoGasolina; // Custo de combustível

       // Atualiza o alerta com o valor do combustível
       $('#valorCombustivelAlert').text(formatCurrency(custoCombustivel));
       $('#alertCustoCombustivel').removeClass('d-none'); // Mostra o alerta

       alert(`Distância até o cliente (ida e volta): ${distanciaKm} km\nCusto estimado de combustível: ${formatCurrency(custoCombustivel)}`);
   }
});
</script>
@endsection
