  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
  <script>
  let custoCombustivel = 0; // Variável global para armazenar o custo do combustível

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
                    `${produto.nome} - R$ ${parseFloat(produto.preco_venda).toFixed(2).replace('.', ',')}`,
                    produto.id,
                    false,
                    false
                );
                // Adiciona o preço como um atributo de dados (garante que seja número)
                $(option).data('preco', parseFloat(produto.preco_venda) || 0);
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


  // Verifica se a view é de edição
  const isEditView = window.location.pathname.includes('/edit');

  // Formata um valor como moeda (R$)
  function formatCurrency(value) {
      // Trata valores undefined, null ou vazios
      if (value === undefined || value === null || value === '') {
          return 'R$ 0,00';
      }
      const numValue = parseFloat(value);
      if (isNaN(numValue)) return 'R$ 0,00';
      const formatted = Math.abs(numValue).toFixed(2);
      return `R$ ${formatted.replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.')}`;
  }

  // Remove a formatação de moeda e retorna um número
  function parseCurrency(value) {
      if (!value) return 0; // Retorna 0 se o valor for undefined ou null
      return parseFloat(value.replace('R$', '').replace(/\./g, '').replace(',', '.').trim());
  }

  // Atualiza a mensagem "Nenhum produto adicionado" na tabela
  function atualizarMensagemTabela() {
      const linhasProdutos = $('#tabelaProdutos tbody tr').length;
      if (linhasProdutos === 0) {
          $('#tabelaProdutos tbody').append(`
              <tr id="tabelaVazia">
                  <td colspan="6" class="text-center">
                      <div class="alert alert-info" role="alert">
                          Nenhum produto adicionado.
                      </div>
                  </td>
              </tr>
          `);
      } else {
          $('#tabelaVazia').remove();
      }
  }

  // Atualiza o valor total na linha de total
  function atualizarValorTotalTabela() {
      let total = 0;
      $('#tabelaProdutos tbody tr').each(function () {
          const valorTotal = parseCurrency($(this).find('.valor-total').text());
          total += valorTotal;
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

  // Adiciona um produto à tabela
  function adicionarProdutoTabela(produtoId, produtoNome, quantidade, valorUnitario, valorTotal) {
      // Remove a mensagem "Nenhum produto adicionado" se existir
      $('#tabelaVazia').remove();

      // Adiciona uma nova linha à tabela
      $('#tabelaProdutos tbody').append(`
          <tr data-produto-id="${produtoId}">
              <td>${produtoId}</td>
              <td>${produtoNome}</td>
              <td class="text-center">
                  <input type="number" class="form-control quantidade" value="${quantidade}" min="1">
              </td>
              <td class="text-center">
                  <input type="text" class="form-control valor-unitario" value="${valorUnitario}">
              </td>
              <td class="valor-total">${formatCurrency(valorTotal)}</td>
              <td>
                  <button type="button" class="btn btn-danger btn-sm btn-remover-produto">
                      <i class="bx bx-trash"></i> Remover
                  </button>
              </td>
          </tr>
      `);

      // Atualiza o valor total da tabela
      atualizarValorTotalTabela();
  }

  // Remove um produto da tabela
  $(document).on('click', '.btn-remover-produto', function () {
      $(this).closest('tr').remove();
      atualizarMensagemTabela();
      atualizarValorTotalTabela();
  });

  // Atualiza o valor total da linha ao modificar quantidade ou valor unitário
  $(document).on('input', '.quantidade, .valor-unitario', function () {
      const tr = $(this).closest('tr');
      const quantidade = parseFloat(tr.find('.quantidade').val()) || 0;
      const valorUnitario = parseCurrency(tr.find('.valor-unitario').val()) || 0;
      const valorTotal = quantidade * valorUnitario;

      // Atualiza o valor total da linha
      tr.find('.valor-total').text(formatCurrency(valorTotal));

      // Atualiza o valor total da tabela
      atualizarValorTotalTabela();
  });

  $(document).ready(function () {
      // Configura o idioma do Select2
      $.fn.select2.defaults.set('language', 'pt-BR');

      // Inicializa o Select2 para o campo de produtos
      $('#produto_id').select2({
          tags: false,
          dropdownParent: $('#modalAdicionarProduto'),
          placeholder: 'Selecione um produto',
          width: '100%'
      });

    atualizarProdutos();


      // Atualiza o valor unitário e total ao selecionar um produto
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

      // Atualiza o valor total ao alterar a quantidade
      $('#quantidade').on('input', function () {
          const preco = parseCurrency($('#valor_unitario').val());
          const quantidade = parseInt($(this).val() || 1);
          $('#valor_total').val(formatCurrency(preco * quantidade));
      });

      // Adiciona o produto na tabela ao clicar no botão "Adicionar Produto"
      $('#adicionarProduto').on('click', function () {
          const produtoId = $('#produto_id').val();
          const produtoNome = $('#produto_id option:selected').text().split(' - ')[0];
          const quantidade = parseInt($('#quantidade').val() || 1);
          const valorUnitario = $('#valor_unitario').val();
          const valorTotal = parseCurrency($('#valor_total').val());

          if (!produtoId || !valorUnitario || quantidade <= 0) {
              alert('Por favor, preencha todos os campos corretamente antes de adicionar um produto.');
              return;
          }

          // Adiciona o produto à tabela
          adicionarProdutoTabela(produtoId, produtoNome, quantidade, valorUnitario, valorTotal);

          // Fecha o modal e limpa os campos
          $('#modalAdicionarProduto').modal('hide');
          limparCamposModal();
      });

      // Inicializa o Select2 para o campo de clientes
      $('#select2Cliente').select2({
          tags: true,
          placeholder: 'Selecione um cliente',
          width: '100%'
      });

      $('#select2Cliente').on('select2:opening', function (e) {
          const select = $(this);
          const count = select.find('option').not('[value=""]').length;
          if (count === 0) {
              if (confirm('Nenhum cliente cadastrado. Deseja cadastrar agora?')) {
                  window.location.href = '{{ route("clientes.create") }}';
              }
              e.preventDefault();
          }
      });

      // Lógica para o botão "Emitir NF-e"
      $('#emitirNFe').on('click', function () {
          const produtos = [];
          $('#tabelaProdutos tbody tr').each(function () {
              const produtoId = $(this).find('input[name*="[id]"]').val();
              const quantidade = $(this).find('input[name*="[quantidade]"]').val();
              const valorUnitario = parseCurrency($(this).find('input[name*="[valor_unitario]"]').val());
              produtos.push({ id: produtoId, quantidade: quantidade, valor_unitario: valorUnitario });
          });

          if (produtos.length === 0) {
              alert('Adicione pelo menos um produto antes de emitir a NF-e.');
              return;
          }

          console.log('Produtos para NF-e:', produtos);
          alert('NF-e emitida com sucesso!');
      });

      // Lógica para o botão "Emitir Cobrança"
      $('#emitirCobranca').on('click', function () {
          const produtos = [];
          $('#tabelaProdutos tbody tr').each(function () {
              const produtoId = $(this).find('input[name*="[id]"]').val();
              const quantidade = $(this).find('input[name*="[quantidade]"]').val();
              const valorUnitario = parseCurrency($(this).find('input[name*="[valor_unitario]"]').val());
              produtos.push({ id: produtoId, quantidade: quantidade, valor_unitario: valorUnitario });
          });

          if (produtos.length === 0) {
              alert('Adicione pelo menos um produto antes de emitir a cobrança.');
              return;
          }

          console.log('Produtos para cobrança:', produtos);
          alert('Cobrança emitida com sucesso!');
      });

      // Lógica para o botão "Exportar PDF"
      $('#exportarPdf').on('click', function () {
          const produtos = [];
          $('#tabelaProdutos tbody tr').each(function () {
              const produtoId = $(this).find('input[name*="[id]"]').val();
              const quantidade = $(this).find('input[name*="[quantidade]"]').val();
              const valorUnitario = parseCurrency($(this).find('input[name*="[valor_unitario]"]').val());
              produtos.push({ id: produtoId, quantidade: quantidade, valor_unitario: valorUnitario });
          });

          if (produtos.length === 0) {
              alert('Adicione pelo menos um produto antes de exportar o PDF.');
              return;
          }

          console.log('Produtos para PDF:', produtos);
          alert('PDF exportado com sucesso!');
      });

      // Inicializa os modais
      $('#modalAdicionarProduto').modal({ backdrop: true, keyboard: true });
      $('#modalEmitirNFe').modal({ backdrop: true, keyboard: true });
      $('#modalEmitirCobranca').modal({ backdrop: true, keyboard: true });
  });

  // Evento de submit do formulário
  $('#formEditarVenda').on('submit', function (e) {
      e.preventDefault();

      // Recupera os produtos da tabela
      const produtos = [];
      $('#tabelaProdutos tbody tr').each(function() {
          if (!$(this).attr('id') || !$(this).attr('id').includes('tabelaVazia')) {
              const produtoId = $(this).attr('data-produto-id');
              const quantidade = $(this).find('.quantidade').val();
              const valorUnitario = $(this).find('.valor-unitario').val();

              // Verifica se os campos existem
              if (produtoId && quantidade && valorUnitario) {
                  produtos.push({
                      id: produtoId,
                      quantidade: parseInt(quantidade),
                      valor_unitario: valorUnitario,
                  });
              }
          }
      });

      if (produtos.length === 0) {
          alert('Adicione pelo menos um produto à venda.');
          return;
      }

      // Remove o campo hidden anterior se existir
      $('#produtosHidden input[name="produtos"]').remove();

      // Cria um campo oculto para enviar os produtos como JSON
      const produtosHidden = $('<input>', {
          type: 'hidden',
          name: 'produtos',
          value: JSON.stringify(produtos)
      });
      $('#produtosHidden').append(produtosHidden);

      // Envia o formulário
      this.submit();
  });


  $(document).ready(function () {
    // Abre o modal de cobrança
    $('#abrirModalCobranca').on('click', function () {
        $('#modalCobranca').modal('show');
    });

    // Gerar PIX
    $('#gerarPix').on('click', function () {
        const vendaId = $('#vendaId').val(); // ID da venda
        const enviarEmail = $('#enviarEmail').is(':checked'); // Verifica se o e-mail deve ser enviado

        // Envia os dados via POST
        $.ajax({
            url: `/vendas/${vendaId}/gerar-cobranca`,
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                metodoPagamento: 'pix',
                enviarEmail: enviarEmail
            },
            success: function (response) {
                if (response.redirectUrl) {
                    window.location.href = response.redirectUrl; // Redireciona para o PagSeguro
                } else {
                    alert('Cobrança gerada com sucesso!');
                }
            },
            error: function (xhr) {
                alert('Erro ao gerar cobrança: ' + xhr.responseJSON.message);
            }
        });
    });

    // Gerar Boleto
    $('#gerarBoleto').on('click', function () {
        const vendaId = $('#vendaId').val(); // ID da venda
        const enviarEmail = $('#enviarEmail').is(':checked'); // Verifica se o e-mail deve ser enviado

        // Envia os dados via POST
        $.ajax({
            url: `/vendas/${vendaId}/gerar-cobranca`,
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                metodoPagamento: 'boleto',
                enviarEmail: enviarEmail
            },
            success: function (response) {
                if (response.redirectUrl) {
                    window.location.href = response.redirectUrl; // Redireciona para o PagSeguro
                } else {
                    alert('Cobrança gerada com sucesso!');
                }
            },
            error: function (xhr) {
                alert('Erro ao gerar cobrança: ' + xhr.responseJSON.message);
            }
        });
    });
});
  </script>
