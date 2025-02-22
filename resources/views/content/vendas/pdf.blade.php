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
            min-height: 100vh; /* Garante que o body ocupe pelo menos a altura da tela */
            position: relative; /* Permite posicionar o footer de forma absoluta */
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px 30px;
            border-radius: 6px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            background-color: #fff;
            flex: 1; /* Faz o container crescer para ocupar o espaço disponível */
            padding-bottom: 150px; /* Aumentei o espaço para o footer */
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header img {
            max-width: 150px;
            margin-bottom: 10px;
        }
        .header h1 {
            font-size: 24px;
            color: #333;
            margin: 0;
        }
        .info-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 15px;
        }
        .info-section p {
            margin: 5px 0;
            font-size: 14px;
        }
        h2 {
            font-size: 18px;
            margin-bottom: 15px;
            color: #007bff;
            border-bottom: 2px solid #007bff;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
            font-size: 14px;
        }
        table th {
            background-color: #f9f9f9;
            font-weight: bold;
        }
        .summary {
            text-align: right;
            margin-top: 20px;
        }
        .summary p {
            margin: 5px 0;
            font-size: 14px;
        }
        .summary .total {
            font-size: 16px;
            font-weight: bold;
            color: #007bff;
        }
        .observacao {
            margin-top: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 6px;
        }
        .observacao h2 {
            margin-top: 0;
        }
        .forma-pagamento {
            margin-top: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 6px;
        }
        .forma-pagamento h2 {
            margin-top: 0;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #666;
            padding: 10px 0;
            border-top: 1px solid #ddd;
            background-color: #fff;
            position: absolute; /* Fixa o footer no rodapé */
            bottom: 20px; /* Aumentei a distância do rodapé */
            width: 100%; /* Ocupa toda a largura */
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
                @foreach ($venda->produtos as $produto)
                <tr>
                    <td>{{ $produto->id }}</td>
                    <td>{{ $produto->nome }}</td>
                    <td>{{ $produto->pivot->quantidade }}</td>
                    <td>R$ {{ number_format($produto->pivot->valor_unitario, 2, ',', '.') }}</td>
                    <td>R$ {{ number_format($produto->pivot->valor_total, 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-end fw-bold">Total</td>
                    <td>R$ {{ number_format($venda->produtos->sum('pivot.valor_total'), 2, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        <!-- Observação -->
        <div class="observacao">
            <h2>Observações</h2>
            <p>{{ $venda->observacoes ?? 'Nenhuma observação informada.' }}</p>
        </div>

        <!-- Forma de Pagamento -->
        <div class="forma-pagamento">
            <h2>Forma de Pagamento</h2>
            <p>{{ $venda->forma_pagamento ?? 'Não informado' }}</p>
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
