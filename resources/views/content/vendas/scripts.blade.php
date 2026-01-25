<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
<script>
let custoCombustivel = 0; // Variável global para armazenar o custo do combustível
let listaProdutos = []; // Lista completa de produtos carregados

// Função para atualizar os produtos
function atualizarProdutos() {
  carregandoProdutos = true; // Indica que uma requisição está em andamento
  console.log('Carregando produtos...');

  $.ajax({
      url: '/produtos/lista', // Altere para a URL correta da sua rota de produtos
      method: 'GET',
      success: function (produtos) {
          console.log('Produtos carregados:', produtos);
          listaProdutos = produtos; // Salva na variável global

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
    reindexarProdutos();
};

$(document).ready(function () {
    // Aplica configurações padrão de venda
    @if(isset($formaPagamentoPadrao))
    var formaPagamentoPadrao = '{{ $formaPagamentoPadrao }}';
    @else
    var formaPagamentoPadrao = 'dinheiro';
    @endif

    @if(isset($descontoMaximo))
    var descontoMaximo = {{ $descontoMaximo }};
    @else
    var descontoMaximo = 10;
    @endif

    // Configura o idioma do Select2
    $.fn.select2.defaults.set('language', 'pt-BR');

    // Inicializa o Select2 para o campo de produtos
    $('#produto_id').select2({
        tags: false, // Permite adicionar valores não listados
        dropdownParent: $('#modalAdicionarProduto'),
        placeholder: 'Selecione um produto',
        width: '100%'
    });
    limparCamposModal();

    // Autofocus no Select2 ao abrir o modal
    $('#modalAdicionarProduto').on('shown.bs.modal', function () {
        $('#produto_id').select2('open');
    });

     // Carrega os produtos ao carregar a página
     atualizarProdutos();

     $('#produto_id').on('select2:opening', function (e) {
       if (!produtosCarregados && !carregandoProdutos) {
           e.preventDefault(); // Impede a abertura automática do dropdown
           atualizarProdutos(); // Atualiza os produtos
       }
   });

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
        let valorTotal = preco * quantidade;

        // Aplica desconto máximo se configurado (será implementado quando houver campo de desconto)
        // if (descontoMaximo > 0) {
        //     const desconto = Math.min(descontoMaximo, (valorTotal * descontoMaximo / 100));
        //     valorTotal -= desconto;
        // }

        $('#valor_total').val(formatCurrency(valorTotal));
    });

    // Adiciona o produto na tabela
    function adicionarProdutoTabela(produtoId, produtoNome, precoUnitario, quantidade, valorTotal) {
         if (!produtoId || precoUnitario <= 0 || quantidade <= 0) {
            alert('Por favor, preencha todos os campos corretamente antes de adicionar um produto.');
            return;
        }

        // Verifica se o produto já foi adicionado
        const produtoJaExiste = $('#tabelaProdutos tbody tr').filter(function() {
            return $(this).find('input[type="hidden"][name*="[id]"]').val() == produtoId;
        }).length > 0;

        if (produtoJaExiste) {
            alert('Este produto já foi adicionado à venda. Remova-o primeiro se desejar alterar a quantidade.');
            return;
        }

        // Conta quantos produtos já existem para usar como índice
        const indiceProduto = $('#tabelaProdutos tbody tr').not('#tabelaVazia').length;

        // Adiciona uma linha à tabela com os campos `name` necessários
        $('#tabelaProdutos tbody').append(`
            <tr data-produto-id="${produtoId}">
                <td>
                    <input type="hidden" name="produtos[${indiceProduto}][id]" value="${produtoId}">${produtoId}
                </td>
                <td>${produtoNome}</td>
                <td>
                    <input type="number" class="form-control" name="produtos[${indiceProduto}][quantidade]" value="${quantidade}" min="1" readonly>
                </td>
                <td>
                    <input type="text" class="form-control" name="produtos[${indiceProduto}][valor_unitario]" value="${formatCurrency(precoUnitario)}" readonly>
                </td>
                <td class="valor-total">${formatCurrency(valorTotal)}</td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm" onclick="removerProduto(this)">Remover</button>
                </td>
            </tr>
        `);

        // Fecha o modal e limpa os campos se estiver aberto
        $('#modalAdicionarProduto').modal('hide');
        limparCamposModal();
        atualizarMensagemTabela();
        atualizarValorTotalTabela();

        // Reindexa os produtos após adicionar
        reindexarProdutos();
    }

    // Event listener para o botão do modal
    $('#adicionarProduto').on('click', function () {
        const produtoId = $('#produto_id').val();
        const produtoNome = $('#produto_id option:selected').text().split(' - ')[0];
        const precoUnitario = parseCurrency($('#valor_unitario').val());
        const quantidade = parseInt($('#quantidade').val() || 1);
        const valorTotal = precoUnitario * quantidade;

        adicionarProdutoTabela(produtoId, produtoNome, precoUnitario, quantidade, valorTotal);
    });

    // Função para reindexar os produtos na tabela
    function reindexarProdutos() {
        $('#tabelaProdutos tbody tr').not('#tabelaVazia').each(function(index) {
            $(this).find('input[name*="[id]"]').attr('name', `produtos[${index}][id]`);
            $(this).find('input[name*="[quantidade]"]').attr('name', `produtos[${index}][quantidade]`);
            $(this).find('input[name*="[valor_unitario]"]').attr('name', `produtos[${index}][valor_unitario]`);
        });
    }

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

    // Atualiza o endereço do cliente ao selecionar
    $('#select2Basic').on('change', function () {
        const enderecoCliente = $(this).find(':selected').data('endereco');
        $('#endereco_cliente').val(enderecoCliente || '');
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

      // Aqui você pode adicionar a lógica para emitir a NF-e
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

        // Aqui você pode adicionar a lógica para emitir a cobrança
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

        // Aqui você pode adicionar a lógica para exportar o PDF
        console.log('Produtos para PDF:', produtos);
        alert('PDF exportado com sucesso!');
    });

    // Inicializa os modais
    $('#modalAdicionarProduto').modal({ backdrop: true, keyboard: true });
    $('#modalEmitirNFe').modal({ backdrop: true, keyboard: true });
    $('#modalEmitirCobranca').modal({ backdrop: true, keyboard: true });
});

document.getElementById('formEditarVenda').addEventListener('submit', function (e) {
  e.preventDefault();

  // Recupera os produtos da tabela
  const produtos = [];
  document.querySelectorAll('#tabelaProdutos tbody tr').forEach((tr, index) => {
      if (!tr.id.includes('tabelaVazia')) {
          const produtoId = tr.getAttribute('data-produto-id');
          const quantidadeInput = tr.querySelector('.quantidade');
          const valorUnitarioInput = tr.querySelector('.valor-unitario');

          // Verifica se os campos existem
          if (quantidadeInput && valorUnitarioInput) {
              const quantidade = quantidadeInput.value;
              const valorUnitario = valorUnitarioInput.value;

              produtos.push({
                  id: produtoId,
                  quantidade: quantidade,
                  valor_unitario: valorUnitario,
              });
          }
      }
  });

  // Cria um campo oculto para enviar os produtos como JSON
  const produtosHidden = document.createElement('input');
  produtosHidden.type = 'hidden';
  produtosHidden.name = 'produtos';
  produtosHidden.value = JSON.stringify(produtos);
  this.appendChild(produtosHidden);

  // Envia o formulário
  this.submit();
});
</script>
