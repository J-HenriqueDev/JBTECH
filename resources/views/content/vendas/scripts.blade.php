<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
<script>
let custoCombustivel = 0; // Variável global para armazenar o custo do combustível

function formatCurrency(value) {
  if (isNaN(value) || value === null) return 'R$ 0,00';
  value = Math.abs(parseFloat(value)).toFixed(2);
  return `R$ ${value.replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.')}`;
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
};

$(document).ready(function () {
    // Configura o idioma do Select2
    $.fn.select2.defaults.set('language', 'pt-BR');

    // Inicializa o Select2 para o campo de produtos
    $('#produto_id').select2({
        tags: false, // Permite adicionar valores não listados
        dropdownParent: $('#modalAdicionarProduto'),
        placeholder: 'Selecione um produto',
        width: '100%'
    });

    $('#produto_id').on('change', function () {
      const preco = $(this).find(':selected').data('preco'); // Obtém o preço do produto selecionado
      if (preco) {
          $('#valor_unitario').val(formatCurrency(preco)); // Formata o valor e preenche o campo
          const quantidade = parseInt($('#quantidade').val() || 1);
          $('#valor_total').val(formatCurrency(preco * quantidade)); // Calcula o valor total
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

    // Inicializa o Select2 para o campo de clientes
    $('#select2Cliente').select2({
        tags: true, // Permite adicionar valores não listados
        placeholder: 'Selecione um cliente',
        width: '100%'
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
