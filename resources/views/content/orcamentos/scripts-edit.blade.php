  <!-- Inclua o jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Inclua o Select2 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

  <!-- Inclua o Select2 JS -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <script>
  let custoCombustivel = 0; // Variável global para armazenar o custo do combustível

  // Formata valores em moeda brasileira (usa função global se disponível)
  function formatCurrency(value) {
      // Trata valores undefined, null ou vazios
      if (value === undefined || value === null || value === '') {
          return 'R$ 0,00';
      }
      
      if (typeof window.formatCurrency === 'function') {
          // Se a função global existir, usa ela
          const input = document.createElement('input');
          input.value = value;
          window.formatCurrency(input);
          return input.value;
      }
      // Fallback local
      const numValue = parseFloat(value);
      if (isNaN(numValue)) return 'R$ 0,00';
      const formatted = Math.abs(numValue).toFixed(2);
      return `R$ ${formatted.replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.')}`;
  }

  // Remove a formatação de moeda e retorna um número
  function parseCurrency(value) {
      if (!value || value === undefined || value === null) return 0;
      if (typeof value !== 'string') return parseFloat(value) || 0;
      const cleaned = value.replace('R$', '').replace(/\./g, '').replace(',', '.').trim();
      return parseFloat(cleaned) || 0;
  }

  // Formata o valor do campo de serviço (usa função global se disponível)
  function formatCurrencyService(input) {
      if (typeof window.formatCurrency === 'function') {
          window.formatCurrency(input);
          return;
      }
      // Fallback local
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

  // Função para atualizar os produtos
  function atualizarProdutos() {
    carregandoProdutos = true; // Indica que uma requisição está em andamento
    console.log('Carregando produtos...');

    $.ajax({
        url: '/produtos/lista', // Altere para a URL correta da sua rota de produtos
        method: 'GET',
        success: function (produtos) {
            console.log('Produtos carregados:', produtos);

            // Limpa as opções atuais
            $('#produto_id').empty();

            // Adiciona um placeholder
            $('#produto_id').append('<option value="" disabled selected>Selecione um produto</option>');

            // Adiciona as novas opções ao select2
            produtos.forEach(function (produto) {
                const option = new Option(
                    `${produto.nome} - R$ ${parseFloat(produto.preco_venda).toFixed(2).replace('.', ',')}${produto.estoque !== undefined ? ' (Estoque: ' + produto.estoque + ')' : ''}`,
                    produto.id,
                    false,
                    false
                );
                // Adiciona o preço e estoque como atributos de dados (garante que seja número)
                $(option).data('preco', parseFloat(produto.preco_venda) || 0);
                $(option).data('estoque', produto.estoque ?? 0);
                $('#produto_id').append(option);
            });

            // Atualiza o Select2 para refletir as novas opções
            $('#produto_id').trigger('change.select2');

            // Abre o dropdown manualmente após a atualização
            $('#produto_id').select2('open');
        },
        error: function (xhr, status, error) {
            console.error('Erro na requisição:', error);
            alert('Erro ao carregar os produtos. Tente novamente mais tarde.');
        },
        complete: function () {
            carregandoProdutos = false; // Indica que a requisição foi concluída
        }
    });
  }

  let produtosCarregados = false; // Indica se os produtos já foram carregados
  let carregandoProdutos = false; // Indica se uma requisição está em andamento


  $(document).ready(function () {
      // Inicializa o Select2 para o campo de clientes
      $('#select2Basic').select2({
          tags: false,
          placeholder: 'Selecione um cliente',
          width: '100%',
          allowClear: true
      });

      // Atualiza o endereço do cliente ao selecionar
      $('#select2Basic').on('change', function () {
          const enderecoCliente = $(this).find(':selected').data('endereco');
          $('#endereco_cliente').val(enderecoCliente || '');
      });

      // Preencher automaticamente o endereço ao carregar a página
      const selectedEndereco = $('#select2Basic').find(':selected').data('endereco');
      if (selectedEndereco) {
          $('#endereco_cliente').val(selectedEndereco);
      }

      // Inicializa o Select2 para o campo de produtos
      $('#produto_id').select2({
          tags: false,
          dropdownParent: $('#modalAdicionarProduto'),
          placeholder: 'Selecione um produto',
          width: '100%',
          allowClear: true
      });

      // Carrega os produtos ao carregar a página
      atualizarProdutos();

      $('#produto_id').on('select2:opening', function (e) {
          if (!produtosCarregados && !carregandoProdutos) {
              e.preventDefault(); // Impede a abertura automática do dropdown
              atualizarProdutos(); // Atualiza os produtos
          }
      });

  // Atualiza o valor_unitário e valor_total ao selecionar um produto
  $('#produto_id').on('change', function () {
      const selectedId = $(this).val();
      
      // Ignora se nenhum produto foi selecionado ou se é o placeholder
      if (!selectedId || selectedId === '') {
          $('#valor_unitario').val('');
          $('#valor_total').val('');
          return;
      }
      
      const selectedOption = $(this).find('option[value="' + selectedId + '"]');
      const preco = parseFloat(selectedOption.data('preco')) || 0;
      
      if (preco && preco > 0) {
          // Preenche o campo Valor do Produto
          $('#valor_unitario').val(formatCurrency(preco));
          
          // Calcula e preenche o Valor Total (preço × quantidade)
          const quantidade = parseInt($('#quantidade').val() || 1);
          const valorTotal = preco * quantidade;
          $('#valor_total').val(formatCurrency(valorTotal));
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
          
          // Atualiza informação de estoque
          const estoque = $('#produto_id').find(':selected').data('estoque');
          if (estoque !== undefined) {
              if (estoque < quantidade) {
                  $('#estoqueInfo').html('<span class="text-danger">⚠ Estoque disponível: ' + estoque + ' unidades</span>');
              } else if (estoque <= 10) {
                  $('#estoqueInfo').html('<span class="text-warning">⚠ Estoque baixo: ' + estoque + ' unidades</span>');
              } else {
                  $('#estoqueInfo').html('<span class="text-success">✓ Estoque disponível: ' + estoque + ' unidades</span>');
              }
          }
      });

      // Adiciona o produto na tabela
      $('#adicionarProduto').on('click', function () {
        const produtoId = $('#produto_id').val();
        const produtoTexto = $('#produto_id option:selected').text();
        const produtoNome = produtoTexto.split(' - ')[0];
        const precoUnitario = parseCurrency($('#valor_unitario').val());
        const quantidade = parseInt($('#quantidade').val() || 1);
        const valorTotal = precoUnitario * quantidade;

        if (!produtoId || precoUnitario <= 0 || quantidade <= 0) {
            alert('Por favor, preencha todos os campos corretamente antes de adicionar um produto.');
            return;
        }

        // Verifica se o produto já existe na tabela
        let produtoExiste = false;
        $('#tabelaProdutos tbody tr').each(function() {
            const idExistente = $(this).find('input[type="hidden"]').val();
            if (idExistente == produtoId) {
                produtoExiste = true;
                return false; // break
            }
        });

        if (produtoExiste) {
            alert('Este produto já foi adicionado à tabela. Remova-o primeiro se desejar alterar.');
            return;
        }

        // Adiciona uma linha à tabela com os campos `name` necessários
        $('#tabelaProdutos tbody').append(`
            <tr>
                <td>
                    <input type="hidden" name="produtos[${produtoId}][id]" value="${produtoId}">${produtoId}
                </td>
                <td><strong>${produtoNome}</strong></td>
                <td>
                    <input type="number" class="form-control" name="produtos[${produtoId}][quantidade]" value="${quantidade}" min="1" onchange="atualizarValorTotalTabela()">
                </td>
                <td>
                    <input type="text" class="form-control" name="produtos[${produtoId}][valor_unitario]" value="${formatCurrency(precoUnitario)}" oninput="formatCurrencyService(this); atualizarValorTotalTabela()">
                </td>
                <td class="valor-total" data-valor="${valorTotal}"><strong>${formatCurrency(valorTotal)}</strong></td>
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


      // Verifica se existe campo de serviço na página
      if ($('#valor_servico').length && $('#adicionarServico').length) {
          $('#adicionarServico').on('click', function () {
              if (!validarValorServico()) return;

              const valorServico = parseCurrency($('#valor_servico').val());

              // Verifica se o serviço já existe na tabela
              let servicoExiste = false;
              $('#tabelaProdutos tbody tr').each(function() {
                  const idExistente = $(this).find('td:first').text().trim();
                  if (idExistente == '1') {
                      servicoExiste = true;
                      return false; // break
                  }
              });

              if (servicoExiste) {
                  alert('O serviço já foi adicionado à tabela. Remova-o primeiro se desejar alterar.');
                  return;
              }

              // Simula o produto "Serviço" com ID 1 e insere na tabela
              $('#tabelaProdutos tbody').append(`
                  <tr>
                      <td>1</td> <!-- ID do produto Serviço -->
                      <td><strong>Serviço</strong></td>
                      <td>
                          <input type="number" class="form-control" name="produtos[1][quantidade]" value="1" readonly>
                      </td>
                      <td>
                          <input type="text" class="form-control" name="produtos[1][valor_unitario]" value="${formatCurrency(valorServico)}" readonly>
                      </td>
                      <td class="valor-total" data-valor="${valorServico}"><strong>${formatCurrency(valorServico)}</strong></td>
                      <td>
                          <button type="button" class="btn btn-danger btn-sm" onclick="removerProduto(this)">Remover</button>
                      </td>
                  </tr>
              `);

              atualizarMensagemTabela();
              atualizarValorTotalTabela();
              
              // Limpa o campo de serviço
              $('#valor_servico').val('');
          });
      }


      // Calcula distância do cliente e custo de combustível
      if ($('#calcularDistancia').length) {
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
                  error: function() {
                      alert('Erro ao calcular distância. Verifique sua conexão e tente novamente.');
                  }
              });
          });
      }

      function calcularDistanciaDaLoja(lat, lng) {
          if (typeof google === 'undefined' || !google.maps || !google.maps.geometry) {
              alert('Google Maps não está carregado. Verifique sua conexão e tente novamente.');
              return;
          }

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

          if ($('#alertCustoCombustivel').length) {
              $('#alertCustoCombustivel').removeClass('d-none').html(`
                  <strong>Custo estimado de combustível: ${formatCurrency(custoCombustivelCalculado)}</strong>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              `);
              if ($('#valorCombustivelAlert').length) {
                  $('#valorCombustivelAlert').text(formatCurrency(custoCombustivel));
              }
          }

          alert(`Distância até o cliente (ida e volta): ${distanciaKm} km\nCusto estimado de combustível: ${formatCurrency(custoCombustivel)}`);
      }
  });

  // Atualiza a mensagem "Nenhum produto adicionado" na tabela
  function atualizarMensagemTabela() {
      const linhasProdutos = $('#tabelaProdutos tbody tr').length;
      $('#tabelaVazia').toggleClass('d-none', linhasProdutos > 0);
  }

  // Atualiza o valor total na linha de total e recalcula valores das linhas
  function atualizarValorTotalTabela() {
      let total = 0;
      $('#tabelaProdutos tbody tr').each(function () {
          // Ignora a linha vazia
          if ($(this).attr('id') === 'tabelaVazia') {
              return;
          }
          
          const quantidade = parseFloat($(this).find('input[name*="[quantidade]"]').val() || 0) || 0;
          const valorUnitarioStr = $(this).find('input[name*="[valor_unitario]"]').val() || 'R$ 0,00';
          const valorUnitario = parseCurrency(valorUnitarioStr) || 0;
          const valorTotal = (quantidade * valorUnitario) || 0;
          
          // Atualiza o valor total da linha (garante que seja um número válido)
          if (!isNaN(valorTotal) && valorTotal >= 0) {
              $(this).find('.valor-total').html('<strong>' + formatCurrency(valorTotal) + '</strong>');
              $(this).find('.valor-total').attr('data-valor', valorTotal);
              total += valorTotal;
          }
      });
      $('#valorTotalTabela').text(formatCurrency(total || 0));
  }
  
  // Atualiza valores ao carregar a página e quando campos mudam
  $(document).ready(function() {
      atualizarValorTotalTabela();
      
      // Atualiza quando quantidade ou valor unitário mudam
      $(document).on('input change', 'input[name*="[quantidade]"], input[name*="[valor_unitario]"]', function() {
          atualizarValorTotalTabela();
      });
  });

  // Limpa os campos do modal de produtos
  function limparCamposModal() {
      $('#produto_id').val(null).trigger('change');
      $('#valor_unitario').val('');
      $('#quantidade').val(1);
      $('#valor_total').val('R$ 0,00');
      $('#estoqueInfo').html('');
  }

  // Remove uma linha da tabela
  window.removerProduto = function (button) {
      $(button).closest('tr').remove();
      atualizarMensagemTabela();
      atualizarValorTotalTabela();
  };
  </script>
