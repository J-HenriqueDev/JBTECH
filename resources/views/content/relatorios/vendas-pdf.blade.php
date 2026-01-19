<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Vendas</title>
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

        .items-table {
            width: 100%;
            margin-bottom: 20px;
        }

        .items-table th, .items-table td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
            font-size: 11px;
        }

        .items-table th {
            background-color: #eee;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
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
        <p style="margin-top: 10px; font-size: 9px;">Relatório gerado em {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    </div>

    <!-- Header -->
    <div class="header-center">
        <img src="{{ public_path('assets/img/front-pages/landing-page/jblogo_black.png') }}" class="logo" alt="Logo">
    </div>

    <!-- Title -->
    <div class="document-title">
        <h1>Relatório de Vendas</h1>
        <p>Emissão: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    </div>

    <!-- Summary -->
    <div class="box">
        <span class="box-title">RESUMO</span>
        <table style="width: 100%;">
            <tr>
                <td><strong>Período:</strong> {{ request('data_inicio') ? \Carbon\Carbon::parse(request('data_inicio'))->format('d/m/Y') : 'Início' }} até {{ request('data_fim') ? \Carbon\Carbon::parse(request('data_fim'))->format('d/m/Y') : 'Fim' }}</td>
                <td><strong>Total de Vendas:</strong> {{ $quantidade }}</td>
                <td class="text-right"><strong>Valor Total:</strong> R$ {{ number_format($total, 2, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <!-- Items -->
    <table class="items-table">
        <thead>
            <tr>
                <th width="10%" class="text-center">ID</th>
                <th width="40%">Cliente</th>
                <th width="20%" class="text-center">Data</th>
                <th width="30%" class="text-right">Valor Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($vendas as $venda)
            <tr>
                <td class="text-center">#{{ $venda->id }}</td>
                <td>{{ $venda->cliente->nome ?? 'Cliente não identificado' }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($venda->created_at)->format('d/m/Y H:i') }}</td>
                <td class="text-right">R$ {{ number_format($venda->valor_total, 2, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center">Nenhuma venda encontrada no período selecionado.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
