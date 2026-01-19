<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Ordem de Serviço #{{ str_pad($os->id, 5, '0', STR_PAD_LEFT) }}</title>
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

        .info-table td {
            padding: 5px;
            vertical-align: top;
        }

        .label {
            font-weight: bold;
            width: 140px;
            display: inline-block;
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

        .signatures {
            margin-top: 50px;
            width: 100%;
        }

        .signature-line {
            border-top: 1px solid #000;
            width: 80%;
            margin: 0 auto;
            margin-bottom: 5px;
        }

        .signature-block {
            text-align: center;
            width: 45%;
            display: inline-block;
            vertical-align: top;
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
        <p style="margin-top: 10px; font-size: 9px;">Ordem de Serviço gerada em {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    </div>

    <!-- Header -->
    <div class="header-center">
        <img src="{{ public_path('assets/img/front-pages/landing-page/jblogo_black.png') }}" class="logo" alt="Logo">
    </div>

    <!-- Title -->
    <div class="document-title">
        <h1>Ordem de Serviço Nº {{ str_pad($os->id, 5, '0', STR_PAD_LEFT) }}</h1>
        <p>Data de Entrada: {{ \Carbon\Carbon::parse($os->data_de_entrada)->format('d/m/Y') }}</p>
    </div>

    <!-- Client Info -->
    <div class="box">
        <span class="box-title">DADOS DO CLIENTE</span>
        <table class="info-table">
            <tr>
                <td><span class="label">Nome:</span> {{ $os->cliente->nome ?? 'Cliente Não Identificado' }}</td>
                <td><span class="label">Telefone:</span> {{ \App\Helpers\FormatacaoHelper::telefone($os->cliente->telefone ?? '') }}</td>
            </tr>
            <tr>
                <td colspan="2"><span class="label">Endereço:</span> {{ $os->cliente->endereco->rua ?? '' }}, {{ $os->cliente->endereco->numero ?? '' }} - {{ $os->cliente->endereco->bairro ?? '' }}</td>
            </tr>
            <tr>
                <td colspan="2"><span class="label">Cidade/UF:</span> {{ $os->cliente->endereco->cidade ?? '' }}/{{ $os->cliente->endereco->estado ?? '' }}</td>
            </tr>
        </table>
    </div>

    <!-- Equipment Info -->
    <div class="box">
        <span class="box-title">DADOS DO EQUIPAMENTO</span>
        <table class="info-table">
            <tr>
                <td><span class="label">Tipo:</span> {{ \App\Models\OS::TIPOS_DE_EQUIPAMENTO[$os->tipo_id] ?? $os->tipo_id }}</td>
                <td><span class="label">Marca/Modelo:</span> {{ $os->modelo_do_dispositivo ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><span class="label">Nº de Série:</span> {{ $os->sn ?? 'N/A' }}</td>
                <td><span class="label">Senha:</span> {{ $os->senha_do_dispositivo ?? 'Não informada' }}</td>
            </tr>
            <tr>
                <td colspan="2"><span class="label">Acessórios:</span> {{ $os->acessorios ?? 'Nenhum' }}</td>
            </tr>
        </table>
    </div>

    <!-- Problem Description -->
    <div class="box">
        <span class="box-title">DESCRIÇÃO DO PROBLEMA / DEFEITO RELATADO</span>
        <div style="padding: 5px; min-height: 50px;">
            {!! nl2br(e($os->problema_item)) !!}
        </div>
    </div>

    @if($os->avarias)
    <!-- Damage Report -->
    <div class="box">
        <span class="box-title">AVARIAS EXISTENTES</span>
        <div style="padding: 5px;">
            {!! nl2br(e($os->avarias)) !!}
        </div>
    </div>
    @endif

    <!-- Technical Info -->
    <div class="box">
        <span class="box-title">INFORMAÇÕES TÉCNICAS</span>
        <table class="info-table">
            <tr>
                <td><span class="label">Status Atual:</span> {{ \App\Models\OS::STATUS[$os->status] ?? ucfirst($os->status) }}</td>
                <td><span class="label">Prazo de Entrega:</span> {{ $os->prazo_entrega ? \Carbon\Carbon::parse($os->prazo_entrega)->format('d/m/Y') : 'A definir' }}</td>
            </tr>
            <tr>
                <td><span class="label">Técnico Responsável:</span> {{ $os->usuario->name ?? 'N/A' }}</td>
                <td><span class="label">Valor Estimado:</span> R$ {{ number_format($os->valor_servico ?? 0, 2, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    @if($os->observacoes)
    <!-- Observations -->
    <div class="box">
        <span class="box-title">OBSERVAÇÕES INTERNAS</span>
        <div style="padding: 5px;">
            {!! nl2br(e($os->observacoes)) !!}
        </div>
    </div>
    @endif

    <!-- Signatures -->
    <div class="signatures">
        <div class="signature-block">
            <div class="signature-line"></div>
            <strong>{{ \App\Models\Configuracao::get('empresa_nome', 'JB Tech Soluções') }}</strong><br>
            Técnico Responsável
        </div>
        <div class="signature-block" style="float: right;">
            <div class="signature-line"></div>
            <strong>{{ $os->cliente->nome ?? 'Cliente' }}</strong><br>
            Assinatura do Cliente
        </div>
    </div>

    <div style="clear: both; margin-top: 20px; font-size: 10px; text-align: center; color: #666;">
        <p>Declaro que recebi o equipamento acima descrito nas condições informadas.</p>
    </div>
</body>
</html>