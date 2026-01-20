<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Relatório de Estoque</title>
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

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
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
        <h1>Relatório de Estoque</h1>
        <p>Emissão: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    </div>

    <!-- Summary Box -->
    <div class="box">
        <span class="box-title">RESUMO</span>
        <table style="width: 100%;">
            <tr>
                <td><strong>Total de Produtos:</strong> {{ $produtos->count() }}</td>
                <td><strong>Estoque Baixo (≤ 10):</strong> {{ $produtosBaixo->count() }}</td>
            </tr>
            <tr>
                <td><strong>Estoque Médio (11-50):</strong> {{ $produtosMedio->count() }}</td>
                <td><strong>Estoque Alto (> 50):</strong> {{ $produtosAlto->count() }}</td>
            </tr>
        </table>
    </div>

    <!-- Products Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th width="5%" class="text-center">ID</th>
                <th width="35%">Nome</th>
                <th width="20%">Categoria</th>
                <th width="15%" class="text-center">Estoque</th>
                <th width="25%" class="text-right">Preço Venda</th>
            </tr>
        </thead>
        <tbody>
            @forelse($produtos as $produto)
            <tr>
                <td class="text-center">{{ $produto->id }}</td>
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