@extends('layouts/contentNavbarLayout')

@section('title', 'Editar Contrato')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('content')
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Contratos /</span> Editar
    </h4>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <h5 class="card-header">Detalhes do Contrato #{{ $contrato->id }}</h5>
                <div class="card-body">
                    <form action="{{ route('contratos.update', $contrato->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="cliente_id" class="form-label">Cliente</label>
                                <select class="form-select select2" id="cliente_id" name="cliente_id" required>
                                    <option value="">Selecione...</option>
                                    @foreach ($clientes as $cliente)
                                        <option value="{{ $cliente->id }}"
                                            {{ (old('cliente_id') ?? $contrato->cliente_id) == $cliente->id ? 'selected' : '' }}>
                                            #{{ $cliente->id }} - {{ $cliente->nome }} ({{ $cliente->cpf_cnpj }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="servico_id" class="form-label">Tipo de Serviço (Modelo Fiscal)</label>
                                <select class="form-select select2" id="servico_id" name="servico_id">
                                    <option value="">Personalizado / Nenhum</option>
                                    @foreach ($servicos as $servico)
                                        <option value="{{ $servico->id }}" data-codigo="{{ $servico->codigo_servico }}"
                                            data-nbs="{{ $servico->codigo_nbs }}"
                                            data-aliquota="{{ $servico->aliquota_iss }}"
                                            data-retido="{{ $servico->iss_retido }}"
                                            data-discriminacao="{{ $servico->discriminacao_padrao }}"
                                            {{ (old('servico_id') ?? $contrato->servico_id) == $servico->id ? 'selected' : '' }}>
                                            {{ $servico->nome }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="descricao" class="form-label">Descrição</label>
                                <input type="text" class="form-control" id="descricao" name="descricao"
                                    value="{{ old('descricao', $contrato->descricao) }}" required>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label d-block">Tipo de Contrato</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="tipo" id="tipo_recorrente"
                                        value="recorrente"
                                        {{ old('tipo', $contrato->tipo ?? 'recorrente') == 'recorrente' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="tipo_recorrente">Recorrente (Mensalidade /
                                        Assinatura)</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="tipo" id="tipo_parcelado"
                                        value="parcelado"
                                        {{ old('tipo', $contrato->tipo) == 'parcelado' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="tipo_parcelado">Projeto Único (Valor
                                        Fechado)</label>
                                </div>
                                <small class="form-text text-muted d-block mt-1">
                                    Use <b>Recorrente</b> para pagamentos contínuos (ex: manutenção mensal). <br>
                                    Use <b>Projeto Único</b> para serviços com preço fixo parcelado (ex: desenvolvimento de
                                    site em 3x).
                                </small>
                            </div>

                            <div id="parcelado-fields" class="row" style="display: none;">
                                <div class="col-md-6 mb-3">
                                    <label for="valor_total" class="form-label">Valor Total do Projeto (R$)</label>
                                    <input type="text" class="form-control money" id="valor_total" name="valor_total"
                                        value="{{ old('valor_total', $contrato->valor_total) }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="qtd_parcelas" class="form-label">Qtd. Parcelas</label>
                                    <input type="number" class="form-control" id="qtd_parcelas" name="qtd_parcelas"
                                        value="{{ old('qtd_parcelas', $contrato->qtd_parcelas) }}" min="1">
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="valor" class="form-label" id="label-valor">Valor Total Mensal (R$)</label>
                                <input type="text" class="form-control money" id="valor" name="valor"
                                    value="{{ old('valor', $contrato->valor) }}" required>
                                <div id="valor-parcela-hint" class="form-text text-primary fw-bold mt-1"
                                    style="display:none;"></div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="frequencia" class="form-label">Frequência</label>
                                <select class="form-select select2" id="frequencia" name="frequencia" required>
                                    <option value="mensal"
                                        {{ (old('frequencia') ?? $contrato->frequencia) == 'mensal' ? 'selected' : '' }}>
                                        Mensal</option>
                                    <option value="trimestral"
                                        {{ (old('frequencia') ?? $contrato->frequencia) == 'trimestral' ? 'selected' : '' }}>
                                        Trimestral</option>
                                    <option value="semestral"
                                        {{ (old('frequencia') ?? $contrato->frequencia) == 'semestral' ? 'selected' : '' }}>
                                        Semestral</option>
                                    <option value="anual"
                                        {{ (old('frequencia') ?? $contrato->frequencia) == 'anual' ? 'selected' : '' }}>
                                        Anual</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="forma_pagamento" class="form-label">Forma de Pagamento</label>
                                <select class="form-select select2" id="forma_pagamento" name="forma_pagamento" required>
                                    <option value="boleto_pix"
                                        {{ old('forma_pagamento', $contrato->forma_pagamento ?? 'boleto_pix') == 'boleto_pix' ? 'selected' : '' }}>
                                        Boleto + PIX (Padrão)</option>
                                    <option value="boleto"
                                        {{ old('forma_pagamento', $contrato->forma_pagamento ?? '') == 'boleto' ? 'selected' : '' }}>
                                        Apenas Boleto</option>
                                    <option value="pix"
                                        {{ old('forma_pagamento', $contrato->forma_pagamento ?? '') == 'pix' ? 'selected' : '' }}>
                                        Apenas PIX</option>
                                    <option value="transferencia"
                                        {{ old('forma_pagamento', $contrato->forma_pagamento ?? '') == 'transferencia' ? 'selected' : '' }}>
                                        Transferência Bancária</option>
                                    <option value="cartao"
                                        {{ old('forma_pagamento', $contrato->forma_pagamento ?? '') == 'cartao' ? 'selected' : '' }}>
                                        Cartão de Crédito</option>
                                    <option value="outro"
                                        {{ old('forma_pagamento', $contrato->forma_pagamento ?? '') == 'outro' ? 'selected' : '' }}>
                                        Outro</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="dia_vencimento" class="form-label">Dia de Vencimento</label>
                                <input type="number" class="form-control" id="dia_vencimento" name="dia_vencimento"
                                    min="1" max="31"
                                    value="{{ old('dia_vencimento', $contrato->dia_vencimento) }}" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="dias_personalizados" class="form-label">Dias Personalizados
                                    (Múltiplos)</label>
                                <input type="text" class="form-control" id="dias_personalizados"
                                    name="dias_personalizados"
                                    value="{{ old('dias_personalizados', $contrato->dias_personalizados) }}"
                                    placeholder="Ex: 5, 20 (Quinzenal)">
                                <small class="text-muted">Se preenchido, gera cobranças nesses dias específicos. Ajuste o
                                    Valor para o valor da parcela.</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="data_inicio" class="form-label">Data de Início</label>
                                <input type="date" class="form-control" id="data_inicio" name="data_inicio"
                                    value="{{ old('data_inicio', $contrato->data_inicio->format('Y-m-d')) }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="data_fim" class="form-label">Data de Fim (Opcional)</label>
                                <input type="date" class="form-control" id="data_fim" name="data_fim"
                                    value="{{ old('data_fim', $contrato->data_fim?->format('Y-m-d')) }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="ativo" name="ativo"
                                        value="1" {{ old('ativo') ?? $contrato->ativo ? 'checked' : '' }}>
                                    <label class="form-check-label" for="ativo">Contrato Ativo</label>
                                </div>
                            </div>

                            <div class="divider text-start">
                                <div class="divider-text">Dados Fiscais (NFS-e Automática)</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="codigo_servico" class="form-label">Código de Tributação Nacional (LC 116)
                                    *</label>
                                <input type="text" class="form-control" id="codigo_servico" name="codigo_servico"
                                    value="{{ old('codigo_servico', $contrato->codigo_servico) }}"
                                    placeholder="Ex: 01.07.01">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="codigo_nbs" class="form-label">Item da NBS correspondente ao serviço prestado
                                    *</label>
                                <input type="text" class="form-control" id="codigo_nbs" name="codigo_nbs"
                                    value="{{ old('codigo_nbs', $contrato->codigo_nbs ?? '') }}"
                                    placeholder="Ex: 112013100">
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="discriminacao_servico" class="form-label">Descrição do Serviço (NFS-e)
                                    *</label>
                                <textarea class="form-control" id="discriminacao_servico" name="discriminacao_servico" rows="3">{{ old('discriminacao_servico', $contrato->discriminacao_servico) }}</textarea>
                            </div>

                            <div class="col-md-12">
                                <a class="btn btn-link px-0" data-bs-toggle="collapse" href="#advancedFields"
                                    role="button" aria-expanded="false" aria-controls="advancedFields">
                                    Exibir Configurações Fiscais Avançadas
                                </a>
                            </div>

                            <div class="collapse mt-3" id="advancedFields">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label for="aliquota_iss" class="form-label">Alíquota ISS (%)</label>
                                        <input type="number" step="0.01" class="form-control" id="aliquota_iss"
                                            name="aliquota_iss"
                                            value="{{ old('aliquota_iss', $contrato->aliquota_iss) }}">
                                    </div>

                                    <div class="col-md-3 mb-3 d-flex align-items-end">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="iss_retido"
                                                name="iss_retido" value="1"
                                                {{ old('iss_retido') ?? $contrato->iss_retido ? 'checked' : '' }}>
                                            <label class="form-check-label" for="iss_retido">ISS Retido?</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 mb-3 mt-3">
                                <label for="observacoes" class="form-label">Observações Internas</label>
                                <textarea class="form-control" id="observacoes" name="observacoes" rows="2">{{ old('observacoes', $contrato->observacoes) }}</textarea>
                            </div>
                        </div>

                        <div class="mt-2">
                            <button type="submit" class="btn btn-primary me-2">Salvar Alterações</button>
                            <a href="{{ route('contratos.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('page-script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function loadScript(url, callback) {
                var script = document.createElement('script');
                script.type = 'text/javascript';
                script.src = url;
                script.onload = callback;
                document.body.appendChild(script);
            }

            function initContratoScripts() {
                if (typeof $ === 'undefined' || typeof $.fn.select2 === 'undefined') {
                    setTimeout(initContratoScripts, 100);
                    return;
                }

                if (typeof $.fn.mask === 'undefined') {
                    loadScript('https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js',
                        function() {
                            initContratoScripts();
                        });
                    return;
                }

                $('.select2').select2();
                $('.money').mask('#.##0,00', {
                    reverse: true
                });

                $('#servico_id').on('select2:select', function(e) {
                    var data = e.params.data.element.dataset;

                    if (data.codigo) $('#codigo_servico').val(data.codigo);
                    if (data.nbs) $('#codigo_nbs').val(data.nbs);
                    if (data.aliquota) $('#aliquota_iss').val(data.aliquota);

                    if (data.retido == '1') {
                        $('#iss_retido').prop('checked', true);
                    } else {
                        $('#iss_retido').prop('checked', false);
                    }

                    if (data.discriminacao) $('#discriminacao_servico').val(data.discriminacao);
                });

                // Lógica de Parcelamento
                function toggleParcelado() {
                    if ($('#tipo_parcelado').is(':checked')) {
                        $('#parcelado-fields').slideDown();
                        $('#valor').prop('readonly', true);
                        $('#label-valor').text('Valor da Parcela (R$)');
                        $('#valor-parcela-hint').hide();
                    } else {
                        $('#parcelado-fields').slideUp();
                        $('#valor').prop('readonly', false);
                        $('#label-valor').text('Valor Total Mensal (R$)');
                        calcSplitMensal();
                    }
                }

                function calcSplitMensal() {
                    if ($('#tipo_parcelado').is(':checked')) {
                        $('#valor-parcela-hint').hide();
                        return;
                    }

                    var diasStr = $('#dias_personalizados').val();
                    if (!diasStr) {
                        $('#valor-parcela-hint').hide();
                        return;
                    }

                    var dias = diasStr.split(',').filter(d => d.trim().length > 0);
                    if (dias.length <= 1) {
                        $('#valor-parcela-hint').hide();
                        return;
                    }

                    var valorStr = $('#valor').val() || '0';
                    var valor = parseFloat(valorStr.replace(/\./g, '').replace(',', '.'));

                    if (valor > 0) {
                        var parcela = valor / dias.length;
                        var parcelaFmt = parcela.toLocaleString('pt-BR', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                        $('#valor-parcela-hint').text(
                            `Serão geradas ${dias.length} cobranças de R$ ${parcelaFmt} cada no mês.`).show();
                    } else {
                        $('#valor-parcela-hint').hide();
                    }
                }

                $('input[name="tipo"]').on('change', toggleParcelado);
                $('#dias_personalizados, #valor').on('keyup change', calcSplitMensal);
                toggleParcelado();

                function calcParcela() {
                    if (!$('#tipo_parcelado').is(':checked')) return;

                    var totalStr = $('#valor_total').val() || '0';
                    // Remove pontos de milhar e troca vírgula por ponto
                    var total = parseFloat(totalStr.replace(/\./g, '').replace(',', '.'));
                    var qtd = parseInt($('#qtd_parcelas').val()) || 0;

                    if (total > 0 && qtd > 0) {
                        var parcela = total / qtd;
                        // Formata para pt-BR
                        var parcelaFmt = parcela.toLocaleString('pt-BR', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                        $('#valor').val(parcelaFmt).trigger('input');
                    }
                }
                $('#valor_total, #qtd_parcelas').on('keyup change', calcParcela);
            }
            initContratoScripts();
        });
    </script>
@endsection
