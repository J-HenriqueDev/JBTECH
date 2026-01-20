@extends('layouts.layoutMaster')

@section('title', 'Processar Nota de Entrada')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/animate-css/animate.scss'
])
<style>
    .item-card {
        border-left: 4px solid #696cff;
        transition: all 0.2s;
    }

    .item-card:hover {
        background-color: #f8f9fa;
    }

    .acao-radio:checked+.form-check-label {
        font-weight: bold;
        color: #696cff;
    }

    .xml-badge {
        font-size: 0.75rem;
        background: #e7e7ff;
        color: #696cff;
        padding: 2px 6px;
        border-radius: 4px;
        margin-right: 5px;
    }
</style>
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/select2/select2.js'
])
@endsection

@section('page-script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Aguarda jQuery ser carregado (devido ao carregamento via Vite)
        const waitForJQuery = setInterval(function() {
            if (window.jQuery) {
                clearInterval(waitForJQuery);
                initProcessarNota(window.jQuery);
            }
        }, 50);

        function initProcessarNota($) {
            // Inicializa Select2 nos campos usando AJAX para performance e busca real
            initSelect2();

            function initSelect2() {
                $('.select2-produto').each(function() {
                    if ($(this).hasClass("select2-hidden-accessible")) return;

                    $(this).select2({
                        ajax: {
                            url: '{{ route("produtos.lista") }}',
                            dataType: 'json',
                            delay: 250,
                            data: function(params) {
                                return {
                                    q: params.term // termo de busca
                                };
                            },
                            processResults: function(data) {
                                return {
                                    results: data
                                };
                            },
                            cache: true
                        },
                        placeholder: "Busque por Nome ou Código de Barras...",
                        allowClear: true,
                        width: '100%',
                        minimumInputLength: 1,
                        language: {
                            noResults: function() {
                                return "Nenhum produto encontrado";
                            },
                            inputTooShort: function() {
                                return "Digite nome ou código de barras";
                            },
                            searching: function() {
                                return "Buscando...";
                            }
                        }
                    });

                    // Evento ao selecionar um produto manualmente
                    $(this).on('select2:select', function(e) {
                        var data = e.params.data;
                        var row = $(this).closest('tr');

                        if (data.preco_custo) {
                            row.find('.ultimo-custo').val(data.preco_custo);
                        } else {
                            row.find('.ultimo-custo').val(0);
                        }
                        updateRowCalculations(row);
                    });

                    // Evento ao limpar seleção
                    $(this).on('select2:clear', function(e) {
                        var row = $(this).closest('tr');
                        row.find('.ultimo-custo').val(0);
                        updateRowCalculations(row);
                    });
                });
            }

            // Monitora mudança na ação (Criar vs Associar)
            $(document).on('change', '.acao-radio', function() {
                let index = $(this).data('index');
                let acao = $(this).val();
                let container = $(this).closest('td');

                if (acao === 'associar') {
                    container.find('.produto-novo-container').addClass('d-none');
                    container.find('.produto-associar-container').removeClass('d-none');
                    initSelect2();
                } else if (acao === 'criar') {
                    container.find('.produto-novo-container').removeClass('d-none');
                    container.find('.produto-associar-container').addClass('d-none');
                }
            });

            // LÓGICA DE CÁLCULOS (Conversão, Custos, Margem)

            function formatMoney(value) {
                return parseFloat(value).toFixed(2);
            }

            function formatCost(value) {
                return parseFloat(value).toFixed(4);
            }

            // Atualiza linha quando Fator ou Custo XML muda
            function updateRowCalculations(row) {
                let qtdXml = parseFloat(row.find('.qtd-xml').val()) || 0;
                let custoXml = parseFloat(row.find('.custo-xml').val()) || 0;
                let fator = parseFloat(row.find('.fator-conversao').val()) || 1;

                // Evita divisão por zero
                if (fator <= 0) fator = 1;

                // 1. Calcula Quantidade Sistema
                let qtdSistema = qtdXml * fator;
                row.find('.qtd-sistema').val(qtdSistema);

                // 2. Calcula Custo Unitário Sistema
                let custoSistema = custoXml / fator;
                row.find('.custo-sistema').val(formatCost(custoSistema));

                // 3. Atualiza comparação com último custo
                let ultimoCusto = parseFloat(row.find('.ultimo-custo').val()) || 0;
                let diffContainer = row.find('.custo-diff');

                if (ultimoCusto > 0) {
                    let diff = custoSistema - ultimoCusto;
                    let diffPercent = (diff / ultimoCusto) * 100;

                    let icon = '';
                    let color = '';

                    if (diffPercent > 5) {
                        icon = '<i class="bx bx-up-arrow-alt"></i>';
                        color = 'text-danger'; // Custo subiu
                    } else if (diffPercent < -5) {
                        icon = '<i class="bx bx-down-arrow-alt"></i>';
                        color = 'text-success'; // Custo caiu
                    } else {
                        icon = '<i class="bx bx-minus"></i>';
                        color = 'text-muted'; // Estável
                    }

                    diffContainer.html(`<span class="${color}" title="Anterior: R$ ${formatCost(ultimoCusto)} (${diffPercent.toFixed(1)}%)">${icon} ${diffPercent.toFixed(1)}%</span>`);
                } else {
                    diffContainer.html('<span class="text-muted small">-</span>');
                }

                // 4. Recalcula Preço de Venda baseado na Margem
                updatePriceFromMargin(row);
            }

            // Atualiza Preço de Venda baseado no Custo e Margem
            function updatePriceFromMargin(row) {
                let custoSistema = parseFloat(row.find('.custo-sistema').val()) || 0;
                let margem = parseFloat(row.find('.margem-percent').val()) || 0;

                let precoVenda = custoSistema * (1 + (margem / 100));
                row.find('.preco-venda').val(formatMoney(precoVenda));
            }

            // Atualiza Margem baseada no Preço de Venda e Custo
            function updateMarginFromPrice(row) {
                let custoSistema = parseFloat(row.find('.custo-sistema').val()) || 0;
                let precoVenda = parseFloat(row.find('.preco-venda').val()) || 0;

                if (custoSistema > 0) {
                    let margem = ((precoVenda / custoSistema) - 1) * 100;
                    row.find('.margem-percent').val(margem.toFixed(2));
                }
            }

            // Listeners
            $(document).on('input', '.fator-conversao', function() {
                updateRowCalculations($(this).closest('tr'));
            });

            $(document).on('input', '.margem-percent', function() {
                updatePriceFromMargin($(this).closest('tr'));
            });

            $(document).on('input', '.preco-venda', function() {
                updateMarginFromPrice($(this).closest('tr'));
            });

            $(document).on('input', '.custo-sistema', function() {
                updatePriceFromMargin($(this).closest('tr'));
            });

            // Inicializa cálculos para todas as linhas
            $('tr.item-row').each(function() {
                updateRowCalculations($(this));
            });
        }
    });
