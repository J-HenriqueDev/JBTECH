<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
<script>
let custoCombustivel = 0; // Variável global para armazenar o custo do combustível

// Verifica se a view é de edição
const isEditView = window.location.pathname.includes('/edit');

// Formata um valor como moeda (R$)
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

    // Atualiza o valor unitário e total ao selecionar um produto
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
