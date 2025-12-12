<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Movimentações Semanais</title>
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
            padding-bottom: 100px;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
        }
        .header img {
            max-width: 120px;
            margin-bottom: 5px;
        }
        .header h1 {
            font-size: 20px;
            color: #333;
            margin: 0;
        }
        .info-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 15px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .info-section p {
            margin: 3px 0;
            font-size: 13px;
        }
        h2 {
            font-size: 16px;
            margin-bottom: 10px;
            color: #007bff;
            border-bottom: 2px solid #007bff;
            padding-bottom: 3px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 13px;
        }
        table th {
            background-color: #f9f9f9;
            font-weight: bold;
        }
        .summary {
            text-align: right;
            margin-top: 15px;
        }
        .summary p {
            margin: 3px 0;
            font-size: 13px;
        }
        .summary .total {
            font-size: 14px;
            font-weight: bold;
            color: #007bff;
        }
        .footer {
            text-align: center;
            font-size: 11px;
            color: #666;
            padding: 8px 0;
            border-top: 1px solid #ddd;
            background-color: #fff;
            position: absolute;
            bottom: 10px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Cabeçalho -->
        <div class="header">
            <img src="https://jbtechresende.com.br/assets/img/front-pages/landing-page/jblogo_black.png" alt="JBTECH Logo">
            <h1>Relatório de Movimentações Semanais</h1>
        </div>

        <!-- Informações do Relatório -->
        <div class="info-section">
            <p><strong>Data de Emissão:</strong> {{ Carbon\Carbon::now()->translatedFormat('d \d\e F \d\e Y') }}</p>
            <p><strong>Período:</strong> {{ $dataInicio->format('d/m/Y') }} até {{ $dataFim->format('d/m/Y') }}</p>
            <p><strong>Total de Itens:</strong> {{ number_format($totalItens, 0, ',', '.') }}</p>
            <p><strong>Valor Total:</strong> R$ {{ number_format($totalMovimentado, 2, ',', '.') }}</p>
        </div>

        <!-- Movimentações -->
        <h2>Movimentações por Semana</h2>
        <table>
            <thead>
                <tr>
                    <th>Semana</th>
                    <th>Produto</th>
                    <th>Categoria</th>
                    <th>Quantidade</th>
                    <th>Nº de Vendas</th>
                    <th>Valor Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movimentacoes as $mov)
                <tr>
                    <td>{{ $mov['semana'] }}</td>
                    <td>{{ $mov['produto']->nome }}</td>
                    <td>{{ $mov['produto']->categoria->nome ?? 'N/A' }}</td>
                    <td>{{ $mov['quantidade'] }}</td>
                    <td>{{ $mov['vendas'] }}</td>
                    <td>R$ {{ number_format($mov['valor_total'], 2, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">Nenhuma movimentação encontrada no período.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Resumo Financeiro -->
        <div class="summary">
            <p><strong>Total de Itens Movimentados:</strong> {{ number_format($totalItens, 0, ',', '.') }}</p>
            <p class="total">Valor Total Movimentado: R$ {{ number_format($totalMovimentado, 2, ',', '.') }}</p>
        </div>
    </div>

    <!-- Rodapé -->
    <div class="footer">
        <p>{{ $empresa['nome'] ?? 'JBTECH Informática' }} - Tecnologia ao Seu Alcance</p>
        <p>{{ $empresa['endereco'] ?? 'Rua Willy Faulstich' }}, {{ $empresa['numero'] ?? '252' }}, {{ $empresa['bairro'] ?? 'Centro' }}, {{ $empresa['cidade'] ?? 'Resende' }}, {{ $empresa['uf'] ?? 'RJ' }} | CNPJ: {{ $empresa['cnpj'] ?? '54.819.910/0001-20' }}</p>
        <p>Telefone: {{ $empresa['telefone'] ?? '+55 (24) 98113-2097' }} | E-mail: {{ $empresa['email'] ?? 'informatica.jbtech@gmail.com' }}</p>
    </div>
</body>
</html>