</script>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
                <div>
                    <h5 class="mb-0 text-white">Processar Nota: {{ $nota->numero_nfe ? 'NFe '.$nota->numero_nfe : $nota->chave_acesso }}</h5>
                    <small>{{ $nota->emitente_nome }} | Emitida em: {{ \Carbon\Carbon::parse($nota->data_emissao)->format('d/m/Y') }}</small>
                </div>
                <div>
                    <h4 class="mb-0 text-white">R$ {{ number_format($nota->valor_total, 2, ',', '.') }}</h4>
                </div>
            </div>
            <div class="card-body p-0">
                <form action="{{ route('notas-entrada.confirmar', $nota->id) }}" method="POST">
                    @csrf

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50%">Produto XML / Associação</th>
                                    <th style="width: 10%">Conversão</th>
                                    <th style="width: 20%">Custos (Unit.)</th>
                                    <th style="width: 20%">Precificação</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($itens as $index => $item)
                                <tr class="item-row">
                                    <!-- PRODUTO E AÇÃO -->
                                    <td class="align-top border-end">
                                        <div class="d-flex flex-column">
                                            <!-- Nome e Detalhes do XML -->
                                            <div class="mb-3">
                                                <span class="fw-bold text-primary d-block mb-1" style="font-size: 1.05rem;">{{ $item['xProd'] }}</span>
                                                <div class="d-flex flex-wrap gap-2 text-muted small">
                                                    <span class="xml-badge"><i class="bx bx-barcode"></i> {{ $item['cEAN'] }}</span>
                                                    <span class="xml-badge"><i class="bx bx-box"></i> {{ $item['uCom'] }}</span>
                                                    <span class="xml-badge"><i class="bx bx-file"></i> NCM: {{ $item['NCM'] }}</span>
                                                </div>
                                            </div>

                                            <input type="hidden" name="itens[{{ $index }}][nItem]" value="{{ $item['nItem'] }}">
                                            <input type="hidden" name="itens[{{ $index }}][cProd]" value="{{ $item['cProd'] }}">
                                            <input type="hidden" name="itens[{{ $index }}][xProd]" value="{{ $item['xProd'] }}">
                                            <input type="hidden" name="itens[{{ $index }}][cEAN]" value="{{ $item['cEAN'] }}">
                                            <input type="hidden" name="itens[{{ $index }}][NCM]" value="{{ $item['NCM'] }}">
                                            <input type="hidden" name="itens[{{ $index }}][CEST]" value="{{ $item['CEST'] }}">
                                            <input type="hidden" name="itens[{{ $index }}][CFOP]" value="{{ $item['CFOP'] }}">
                                            <input type="hidden" name="itens[{{ $index }}][uCom]" value="{{ $item['uCom'] }}">

                                            <!-- Opções de Ação -->
                                            <div class="bg-light p-3 rounded border">
                                                <div class="d-flex gap-4 mb-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input acao-radio" type="radio"
                                                            name="itens[{{ $index }}][acao]"
                                                            value="criar"
                                                            id="acao-criar-{{ $index }}"
                                                            data-index="{{ $index }}"
                                                            {{ !$item['produto_existente'] ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="acao-criar-{{ $index }}">
                                                            <i class="bx bx-plus-circle"></i> Cadastrar Novo
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input acao-radio" type="radio"
                                                            name="itens[{{ $index }}][acao]"
                                                            value="associar"
                                                            id="acao-associar-{{ $index }}"
                                                            data-index="{{ $index }}"
                                                            {{ $item['produto_existente'] ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="acao-associar-{{ $index }}">
                                                            <i class="bx bx-link"></i> Vincular Existente
                                                        </label>
                                                    </div>
                                                </div>

                                                <!-- Container: Criar Novo -->
                                                <div class="produto-novo-container {{ $item['produto_existente'] ? 'd-none' : '' }}" id="produto-novo-{{ $index }}">
                                                    <label class="form-label small text-muted">Nome do Novo Produto</label>
                                                    <input type="text" class="form-control form-control-sm" name="itens[{{ $index }}][nome_novo]" value="{{ $item['xProd'] }}" placeholder="Nome do Produto">
                                                    <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">
                                                        <i class="bx bx-info-circle"></i> Será criado com os dados fiscais do XML (NCM, Unidade, EAN).
                                                    </small>
                                                </div>

                                                <!-- Container: Associar Existente -->
                                                <div class="produto-associar-container {{ !$item['produto_existente'] ? 'd-none' : '' }}" id="produto-associar-{{ $index }}">
                                                    <label class="form-label small text-muted">Buscar Produto (Nome ou Código)</label>
                                                    <select class="select2-produto form-select" name="itens[{{ $index }}][produto_id]" style="width: 100%;">
                                                        @if($item['produto_existente'])
                                                        <option value="{{ $item['produto_existente']->id }}" selected>
                                                            {{ $item['produto_existente']->nome }} - (Cód: {{ $item['produto_existente']->codigo_barras ?? $item['produto_existente']->id }})
                                                        </option>
                                                        @else
                                                        <option></option>
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- CONVERSÃO -->
                                    <td class="align-top">
                                        <div class="mb-2">
                                            <label class="small text-muted d-block">Qtd XML</label>
                                            <input type="number" class="form-control form-control-sm qtd-xml bg-light" value="{{ $item['qCom'] }}" readonly>
                                        </div>
                                        <div class="mb-2">
                                            <label class="small text-muted d-block" title="Quantos itens vêm na embalagem?">Fator Divisor</label>
                                            <input type="number" class="form-control form-control-sm fator-conversao border-primary" name="itens[{{ $index }}][fator]" value="1" min="0.01" step="0.01">
                                        </div>
                                        <div>
                                            <label class="small text-muted d-block fw-bold">Qtd Entrada</label>
                                            <input type="number" class="form-control form-control-sm qtd-sistema fw-bold" name="itens[{{ $index }}][quantidade]" readonly>
                                        </div>
                                    </td>

                                    <!-- CUSTOS -->
                                    <td class="align-top">
                                        <div class="mb-2">
                                            <label class="small text-muted d-block">Custo Atual (Sistema)</label>
                                            <input type="number" class="form-control form-control-sm ultimo-custo bg-light" value="{{ $item['ultimo_custo'] }}" readonly>
                                        </div>
                                        <div class="mb-2">
                                            <label class="small text-muted d-block">Custo Unit. XML</label>
                                            <input type="number" class="form-control form-control-sm custo-xml bg-light" value="{{ $item['vUnCom'] }}" readonly>
                                        </div>
                                        <div class="mb-2">
                                            <label class="small text-muted d-block fw-bold">Novo Custo Sistema</label>
                                            <input type="number" step="0.0001" class="form-control form-control-sm custo-sistema fw-bold border-success" name="itens[{{ $index }}][preco_custo]" readonly>
                                        </div>
                                    </td>

                                    <!-- PRECIFICAÇÃO -->
                                    <td class="align-top">
                                        <div class="mb-2">
                                            <label class="small text-muted d-block">Margem (%)</label>
                                            <input type="number" step="0.01" class="form-control form-control-sm margem-percent" name="itens[{{ $index }}][margem]" value="50.00">
                                        </div>
                                        <div>
                                            <label class="small text-muted d-block fw-bold">Preço Venda</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">R$</span>
                                                <input type="number" step="0.01" class="form-control form-control-sm preco-venda fw-bold border-primary" name="itens[{{ $index }}][preco_venda]">
                                            </div>
                                        </div>
                                    </td>

                                    <!-- ESTOQUE ATUAL REMOVIDO -->

                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="card-footer d-flex justify-content-end gap-2 bg-light">
                        <a href="{{ route('notas-entrada.index') }}" class="btn btn-label-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-check-double"></i> Confirmar Processamento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection