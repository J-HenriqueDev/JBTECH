<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Relatório Financeiro</title>
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

        .items-table th,
        .items-table td {
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
    @include('layouts.pdf_footer')

    <!-- Header -->
    <div class="header-center">
        <img src="{{ public_path('assets/img/front-pages/landing-page/jblogo_black.png') }}" class="logo" alt="Logo">
    </div>

    <!-- Title -->
    <div class="document-title">
        <h1>Relatório Financeiro</h1>
        <p>Emissão: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    </div>

    <!-- Summary -->
    <div class="box">
        <span class="box-title">RESUMO</span>
        <table style="width: 100%;">
            <tr>
                <td><strong>Período:</strong> {{ request('data_inicio') ? \Carbon\Carbon::parse(request('data_inicio'))->format('d/m/Y') : 'Início' }} até {{ request('data_fim') ? \Carbon\Carbon::parse(request('data_fim'))->format('d/m/Y') : 'Fim' }}</td>
            </tr>
            <tr>
                <td><strong>Total Pendente:</strong> R$ {{ number_format($totalPendente, 2, ',', '.') }}</td>
                <td><strong>Total Pago:</strong> R$ {{ number_format($totalPago, 2, ',', '.') }}</td>
                <td class="text-right"><strong>Total Cancelado:</strong> R$ {{ number_format($totalCancelado, 2, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <!-- Items -->
    <table class="items-table">
        <thead>
            <tr>
                <th width="5%" class="text-center">ID</th>
                <th width="30%">Cliente</th>
                <th width="15%">Método</th>
                <th width="15%" class="text-right">Valor</th>
                <th width="15%" class="text-center">Status</th>
                <th width="10%" class="text-center">Vencimento</th>
                <th width="10%" class="text-center">Data</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cobrancas as $cobranca)
            <tr>
                <td class="text-center">#{{ $cobranca->id }}</td>
                <td>{{ $cobranca->venda->cliente->nome ?? 'N/A' }}</td>
                <td>{{ ucfirst($cobranca->metodo_pagamento) }}</td>
                <td class="text-right">R$ {{ number_format($cobranca->valor, 2, ',', '.') }}</td>
                <td class="text-center">{{ ucfirst($cobranca->status) }}</td>
                <td class="text-center">{{ $cobranca->data_vencimento ? \Carbon\Carbon::parse($cobranca->data_vencimento)->format('d/m/Y') : '-' }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($cobranca->created_at)->format('d/m/Y') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Nenhuma cobrança encontrada no período.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>