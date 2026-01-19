<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Detalhes da Cobrança #{{ $cobranca->id }}</title>
    <style>
        @page {
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

        .info-row {
            margin-bottom: 5px;
        }

        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 150px;
        }

        .footer {
            position: fixed;
            bottom: -3cm;
            left: 0;
            right: 0;
            height: 90px;
            border-top: 1px solid #ccc;
            padding-top: 10px;
            text-align: center;
            font-size: 10px;
            color: #777;
            background-color: #fff;
        }
    </style>
</head>
<body>
    <!-- Footer -->
    <div class="footer">
        <p style="margin-bottom: 5px; font-weight: bold;">
            {{ \App\Models\Configuracao::get('empresa_nome', 'JB Tech Soluções') }} -
            CNPJ: {{ formatarCpfCnpj(\App\Models\Configuracao::get('empresa_cnpj', '00.000.000/0001-00')) }}
        </p>
        <p style="margin-bottom: 5px;">
            {{ \App\Models\Configuracao::get('empresa_endereco') }}, {{ \App\Models\Configuracao::get('empresa_numero') }} -
            {{ \App\Models\Configuracao::get('empresa_bairro') }} -
            {{ \App\Models\Configuracao::get('empresa_cidade') }}/{{ \App\Models\Configuracao::get('empresa_uf') }}
        </p>
        <p style="margin-bottom: 5px;">
            Tel: {{ \App\Helpers\FormatacaoHelper::telefone(\App\Models\Configuracao::get('empresa_telefone')) }} -
            Email: {{ \App\Models\Configuracao::get('empresa_email') }}
        </p>
        <p style="margin-top: 10px; font-size: 9px;">Gerado em {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    </div>

    <!-- Header -->
    <div class="header-center">
        <img src="{{ public_path('assets/img/front-pages/landing-page/jblogo_black.png') }}" class="logo" alt="Logo">
    </div>

    <!-- Title -->
    <div class="document-title">
        <h1>Detalhes da Cobrança #{{ $cobranca->id }}</h1>
        <p>Emissão: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    </div>

    <!-- Details -->
    <div class="box">
        <span class="box-title">DADOS DA COBRANÇA</span>
        
        <div class="info-row">
            <span class="info-label">Venda Vinculada:</span>
            <span>#{{ $cobranca->venda->id }}</span>
        </div>

        <div class="info-row">
            <span class="info-label">Cliente:</span>
            <span>{{ $cobranca->venda->cliente->nome ?? 'N/A' }}</span>
        </div>

        <div class="info-row">
            <span class="info-label">Método de Pagamento:</span>
            <span>{{ ucfirst($cobranca->metodo_pagamento) }}</span>
        </div>

        <div class="info-row">
            <span class="info-label">Valor:</span>
            <span>R$ {{ number_format($cobranca->valor, 2, ',', '.') }}</span>
        </div>

        <div class="info-row">
            <span class="info-label">Status:</span>
            <span>{{ ucfirst($cobranca->status) }}</span>
        </div>

        <div class="info-row">
            <span class="info-label">Data de Vencimento:</span>
            <span>{{ $cobranca->data_vencimento ? \Carbon\Carbon::parse($cobranca->data_vencimento)->format('d/m/Y') : '-' }}</span>
        </div>
    </div>

    @if ($cobranca->codigo_pix || $cobranca->link_boleto || $cobranca->link_pagamento)
    <div class="box">
        <span class="box-title">DADOS PARA PAGAMENTO</span>
        
        @if ($cobranca->codigo_pix)
        <div class="info-row" style="margin-bottom: 10px;">
            <span class="info-label" style="display: block; margin-bottom: 5px;">Código PIX (Copia e Cola):</span>
            <div style="background: #f9f9f9; padding: 10px; border: 1px solid #eee; word-break: break-all; font-family: monospace;">
                {{ $cobranca->codigo_pix }}
            </div>
        </div>
        @endif

        @if ($cobranca->link_boleto)
        <div class="info-row">
            <span class="info-label">Link do Boleto:</span>
            <a href="{{ $cobranca->link_boleto }}" target="_blank">{{ $cobranca->link_boleto }}</a>
        </div>
        @endif

        @if ($cobranca->link_pagamento)
        <div class="info-row">
            <span class="info-label">Link de Pagamento:</span>
            <a href="{{ $cobranca->link_pagamento }}" target="_blank">{{ $cobranca->link_pagamento }}</a>
        </div>
        @endif
    </div>
    @endif

    @if ($cobranca->recorrente)
    <div class="box">
        <span class="box-title">INFORMAÇÕES DE RECORRÊNCIA</span>
        
        <div class="info-row">
            <span class="info-label">Frequência:</span>
            <span>{{ ucfirst($cobranca->frequencia_recorrencia) }}</span>
        </div>

        @if($cobranca->proxima_cobranca)
        <div class="info-row">
            <span class="info-label">Próxima Cobrança:</span>
            <span>{{ \Carbon\Carbon::parse($cobranca->proxima_cobranca)->format('d/m/Y') }}</span>
        </div>
        @endif
    </div>
    @endif

</body>
</html>
