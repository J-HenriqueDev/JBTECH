<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Orçamento #{{ str_pad($orcamento->id, 5, '0', STR_PAD_LEFT) }}</title>
    <style>
        @page {
            size: A4;
            margin: 1cm 1cm 3.5cm 1cm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.4;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-center {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo {
            max-width: 150px;
            max-height: 80px;
        }

        .company-info {
            text-align: right;
            font-size: 11px;
            color: #555;
            padding: 5px 0;
        }

        .company-name {
            font-size: 16px;
            font-weight: bold;
            color: #000;
            margin-bottom: 5px;
            display: block;
        }

        .document-title {
            text-align: center;
            margin: 10px 0;
            padding: 5px;
            background-color: #f5f5f5;
            border-top: 1px solid #ddd;
            border-bottom: 1px solid #ddd;
        }

        .document-title h1 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
        }

        .document-title p {
            margin: 5px 0 0;
            font-size: 12px;
            color: #666;
        }

        .box {
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 20px;
            background-color: #fff;
        }

        .box-title {
            font-weight: bold;
            font-size: 13px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
            margin-bottom: 10px;
            display: block;
        }

        .info-table {
            line-height: 1.2;
        }

        .info-table td {
            padding: 1px 0;
            vertical-align: top;
        }

        .items-table {
            margin-bottom: 20px;
        }

        .items-table th {
            background-color: #eee;
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }

        .items-table td {
            border: 1px solid #ccc;
            padding: 8px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .totals-table {
            width: 40%;
            float: right;
        }

        .totals-table td {
            padding: 5px;
            border-bottom: 1px solid #eee;
        }

        .total-final {
            font-weight: bold;
            font-size: 14px;
            background-color: #eee;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        .payment-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .payment-table th,
        .payment-table td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
            font-size: 11px;
        }

        .payment-table th {
            background-color: #eee;
            font-weight: bold;
        }
    </style>
</head>

<body>

    @include('layouts.pdf_footer')

    <!-- Header -->
    <div class="header-center">
        <img src="{{ public_path('assets/img/front-pages/landing-page/jblogo_black.png') }}" class="logo" alt="Logo">
    </div>

    <!-- Title -->
    <div class="document-title">
        <h1>Orçamento Nº {{ str_pad($orcamento->id, 5, '0', STR_PAD_LEFT) }}</h1>
        <p>Data de Emissão: {{ \Carbon\Carbon::parse($orcamento->data)->format('d/m/Y') }}</p>
    </div>

    <!-- Client Info -->
    <div class="box">
        <span class="box-title">DADOS DO CLIENTE</span>
        <table class="info-table">
            <tr>
                <td width="15%"><strong>Nome:</strong></td>
                <td width="45%">{{ $orcamento->cliente->nome }}</td>
                <td width="15%"><strong>CPF/CNPJ:</strong></td>
                <td width="25%">{{ formatarCpfCnpj($orcamento->cliente->cpf_cnpj) }}</td>
            </tr>
            <tr>
                <td><strong>Telefone:</strong></td>
                <td>{{ $orcamento->cliente->telefone ?? '-' }}</td>
                <td><strong>Data de Emissão:</strong></td>
                <td>{{ \Carbon\Carbon::parse($orcamento->data)->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td colspan="2"></td>
                <td><strong>Validade:</strong></td>
                <td>{{ \Carbon\Carbon::parse($orcamento->validade)->format('d/m/Y') }}</td>
            </tr>
            @if($orcamento->cliente->endereco)
            <tr>
                <td><strong>Endereço:</strong></td>
                <td colspan="3">
                    {{ $orcamento->cliente->endereco->endereco }}, {{ $orcamento->cliente->endereco->numero }}
                    {{ $orcamento->cliente->endereco->complemento ? ' - ' . $orcamento->cliente->endereco->complemento : '' }}
                    - {{ $orcamento->cliente->endereco->bairro }}, {{ $orcamento->cliente->endereco->cidade }}/{{ $orcamento->cliente->endereco->estado }}
                </td>
            </tr>
            @endif
        </table>
    </div>

    <!-- Items -->
    <table class="items-table">
        <thead>
            <tr>
                <th width="5%" class="text-center">#</th>
                <th width="50%">Descrição</th>
                <th width="10%" class="text-center">Qtd</th>
                <th width="15%" class="text-right">Vlr. Unit.</th>
                <th width="20%" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @php
            $subtotalProdutos = 0;
            $valorServico = 0;
            @endphp
            @foreach($orcamento->produtos as $index => $produto)
            @php
            $valorTotalProduto = $produto->pivot->quantidade * $produto->pivot->valor_unitario;
            // Lógica para separar serviços (Assumindo ID 1 ou categoria se houvesse)
            // Mantendo lógica anterior: ID 1 é serviço
            if ($produto->id == 1) {
            $valorServico += $valorTotalProduto;
            } else {
            $subtotalProdutos += $valorTotalProduto;
            }
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    <strong>{{ $produto->nome }}</strong>
                    @if($produto->descricao)
                    <br><small style="color: #666;">{{ $produto->descricao }}</small>
                    @endif
                </td>
                <td class="text-center">{{ $produto->pivot->quantidade }}</td>
                <td class="text-right">R$ {{ number_format($produto->pivot->valor_unitario, 2, ',', '.') }}</td>
                <td class="text-right">R$ {{ number_format($valorTotalProduto, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <div class="clearfix">
        <table class="totals-table">
            @if($subtotalProdutos > 0)
            <tr>
                <td class="text-right"><strong>Total Produtos:</strong></td>
                <td class="text-right">R$ {{ number_format($subtotalProdutos, 2, ',', '.') }}</td>
            </tr>
            @endif
            @if($valorServico > 0)
            <tr>
                <td class="text-right"><strong>Total Serviços:</strong></td>
                <td class="text-right">R$ {{ number_format($valorServico, 2, ',', '.') }}</td>
            </tr>
            @endif
            <tr class="total-final">
                <td class="text-right">TOTAL GERAL:</td>
                <td class="text-right">R$ {{ number_format($subtotalProdutos + $valorServico, 2, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <!-- Observations -->
    @if (!empty($orcamento->observacoes))
    <div class="box" style="margin-top: 20px; background-color: #fff;">
        <span class="box-title">OBSERVAÇÕES</span>
        <p style="margin: 0; font-size: 11px;">{{ $orcamento->observacoes }}</p>
    </div>
    @endif

    <!-- Payment Methods -->
    <div class="box" style="margin-top: 20px; background-color: #fff;">
        <span class="box-title">FORMAS DE PAGAMENTO</span>
        <table class="payment-table">
            <thead>
                <tr>
                    <th>Forma</th>
                    <th>Condição</th>
                    <th>Taxa</th>
                    <th>Valor Final</th>
                </tr>
            </thead>
            <tbody>
                @php
                $totalGeral = $subtotalProdutos + $valorServico;
                $formasPagamento = $orcamento->formas_pagamento ?? [];
                @endphp

                {{-- À Vista --}}
                @if(in_array('avista', $formasPagamento))
                <tr>
                    <td>Dinheiro / Pix</td>
                    <td>À vista</td>
                    <td>0%</td>
                    <td>R$ {{ number_format($totalGeral, 2, ',', '.') }}</td>
                </tr>
                @endif

                {{-- Boleto --}}
                @if(in_array('boleto', $formasPagamento))
                @php
                $qtdParcelas = $orcamento->parcelas_boleto ?? 1;
                $valorParcelaBoleto = $totalGeral / $qtdParcelas;
                $periodicidade = $orcamento->periodicidade_boleto ?? 'Mensal';
                @endphp
                <tr>
                    <td>Boleto Bancário</td>
                    <td>
                        {{ $qtdParcelas }}x de R$ {{ number_format($valorParcelaBoleto, 2, ',', '.') }}
                        <br>
                        <small style="color: #555;">(Vencimento: {{ $periodicidade }})</small>
                    </td>
                    <td>0%</td>
                    <td>R$ {{ number_format($totalGeral, 2, ',', '.') }}</td>
                </tr>
                @endif
            </tbody>
        </table>

        {{-- Cartão de Crédito (Grid Layout) --}}
        @if(in_array('cartao', $formasPagamento))
        @php
        $taxas = json_decode(\App\Models\Configuracao::get('vendas_taxa_cartao', '[]'), true);
        @endphp
        <div style="margin-top: 10px;">
            <div style="font-weight: bold; font-size: 11px; margin-bottom: 3px; border-bottom: 1px solid #eee; padding-bottom: 2px;">
                SIMULAÇÃO CARTÃO DE CRÉDITO
            </div>
            <table style="width: 100%; border-collapse: collapse;">
                @for($row = 0; $row < 3; $row++)
                    <tr>
                    @for($col = 1; $col <= 4; $col++)
                        @php
                        $i=($row * 4) + $col;
                        $taxa=0;
                        foreach($taxas as $t) {
                        if(isset($t['parcelas']) && $t['parcelas']==$i) {
                        $taxa=floatval(str_replace(',', '.' , $t['taxa']));
                        break;
                        }
                        }
                        $valorComTaxa=$totalGeral * (1 + ($taxa / 100));
                        $valorParcela=$valorComTaxa / $i;
                        @endphp
                        <td style="width: 25%; border: 1px solid #ddd; padding: 5px; font-size: 11px; background-color: #fafafa;">
                        <div style="font-weight: bold; color: #333;">{{ $i }}x de R$ {{ number_format($valorParcela, 2, ',', '.') }}</div>
                        <div style="color: #666; font-size: 10px;">
                            Total: R$ {{ number_format($valorComTaxa, 2, ',', '.') }}
                            @if($taxa > 0)
                            <span style="color: #888;">({{ number_format($taxa, 2, ',', '.') }}%)</span>
                            @else
                            <span style="color: #28a745;">(S/ Juros)</span>
                            @endif
                        </div>
                        </td>
                        @endfor
                        </tr>
                        @endfor
            </table>
        </div>
        @endif
    </div>
</body>

</html>