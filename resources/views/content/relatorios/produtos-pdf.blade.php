<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Relatório de Produtos</title>
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
        <h1>Relatório de Produtos</h1>
        <p>Emissão: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    </div>

    <!-- Summary -->
    <div class="box">
        <span class="box-title">RESUMO</span>
        <table style="width: 100%;">
            <tr>
                <td><strong>Total de Produtos:</strong> {{ $produtos->count() }}</td>
                <td class="text-right"><strong>Valor Total em Estoque:</strong> R$ {{ number_format($valorTotalEstoque, 2, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <!-- Items -->
    <table class="items-table">
        <thead>
            <tr>
                <th width="10%" class="text-center">ID</th>
                <th width="40%">Nome</th>
                <th width="20%">Categoria</th>
                <th width="15%" class="text-center">Estoque</th>
                <th width="15%" class="text-right">Preço Venda</th>
            </tr>
        </thead>
        <tbody>
            @forelse($produtos as $produto)
            <tr>
                <td class="text-center">#{{ $produto->id }}</td>
                <td>{{ $produto->nome }}</td>
                <td>{{ $produto->categoria->nome ?? 'N/A' }}</td>
                <td class="text-center">{{ $produto->estoque ?? 0 }}</td>
                <td class="text-right">R$ {{ number_format($produto->preco_venda, 2, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">Nenhum produto encontrado.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>