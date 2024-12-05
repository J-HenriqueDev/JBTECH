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

        // Adiciona uma linha à tabela com os campos `name` necessários
        $('#tabelaProdutos tbody').append(`
            <tr>
                <td>
                    <input type="hidden" name="produtos[${produtoId}][id]" value="${produtoId}">${produtoId}
                </td>
                <td>${produtoNome}</td>
                <td>
                    <input type="number" class="form-control" name="produtos[${produtoId}][quantidade]" value="${quantidade}" min="1" readonly>
                </td>
                <td>
                    <input type="text" class="form-control" name="produtos[${produtoId}][valor_unitario]" value="${formatCurrency(precoUnitario)}" readonly>
                </td>
                <td class="valor-total">${formatCurrency(valorTotal)}</td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm" onclick="removerProduto(this)">Remover</button>
                </td>
            </tr>
        `);

        // Fecha o modal e limpa os campos
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

      $('#adicionarServico').on('click', function () {
        if (!validarValorServico()) return;

        const valorServico = parseCurrency($('#valor_servico').val());

        // Simula o produto "Serviço" com ID 1 e insere na tabela
        $('#tabelaProdutos tbody').append(`
            <tr>
                <td>1</td> <!-- ID do produto Serviço -->
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
