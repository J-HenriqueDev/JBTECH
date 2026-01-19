@php
    $emit = $emitente;
    $dest = (object) ($nota->dados_destinatario ?? []);
    $produtos = $nota->produtos ?? [];
    $pagamento = $nota->dados_pagamento ?? [];
    $numero = $nota->numero_nfe ? str_pad($nota->numero_nfe, 9, '0', STR_PAD_LEFT) : '';
    $serie = $nota->serie ? str_pad($nota->serie, 3, '0', STR_PAD_LEFT) : '';
    $modelo = '55';
    $chave = $nota->chave_acesso ?? '';
    $chaveFormatada = trim(chunk_split($chave, 4, ' '));
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>DANFE - NF-e {{ $numero }}</title>
    <style>
        @page {
            margin: 15mm 8mm 15mm 8mm;
        }
        * {
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
        }
        body {
            margin: 0;
            padding: 0;
        }
        .danfe {
            width: 100%;
            border: 1px solid #000;
            padding: 4px;
        }
        .row {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }
        .col {
            display: table-cell;
            vertical-align: top;
        }
        .b {
            border: 1px solid #000;
        }
        .p2 {
            padding: 2px 3px;
        }
        .center {
            text-align: center;
        }
        .right {
            text-align: right;
        }
        .title {
            font-size: 9px;
            font-weight: bold;
        }
        .label {
            font-size: 7px;
        }
        .small {
            font-size: 8px;
        }
        .bold {
            font-weight: bold;
        }
        .mt2 {
            margin-top: 2px;
        }
        .prod-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2px;
        }
        .prod-table th,
        .prod-table td {
            border: 1px solid #000;
            padding: 2px 3px;
        }
        .prod-table th {
            font-size: 7px;
        }
        .prod-table td {
            font-size: 8px;
        }
        .footer {
            font-size: 7px;
            margin-top: 4px;
        }
        .footer-line {
            display: flex;
            justify-content: space-between;
        }
        .logo-jbtech {
            height: 12px;
        }
    </style>
