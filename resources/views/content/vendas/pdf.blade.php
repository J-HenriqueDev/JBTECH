<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Venda #{{ $venda->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fff;
            color: #333;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            position: relative;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            flex: 1;
            padding-bottom: 100px; /* Reduzido para aproximar o rodapé */
        }
        .header {
            text-align: center;
            margin-bottom: 15px; /* Reduzido */
        }
        .header img {
            max-width: 120px; /* Reduzido */
            margin-bottom: 5px; /* Reduzido */
        }
        .header h1 {
            font-size: 20px; /* Reduzido */
            color: #333;
            margin: 0;
        }
        .info-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px; /* Reduzido */
            margin-bottom: 15px; /* Reduzido */
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px; /* Reduzido */
        }
        .info-section p {
            margin: 3px 0; /* Reduzido */
            font-size: 13px; /* Reduzido */
        }
        h2 {
            font-size: 16px; /* Reduzido */
            margin-bottom: 10px; /* Reduzido */
            color: #007bff;
            border-bottom: 2px solid #007bff;
            padding-bottom: 3px; /* Reduzido */
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px; /* Reduzido */
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px; /* Reduzido */
            text-align: left;
            font-size: 13px; /* Reduzido */
        }
        table th {
            background-color: #f9f9f9;
            font-weight: bold;
        }
        .summary {
            text-align: right;
            margin-top: 15px; /* Reduzido */
        }
        .summary p {
            margin: 3px 0; /* Reduzido */
            font-size: 13px; /* Reduzido */
        }
        .summary .total {
            font-size: 14px; /* Reduzido */
            font-weight: bold;
            color: #007bff;
        }
        .observacao {
            margin-top: 15px; /* Reduzido */
            padding: 10px; /* Reduzido */
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 6px;
        }
        .observacao h2 {
            margin-top: 0;
        }
        .payment-methods {
            margin-top: 15px; /* Reduzido */
            font-size: 13px; /* Reduzido */
        }
        .payment-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px; /* Reduzido */
        }
        .payment-table th, .payment-table td {
            border: 1px solid #ddd;
            padding: 8px; /* Reduzido */
            text-align: left;
            font-size: 13px; /* Reduzido */
        }
        .payment-table th {
            background-color: #f9f9f9;
        }
        .footer {
            text-align: center;
            font-size: 11px; /* Reduzido */
            color: #666;
            padding: 8px 0; /* Reduzido */
            border-top: 1px solid #ddd;
            background-color: #fff;
            position: absolute;
            bottom: 10px; /* Reduzido */
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Cabeçalho -->
        <div class="header">
            <img src="https://jbtechresende.com.br/assets/img/front-pages/landing-page/jblogo_black.png" alt="JBTECH Logo">
            <h1>Relatório de Venda #{{ $venda->id }}</h1>
        </div>

        <!-- Informações do Cliente -->
        <div class="info-section">
            <p><strong>Cliente:</strong> {{ $venda->cliente->nome }}</p>
            <p><strong>CPF/CNPJ:</strong> {{ formatarCpfCnpj($venda->cliente->cpf_cnpj) }}</p>
            <p><strong>Data de Venda:</strong> {{ Carbon\Carbon::parse($venda->data)->translatedFormat('d \d\e F \d\e Y') }}</p>
            <p><strong>Forma de Pagamento:</strong> {{ $venda->forma_pagamento ?? 'Não informado' }}</p>
        </div>

        <!-- Produtos -->
        <h2>Produtos</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Quantidade</th>
                    <th>Valor Unitário</th>
                    <th>Valor Total</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $subtotalProdutos = 0; // Inicializa a variável
                @endphp
                @foreach ($venda->produtos as $produto)
                @php
                    $subtotalProdutos += $produto->pivot->valor_total; // Soma o valor total de cada produto
                @endphp
                <tr>
                    <td>{{ $produto->id }}</td>
                    <td>{{ $produto->nome }}</td>
                    <td>{{ $produto->pivot->quantidade }}</td>
                    <td>R$ {{ number_format($produto->pivot->valor_unitario, 2, ',', '.') }}</td>
                    <td><strong>R$ {{ number_format($produto->pivot->valor_total, 2, ',', '.') }}</strong></td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-end fw-bold">Total</td>
                    <td>R$ {{ number_format($subtotalProdutos, 2, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        <!-- Observação -->
        <div class="observacao">
            <h2>Observações</h2>
            <p>{{ $venda->observacoes ?? 'Nenhuma observação informada.' }}</p>
        </div>

        <!-- Formas de Pagamento -->
        <div class="payment-methods">
            <h2>Formas de Pagamento</h2>
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
                        $valorPix = $subtotalProdutos; // Valor total para Pix
                        $valor10x = $subtotalProdutos * (1 + 0.12436); // Valor total para Cartão (10x com taxa)
                    @endphp
                    <tr>
                        <td>Pix</td>
                        <td>À vista</td>
                        <td>0%</td>
                        <td>R$ {{ number_format($valorPix, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Cartão</td>
                        <td>10x</td>
                        <td>12,44%</td>
                        <td><strong>$ {{ number_format($valor10x, 2, ',', '.') }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Rodapé -->
    <div class="footer">
        <p>JBTECH Informática - Tecnologia ao Seu Alcance</p>
        <p>Rua Willy Faulstich, 252, Resende, RJ | CNPJ: 54.819.910/0001-20</p>
        <p>Telefone: +55 (24) 98113-2097 | E-mail: informatica.jbtech@gmail.com</p>
    </div>
</body>
</html>
