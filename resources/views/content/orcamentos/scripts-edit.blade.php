<script>
    function initEditOrcamentoScripts() {
        if (typeof $ === 'undefined' || typeof $.fn.select2 === 'undefined') {
            setTimeout(initEditOrcamentoScripts, 100);
            return;
        }

        let custoCombustivel = 0; // Variável global para armazenar o custo do combustível

        // Função para exibir mensagens flash (substituindo alert)
        function showFlashMessage(type, message) {
            const event = new CustomEvent('banner-message', {
                detail: {
                    style: type,
                    message: message
                }
            });
            document.dispatchEvent(event);
        }

        // Formata valores em moeda brasileira (usa função global se disponível)
        window.formatCurrency = function(value) {
            if (value === undefined || value === null || value === '') {
                return 'R$ 0,00';
            }
            const numValue = parseFloat(value);
            if (isNaN(numValue)) return 'R$ 0,00';
            const formatted = Math.abs(numValue).toFixed(2);
            return `R$ ${formatted.replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.')}`;
        };

        // Remove a formatação de moeda e retorna um número
        window.parseCurrency = function(value) {
            if (!value || value === undefined || value === null) return 0;
            if (typeof value !== 'string') return parseFloat(value) || 0;
            const cleaned = value.replace('R$', '').replace(/\./g, '').replace(',', '.').trim();
            return parseFloat(cleaned) || 0;
        };

        // Formata o valor do campo de serviço (usa função global se disponível)
        window.formatCurrencyService = function(input) {
            let value = input.value.replace(/\D/g, '');
            if (value === '') {
                input.value = 'R$ 0,00';
                return;
            }
            let intValue = parseInt(value, 10) / 100;
            input.value = intValue.toLocaleString('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            });
        };

        // Valida se o valor de serviço é maior ou igual ao custo de combustível
        window.validarValorServico = function() {
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
        };

        let produtosCarregados = false;
        let carregandoProdutos = false;

        // Função para atualizar os produtos
        function atualizarProdutos() {
            carregandoProdutos = true;
            console.log('Carregando produtos...');

            $.ajax({
                url: '/produtos/lista',
                method: 'GET',
                success: function(produtos) {
                    console.log('Produtos carregados:', produtos);
                    $('#produto_id').empty();
                    $('#produto_id').append(
                        '<option value="" disabled selected>Selecione um produto</option>');

                    produtos.forEach(function(produto) {
                        const option = new Option(
                            `${produto.nome} - R$ ${parseFloat(produto.preco_venda).toFixed(2).replace('.', ',')}${produto.estoque !== undefined ? ' (Estoque: ' + produto.estoque + ')' : ''}`,
                            produto.id,
                            false,
                            false
                        );
                        $(option).data('preco', parseFloat(produto.preco_venda) || 0);
                        $(option).data('estoque', produto.estoque ?? 0);
                        $('#produto_id').append(option);
                    });

                    $('#produto_id').trigger('change.select2');
                    produtosCarregados = true;
                },
                error: function(xhr, status, error) {
                    console.error('Erro na requisição:', error);
                    showFlashMessage('danger', 'Erro ao carregar os produtos. Tente novamente mais tarde.');
                },
                complete: function() {
                    carregandoProdutos = false;
                }
            });
        }

        // Função para adicionar produto na tabela
        function adicionarProdutoNaTabela(id, nome, quantidade, precoUnitario) {
            const valorTotal = precoUnitario * quantidade;

            let produtoExiste = false;
            $('#tabelaProdutos tbody tr').each(function() {
                const idExistente = $(this).find('input[name*="[id]"]').val();
                if (idExistente == id) {
                    produtoExiste = true;
                    return false;
                }
            });

            if (produtoExiste) {
                showFlashMessage('warning', 'Produto já adicionado. Remova-o primeiro se desejar alterar.');
                return false;
            }

            const precoUnitarioFormatado = precoUnitario.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            const valorTotalFormatado = valorTotal.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            $('#tabelaProdutos tbody').append(`
            <tr>
                <td>
                    <input type="hidden" name="produtos[${id}][id]" value="${id}">${id}
                </td>
                <td><strong>${nome}</strong></td>
                <td>
                    <input type="number" class="form-control" name="produtos[${id}][quantidade]" value="${quantidade}" min="1" onchange="atualizarValorTotalTabela()">
                </td>
                <td>
                    <input type="text" class="form-control" name="produtos[${id}][valor_unitario]" value="R$ ${precoUnitarioFormatado}" oninput="formatCurrencyService(this); atualizarValorTotalTabela()">
                </td>
                <td class="valor-total" data-valor="${valorTotal}"><strong>R$ ${valorTotalFormatado}</strong></td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm" onclick="removerProduto(this)">Remover</button>
                </td>
            </tr>
        `);

            atualizarMensagemTabela();
            atualizarValorTotalTabela();
            return true;
        }

        window.removerProduto = function(btn) {
            $(btn).closest('tr').remove();
            atualizarMensagemTabela();
            atualizarValorTotalTabela();
        };

        window.atualizarValorTotalTabela = function() {
            let totalGeral = 0;
            $('#tabelaProdutos tbody tr').each(function() {
                const quantidade = parseFloat($(this).find('input[name*="[quantidade]"]').val()) || 0;
                const valorUnitarioInput = $(this).find('input[name*="[valor_unitario]"]').val();
                const valorUnitario = parseCurrency(valorUnitarioInput);

                const totalItem = quantidade * valorUnitario;
                $(this).find('.valor-total').data('valor', totalItem);
                $(this).find('.valor-total strong').text('R$ ' + totalItem.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));

                totalGeral += totalItem;
            });

            const valorServico = parseCurrency($('#valor_servico').val());
            totalGeral += valorServico;

            $('#valorTotalTabela').text('R$ ' + totalGeral.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
        };

        window.atualizarMensagemTabela = function() {
            if ($('#tabelaProdutos tbody tr').length === 0) {
                if ($('#tabelaVazia').length === 0) {
                    $('#tabelaProdutos tbody').append(`
                    <tr id="tabelaVazia">
                        <td colspan="6" class="text-center py-4 text-muted">
                            Nenhum item adicionado.
                        </td>
                    </tr>
                `);
                }
            } else {
                $('#tabelaVazia').remove();
            }
        };

        function buscarProdutoPorCodigo(barcode, qtd = 1, customPrice = null) {
            $('#barcode-input').prop('disabled', true);

            $.ajax({
                url: '{{ route('produtos.buscar-codigo') }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    termo: barcode
                },
                success: function(response) {
                    if (response.success) {
                        const p = response.produto;
                        let valor = parseFloat(p.preco_venda);
                        if (customPrice !== null && !isNaN(customPrice)) {
                            valor = customPrice;
                        }

                        if (adicionarProdutoNaTabela(p.id, p.nome, qtd, valor)) {
                            // Sucesso
                        }
                        $('#barcode-input').val('').focus();
                    } else {
                        showFlashMessage('warning', response.message || 'Produto não encontrado');
                        $('#barcode-input').val('').select();
                    }
                },
                error: function(xhr) {
                    console.error(xhr);
                    showFlashMessage('danger', 'Erro ao buscar produto: ' + (xhr.responseJSON?.message ||
                        'Erro desconhecido'));
                    $('#barcode-input').val('').select();
                },
                complete: function() {
                    $('#barcode-input').prop('disabled', false).focus();
                }
            });
        }

        // Evento do Scanner de Código de Barras
        $('#barcode-input').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                const input = $(this).val().trim();

                if (input) {
                    let qtd = 1;
                    let barcode = input;
                    let price = null;
                    const parts = input.split('*');

                    if (parts.length >= 3) {
                        qtd = parseFloat(parts[0].trim()) || 1;
                        barcode = parts[1].trim();
                        let priceStr = parts[2].trim().replace(',', '.');
                        price = parseFloat(priceStr);
                    } else if (parts.length === 2) {
                        qtd = parseFloat(parts[0].trim()) || 1;
                        barcode = parts[1].trim();
                    } else {
                        const match = input.match(/^(\d+)\s*[xX]\s*(.+)$/);
                        if (match) {
                            qtd = parseFloat(match[1]);
                            barcode = match[2];
                        }
                    }

                    buscarProdutoPorCodigo(barcode, qtd, price);
                }
            }
        });

        // Inicializa o Select2 para o campo de clientes
        $('#select2Basic').select2({
            tags: false,
            placeholder: 'Selecione um cliente',
            width: '100%',
            allowClear: true
        });

        $('#select2Basic').on('change', function() {
            const enderecoCliente = $(this).find(':selected').data('endereco');
            $('#endereco_cliente').val(enderecoCliente || '');
        });

        // Preencher endereço inicial se disponível
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

        atualizarProdutos();

        // Toggle parcelas boleto
        function toggleParcelasBoleto() {
            if ($('#pagamento_boleto').is(':checked')) {
                $('#div_parcelas_boleto').slideDown();
            } else {
                $('#div_parcelas_boleto').slideUp();
            }
        }

        $('#pagamento_boleto').on('change', toggleParcelasBoleto);

        $('#produto_id').on('select2:opening', function(e) {
            if (!produtosCarregados && !carregandoProdutos) {
                e.preventDefault();
                atualizarProdutos();
            }
        });

        $('#adicionarProduto').on('click', function() {
            const id = $('#produto_id').val();
            const nome = $('#produto_id option:selected').text().split(' - R$')[0];
            const preco = parseCurrency($('#valor_unitario').val());
            const qtd = parseInt($('#quantidade').val());

            if (id && qtd > 0) {
                if (adicionarProdutoNaTabela(id, nome, qtd, preco)) {
                    $('#modalAdicionarProduto').modal('hide');
                    $('#produto_id').val('').trigger('change');
                    $('#valor_unitario').val('');
                    $('#quantidade').val(1);
                    $('#valor_total').val('');
                }
            } else {
                showFlashMessage('danger', 'Selecione um produto e uma quantidade válida.');
            }
        });

        $('#quantidade, #valor_unitario').on('input', function() {
            const qtd = parseInt($('#quantidade').val()) || 0;
            const valor = parseCurrency($('#valor_unitario').val());
            const total = qtd * valor;
            $('#valor_total').val('R$ ' + total.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
        });

        $('#produto_id').on('select2:select', function(e) {
            const preco = $(this).find(':selected').data('preco');
            const estoque = $(this).find(':selected').data('estoque');

            $('#valor_unitario').val('R$ ' + preco.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
            $('#quantidade').val(1).trigger('input');

            if (estoque !== undefined) {
                $('#estoqueInfo').text(`Estoque disponível: ${estoque}`);
            } else {
                $('#estoqueInfo').text('');
            }
        });

        // Botão Calcular Distância
        $('#calcularDistancia').on('click', function() {
            const endereco = $('#endereco_cliente').val();
            if (!endereco) {
                showFlashMessage('warning', 'Selecione um cliente com endereço cadastrado.');
                return;
            }

            const btn = $(this);
            const originalText = btn.html();
            btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Calculando...');

            $.ajax({
                url: '{{ route('orcamentos.obter-coordenadas') }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    endereco_cliente: endereco
                },
                success: function(response) {
                    if (response.lat && response.lng) {
                        // Aqui você pode adicionar lógica para calcular distância da loja
                        // Por enquanto, apenas mostra as coordenadas ou abre no mapa
                        window.open(
                            `https://www.google.com/maps/dir/?api=1&destination=${response.lat},${response.lng}`,
                            '_blank');
                    } else if (response.error) {
                        showFlashMessage('danger', response.error);
                    }
                },
                error: function() {
                    showFlashMessage('danger', 'Erro ao calcular distância.');
                },
                complete: function() {
                    btn.prop('disabled', false).html(originalText);
                }
            });
        });
    }

    // Inicia o script com retry
    initEditOrcamentoScripts();
</script>