</head>
<body>
<div class="danfe">
    <div class="row">
        <div class="col b p2" style="width: 24%;">
            <div class="label">RECEBEMOS DE</div>
            <div class="small">
                {{ $emit->xNome ?? '' }}<br>
                {{ $emit->xLgr ?? '' }}, {{ $emit->nro ?? '' }} - {{ $emit->xBairro ?? '' }}<br>
                {{ $emit->xMun ?? '' }} - {{ $emit->UF ?? '' }}
            </div>
            <div class="label mt2">DATA DE RECEBIMENTO</div>
            <div class="b" style="height: 14px;"></div>
            <div class="label mt2">IDENTIFICAÇÃO E ASSINATURA DO RECEBEDOR</div>
            <div class="b" style="height: 22px;"></div>
        </div>
        <div class="col b p2 center" style="width: 26%;">
            <div class="title">DANFE</div>
            <div class="small">DOCUMENTO AUXILIAR DA NOTA FISCAL ELETRÔNICA</div>
            <div class="mt2 small">0 - ENTRADA &nbsp;&nbsp;&nbsp; 1 - SAÍDA</div>
            <div class="title mt2">{{ $nota->tipo_documento == 0 ? '0 - ENTRADA' : '1 - SAÍDA' }}</div>
            <div class="mt2 small">Nº {{ $numero }}</div>
            <div class="small">SÉRIE {{ $serie }}&nbsp;&nbsp;&nbsp; FOLHA 1/1</div>
            <div class="mt2 label">CONSULTA DE AUTENTICIDADE NO PORTAL NACIONAL DA NF-e</div>
            <div class="label">WWW.NFE.FAZENDA.GOV.BR / CHAVE DE ACESSO</div>
            <div class="small mt2">{{ $chaveFormatada }}</div>
        </div>
        <div class="col b p2" style="width: 50%;">
            <div class="label">NATUREZA DA OPERAÇÃO</div>
            <div class="small bold">{{ $nota->natureza_operacao }}</div>
            <div class="row mt2">
                <div class="col">
                    <div class="label">INSCRIÇÃO ESTADUAL</div>
                    <div class="small">{{ $emit->IE ?? '' }}</div>
                </div>
                <div class="col">
                    <div class="label">INSCRIÇÃO ESTADUAL DO SUBST. TRIB.</div>
                    <div class="small">&nbsp;</div>
                </div>
                <div class="col">
                    <div class="label">CNPJ</div>
                    <div class="small">{{ $emit->CNPJ ?? '' }}</div>
                </div>
            </div>
            <div class="row mt2">
                <div class="col">
                    <div class="label">DATA DE EMISSÃO</div>
                    <div class="small">
                        {{ optional($nota->data_emissao)->format('d/m/Y') ?? now()->format('d/m/Y') }}
                    </div>
                </div>
                <div class="col">
                    <div class="label">DATA DE SAÍDA</div>
                    <div class="small">
                        {{ optional($nota->data_saida)->format('d/m/Y') ?? '' }}
                    </div>
                </div>
                <div class="col">
                    <div class="label">HORA DE SAÍDA</div>
                    <div class="small">
                        {{ optional($nota->data_saida)->format('H:i') ?? '' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt2">
        <div class="col b p2" style="width: 50%;">
            <div class="label">DESTINATÁRIO / REMETENTE</div>
            <div class="small bold">{{ $dest->xNome ?? '' }}</div>
            <div class="small">
                {{ $dest->xLgr ?? '' }}, {{ $dest->nro ?? '' }} {{ $dest->xCpl ?? '' }}<br>
                {{ $dest->xBairro ?? '' }} - {{ $dest->xMun ?? '' }} - {{ $dest->UF ?? '' }}<br>
                CEP: {{ $dest->CEP ?? '' }} &nbsp;&nbsp; Fone: {{ $dest->fone ?? '' }}
            </div>
        </div>
        <div class="col b p2" style="width: 25%;">
            <div class="label">CNPJ / CPF</div>
            <div class="small">{{ $dest->cpf_cnpj ?? '' }}</div>
            <div class="label mt2">INSCRIÇÃO ESTADUAL</div>
            <div class="small">{{ $dest->IE ?? '' }}</div>
        </div>
        <div class="col b p2" style="width: 25%;">
            <div class="label">DESTINO DA OPERAÇÃO</div>
            <div class="small">
                @if(($dest->UF ?? '') === ($emit->UF ?? ''))
                    1 - OPERAÇÃO INTERNA
                @else
                    2 - OPERAÇÃO INTERESTADUAL
                @endif
            </div>
            <div class="label mt2">CONSUMIDOR FINAL</div>
            <div class="small">1 - NÃO &nbsp;&nbsp; 2 - SIM</div>
        </div>
    </div>

    <table class="prod-table">
        <thead>
        <tr>
            <th style="width: 6%;">CÓD.</th>
            <th style="width: 34%;">DESCRIÇÃO DO PRODUTO / SERVIÇO</th>
            <th style="width: 5%;">NCM</th>
            <th style="width: 5%;">CFOP</th>
            <th style="width: 5%;">UNID.</th>
            <th style="width: 7%;">QTD.</th>
            <th style="width: 9%;">V.UNIT.</th>
            <th style="width: 9%;">V.TOTAL</th>
            <th style="width: 10%;">ALÍQ. ICMS</th>
            <th style="width: 10%;">VALOR ICMS</th>
        </tr>
        </thead>
        <tbody>
        @foreach($produtos as $item)
            @php
                $qtd = $item['qCom'] ?? $item['quantidade'] ?? 0;
                $vUn = $item['vUnCom'] ?? $item['preco_unitario'] ?? 0;
                $vTot = $item['vProd'] ?? ($qtd * $vUn);
            @endphp
            <tr>
                <td>{{ $item['cProd'] ?? '' }}</td>
                <td>{{ $item['xProd'] ?? '' }}</td>
                <td>{{ $item['NCM'] ?? '' }}</td>
                <td>{{ $item['CFOP'] ?? '' }}</td>
                <td>{{ $item['uCom'] ?? $item['unidade_comercial'] ?? '' }}</td>
                <td class="right">{{ number_format($qtd, 2, ',', '.') }}</td>
                <td class="right">{{ number_format($vUn, 2, ',', '.') }}</td>
                <td class="right">{{ number_format($vTot, 2, ',', '.') }}</td>
                <td class="right">{{ $item['aliq_icms'] ?? '' }}</td>
                <td class="right">{{ $item['vICMS'] ?? '' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="row mt2">
        <div class="col b p2" style="width: 60%;">
            <div class="label">DADOS ADICIONAIS</div>
            <div class="label">INFORMAÇÕES COMPLEMENTARES</div>
            <div class="small">
                {{ $nota->observacoes }}
            </div>
        </div>
        <div class="col b p2" style="width: 40%;">
            <div class="label">RESUMO DOS VALORES</div>
            <div class="small">
                <div>Valor dos Produtos: R$ {{ number_format($nota->valor_total ?? 0, 2, ',', '.') }}</div>
                <div>Valor do Frete: R$ 0,00</div>
                <div>Valor do Seguro: R$ 0,00</div>
                <div>Descontos: R$ 0,00</div>
                <div>Valor Total da Nota: R$ {{ number_format($nota->valor_total ?? 0, 2, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <div class="footer">
        <div class="footer-line">
            <span>Impresso em {{ now()->format('d/m/Y \\à\\s H:i:s') }}</span>
            <span>
                @if(!empty($logoRodape))
                    <img src="{{ $logoRodape }}" class="logo-jbtech" alt="JBTech">
                @endif
                Powered by JBTech
            </span>
        </div>
    </div>
</div>
</body>
</html>

