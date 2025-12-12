<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Vendas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: #fff;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
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
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
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
            padding: 12px;
            text-align: left;
            font-size: 14px;
        }
        table th {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .summary {
            text-align: right;
            margin-top: 20px;
            padding: 15px;
            background-color: #f0f0f0;
            border-radius: 5px;
        }
        .summary p {
            margin: 5px 0;
            font-size: 14px;
        }
        .summary .total {
            font-size: 18px;
            font-weight: bold;
            color: #007bff;
        }
        .actions {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 0 5px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .btn-danger {
            background-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #666;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        @media print {
            .actions {
                display: none;
            }
            body {
                background-color: white;
            }
            .container {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Cabeçalho -->
        <div class="header">
            <img src="https://jbtechresende.com.br/assets/img/front-pages/landing-page/jblogo_black.png" alt="JBTECH Logo">
            <h1>Relatório de Vendas</h1>
        </div>

        <!-- Informações do Relatório -->
        <div class="info-section">
            <p><strong>Data de Emissão:</strong> {{ Carbon\Carbon::now()->translatedFormat('d \d\e F \d\e Y') }}</p>
            <p><strong>Total de Vendas:</strong> {{ $quantidade ?? 0 }}</p>
            <p><strong>Período:</strong> {{ request('data_inicio') ? Carbon\Carbon::parse(request('data_inicio'))->format('d/m/Y') : 'Início' }} até {{ request('data_fim') ? Carbon\Carbon::parse(request('data_fim'))->format('d/m/Y') : 'Fim' }}</p>
            <p><strong>Valor Total:</strong> R$ {{ number_format($total ?? 0, 2, ',', '.') }}</p>
        </div>

        <!-- Vendas -->
        <h2>Vendas Realizadas</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Data</th>
                    <th>Produtos</th>
                    <th>Valor Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vendas as $venda)
                <tr>
                    <td>#{{ $venda->id }}</td>
                    <td>{{ $venda->cliente->nome ?? 'N/A' }}</td>
                    <td>{{ Carbon\Carbon::parse($venda->created_at)->format('d/m/Y H:i') }}</td>
                    <td>{{ $venda->produtos->count() }} item(ns)</td>
                    <td>R$ {{ number_format($venda->valor_total, 2, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">Nenhuma venda encontrada no período.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Resumo Financeiro -->
        <div class="summary">
            <p><strong>Total de Vendas:</strong> {{ $quantidade ?? 0 }}</p>
            <p class="total">Valor Total: R$ {{ number_format($total ?? 0, 2, ',', '.') }}</p>
        </div>

        <!-- Ações -->
        <div class="actions">
            <button onclick="window.print()" class="btn">🖨️ Imprimir</button>
            <a href="{{ route('relatorios.vendas', array_merge(request()->except('visualizar'), ['exportar' => 'pdf'])) }}" class="btn btn-danger">📥 Download PDF</a>
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



