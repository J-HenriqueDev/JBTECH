<script>
    let custoCombustivel = 0; // Variável global para armazenar o custo do combustível

    // Função para exibir mensagens flash (substituindo alert)
    function showFlashMessage(type, message) {
        const event = new CustomEvent('banner-message', {
            detail: {
                style: type, // 'success' or 'danger'
                message: message
            }
        });
        document.dispatchEvent(event);
    }

    // Formata valores em moeda brasileira
    function formatMoney(value) {
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
        if (!value || value === undefined || value === null) return 0;
        if (typeof value !== 'string') return parseFloat(value) || 0;
        const cleaned = value.replace('R$', '').replace(/\./g, '').replace(',', '.').trim();
        return parseFloat(cleaned) || 0;
    }

    // Formata o valor do campo de serviço
    function formatCurrencyService(input) {
        if (typeof window.formatCurrencyInput === 'function') {
            window.formatCurrencyInput(input);
            return;
        }
        if (typeof window.formatCurrency === 'function' && window.formatCurrency.length === 1 && window.formatCurrency
            .name !== 'formatCurrency') {
            // Fallback only if we are sure it's the input version (hard to know without name, but checking length is something).
            // Actually, let's just rely on formatCurrencyInput or local logic.
        }

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
    }

    // Função principal de inicialização com retry
    function initOrcamentoScripts() {
        if (typeof $ === 'undefined' || typeof $.fn.select2 === 'undefined') {
            setTimeout(initOrcamentoScripts, 50);
            return;
        }

        // Funções globais que dependem do jQuery (usadas em eventos HTML)
        window.validarValorServico = function() {
            const valorServicoInput = $('#valor_servico');
            const valorServico = parseCurrency(valorServicoInput.val());

            if (valorServico < custoCombustivel) {
                valorServicoInput.addClass('is-invalid');
                $('#alertCustoCombustivel').removeClass('d-none').addClass('alert-danger').html(`
                  <strong>O valor do serviço deve ser maior ou igual ao custo de combustível (${formatMoney(custoCombustivel)}).</strong>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              `);
                return false;
            } else {
                valorServicoInput.removeClass('is-invalid');
                $('#alertCustoCombustivel').addClass('d-none');
                return true;
            }
        };

        window.removerProduto = function(button) {
            $(button).closest('tr').remove();
            atualizarMensagemTabela();
            atualizarValorTotalTabela();
        };

        window.atualizarValorTotalTabela = function() {
            let total = 0;
            $('#tabelaProdutos tbody tr').each(function() {
                if ($(this).attr('id') === 'tabelaVazia') return;

                let valor = parseFloat($(this).find('.valor-total').data('valor'));
                const quantidadeInput = $(this).find('input[name*="[quantidade]"]');
                const valorUnitarioInput = $(this).find('input[name*="[valor_unitario]"]');

                if (quantidadeInput.length && valorUnitarioInput.length) {
                    const qtd = parseInt(quantidadeInput.val()) || 0;
                    const vlrUnit = parseCurrency(valorUnitarioInput.val());
                    valor = qtd * vlrUnit;
                    $(this).find('.valor-total').html(`<strong>${formatMoney(valor)}</strong>`);
                    $(this).find('.valor-total').data('valor', valor);
                } else if (isNaN(valor)) {
                    valor = parseCurrency($(this).find('.valor-total').text());
                }
                total += valor;
            });
            $('#valorTotalTabela').text(formatMoney(total));
        };

        // Funções auxiliares internas
        function atualizarMensagemTabela() {
            if ($('#tabelaProdutos tbody tr').length === 0) {
                $('#tabelaVazia').show();
            } else {
                $('#tabelaVazia').hide();
            }
        }

        function limparCamposModal() {
            $('#produto_id').val(null).trigger('change');
            $('#quantidade').val(1);
            $('#valor_unitario').val('');
            $('#valor_total').val('');
        }

        let produtosCarregados = false;
        let carregandoProdutos = false;

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
                    // Não abrir o dropdown automaticamente para não atrapalhar
                },
                error: function(xhr, status, error) {
                    console.error('Erro na requisição:', error);
                    showFlashMessage('danger', 'Erro ao carregar os produtos.');
                },
                complete: function() {
                    carregandoProdutos = false;
                    produtosCarregados = true;
                }
            });
        }

        // Função para adicionar produto na tabela (Reutilizável)
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
                    <input type="text" class="form-control" name="produtos[${id}][valor_unitario]" value="${formatMoney(precoUnitario)}" oninput="formatCurrencyService(this); atualizarValorTotalTabela()">
                </td>
                <td class="valor-total" data-valor="${valorTotal}"><strong>${formatMoney(valorTotal)}</strong></td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm" onclick="removerProduto(this)">Remover</button>
                </td>
            </tr>
        `);

            atualizarMensagemTabela();
            atualizarValorTotalTabela();
            return true;
        }

        // Busca produto por código de barras
        function buscarProdutoPorCodigo(barcode, qtd = 1, customPrice = null) {
            $('#barcode-input').prop('disabled', true);

            $.ajax({
                url: '{{ route('produtos.buscar-codigo') }}', // Rota deve existir (usada no NFe)
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

        // Inicialização do DOM
        $(document).ready(function() {
            // Evento do Scanner de Código de Barras
            $('#barcode-input').on('keypress', function(e) {
                if (e.which === 13) { // Enter key
                    e.preventDefault();
                    const input = $(this).val().trim();

                    if (input) {
                        let qtd = 1;
                        let barcode = input;
                        let price = null;

                        // Check for Qtd * Code * Price pattern (e.g. 2*789*1,29)
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

            // Inicializa Select2 Produtos
            $('#produto_id').select2({
                tags: false,
                dropdownParent: $('#modalAdicionarProduto'),
                placeholder: 'Selecione um produto',
                width: '100%',
                allowClear: true
            });

            // Autofocus no Select2 ao abrir o modal
            $('#modalAdicionarProduto').on('shown.bs.modal', function() {
                $('#produto_id').select2('open');
            });

            // Inicializa Select2 Clientes
            $('#select2Basic').select2({
                tags: false,
                placeholder: 'Selecione um cliente',
                width: '100%',
                allowClear: true
            });

            // Carrega produtos
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
            toggleParcelasBoleto(); // Init state

            // Eventos
            $('#produto_id').on('select2:opening', function(e) {
                if (!produtosCarregados && !carregandoProdutos) {
                    e.preventDefault();
                    atualizarProdutos();
                }
            });

            $('#produto_id').on('change', function() {
                const selectedId = $(this).val();
                if (!selectedId) {
                    $('#valor_unitario').val('');
                    $('#valor_total').val('');
                    return;
                }
                const selectedOption = $(this).find('option[value="' + selectedId + '"]');
                const preco = parseFloat(selectedOption.data('preco')) || 0;

                if (preco > 0) {
                    $('#valor_unitario').val(formatMoney(preco));
                    const quantidade = parseInt($('#quantidade').val() || 1);
                    $('#valor_total').val(formatMoney(preco * quantidade));
                }
            });

            $('#quantidade').on('input', function() {
                const preco = parseCurrency($('#valor_unitario').val());
                const quantidade = parseInt($(this).val() || 1);
                $('#valor_total').val(formatMoney(preco * quantidade));
            });

            $('#btn-minus-qtd').on('click', function() {
                let qtd = parseInt($('#quantidade').val()) || 1;
                if (qtd > 1) {
                    $('#quantidade').val(qtd - 1).trigger('input');
                }
            });

            $('#btn-plus-qtd').on('click', function() {
                let qtd = parseInt($('#quantidade').val()) || 1;
                $('#quantidade').val(qtd + 1).trigger('input');
            });

            $('#adicionarProduto').on('click', function() {
                const produtoId = $('#produto_id').val();
                const produtoTexto = $('#produto_id option:selected').text();
                const produtoNome = produtoTexto.split(' - ')[0];
                const precoUnitario = parseCurrency($('#valor_unitario').val());
                const quantidade = parseInt($('#quantidade').val() || 1);
                const valorTotal = precoUnitario * quantidade;

                if (!produtoId || precoUnitario <= 0 || quantidade <= 0) {
                    showFlashMessage('danger', 'Preencha todos os campos corretamente.');
                    return;
                }

                let produtoExiste = false;
                $('#tabelaProdutos tbody tr').each(function() {
                    const idExistente = $(this).find('input[name*="[id]"]').val();
                    if (idExistente == produtoId) {
                        produtoExiste = true;
                        return false;
                    }
                });

                if (produtoExiste) {
                    showFlashMessage('warning', 'Produto já adicionado.');
                    return;
                }

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
                        <input type="text" class="form-control" name="produtos[${produtoId}][valor_unitario]" value="${formatMoney(precoUnitario)}" oninput="formatCurrencyService(this); atualizarValorTotalTabela()">
                    </td>
                    <td class="valor-total" data-valor="${valorTotal}"><strong>${formatMoney(valorTotal)}</strong></td>
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

            $('#select2Basic').on('change', function() {
                const enderecoCliente = $(this).find(':selected').data('endereco');
                $('#endereco_cliente').val(enderecoCliente || '');
            });

            $('#adicionarServico').on('click', function() {
                if (!validarValorServico()) return;
                const valorServico = parseCurrency($('#valor_servico').val());

                let servicoExiste = false;
                $('#tabelaProdutos tbody tr').each(function() {
                    // ID do serviço é 1 (hardcoded no append abaixo)
                    // Verifica se existe input hidden com value 1 ou se a primeira célula é 1
                    // Como serviço não tem input hidden ID no código original, verificamos texto
                    if ($(this).find('td:first').text().trim() == '1') {
                        servicoExiste = true;
                        return false;
                    }
                });

                if (servicoExiste) {
                    showFlashMessage('warning', 'Serviço já adicionado.');
                    return;
                }

                $('#tabelaProdutos tbody').append(`
                <tr>
                    <td>1</td>
                    <td><strong>Serviço</strong></td>
                    <td>
                        <input type="number" class="form-control" name="produtos[1][quantidade]" value="1" readonly>
                    </td>
                    <td>
                        <input type="text" class="form-control" name="produtos[1][valor_unitario]" value="${formatMoney(valorServico)}" readonly>
                    </td>
                    <td class="valor-total" data-valor="${valorServico}"><strong>${formatMoney(valorServico)}</strong></td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm" onclick="removerProduto(this)">Remover</button>
                    </td>
                </tr>
            `);

                $('#valor_servico').val('');
                atualizarMensagemTabela();
                atualizarValorTotalTabela();
            });

            $('#modalAdicionarProduto').on('hidden.bs.modal', function() {
                limparCamposModal();
            });

            $('#calcularDistancia').on('click', function() {
                if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
                    showFlashMessage('danger',
                        'Funcionalidade indisponível: Chave API Google Maps não configurada.');
                    return;
                }

                const enderecoCliente = $('#endereco_cliente').val();
                const btn = $(this);
                const originalText = btn.html();

                if (!enderecoCliente) {
                    showFlashMessage('warning', 'Informe o endereço do cliente.');
                    return;
                }

                btn.prop('disabled', true).html(
                    '<i class="bx bx-loader-alt bx-spin me-1"></i> Calculando...');

                const service = new google.maps.DistanceMatrixService();
                service.getDistanceMatrix({
                    origins: [window.empresaEndereco],
                    destinations: [enderecoCliente],
                    travelMode: 'DRIVING',
                    unitSystem: google.maps.UnitSystem.METRIC,
                }, function(response, status) {
                    btn.prop('disabled', false).html(originalText);

                    if (status !== 'OK') {
                        showFlashMessage('danger', 'Erro API Google: ' + status);
                        return;
                    }
                    const results = response.rows[0].elements[0];
                    if (results.status !== 'OK') {
                        showFlashMessage('warning', 'Não foi possível calcular a distância.');
                        return;
                    }

                    const distanciaKm = results.distance.value / 1000;
                    const custoPorKm = window.custoPorKm || 1.50;
                    const custoEstimado = distanciaKm * 2 * custoPorKm;

                    custoCombustivel = custoEstimado;
                    $('#valorCombustivelAlert').text(formatMoney(custoEstimado) +
                        ` (${results.distance.text})`);
                    $('#alertCustoCombustivel').removeClass('d-none').removeClass(
                        'alert-danger').addClass('alert-warning');

                    validarValorServico();
                });
            });

            // Toggle Boleto Parcels
            $('#pagamento_boleto').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#div_parcelas_boleto').fadeIn();
                    $('input[name="parcelas_boleto"]').focus();
                } else {
                    $('#div_parcelas_boleto').fadeOut();
                    $('input[name="parcelas_boleto"]').val('');
                }
            });
        });
    }

    initOrcamentoScripts();
</script>
