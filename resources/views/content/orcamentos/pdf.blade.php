<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orçamento #{{ $orcamento->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .header, .footer {
            text-align: center;
            font-weight: bold;
        }
        .content {
            margin: 20px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Orçamento #{{ $orcamento->id }}</h1>
    </div>
    <div class="content">
        <p><strong>Cliente:</strong> {{ $orcamento->cliente->nome }}</p>
        <p><strong>Data:</strong> {{ $orcamento->data }}</p>
        <p><strong>Validade:</strong> {{ $orcamento->validade }}</p>

        <h3>Produtos</h3>
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
                @foreach($orcamento->produtos as $produto)
                <tr>
                    <td>{{ $produto->id }}</td>
                    <td>{{ $produto->nome }}</td>
                    <td>{{ $produto->pivot->quantidade }}</td>
                    <td>{{ number_format($produto->pivot->valor_unitario, 2, ',', '.') }}</td>
                    <td>{{ number_format($produto->pivot->quantidade * $produto->pivot->valor_unitario, 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <h3>Total: R$ {{ number_format($orcamento->produtos->sum(function($produto) {
            return $produto->pivot->quantidade * $produto->pivot->valor_unitario;
        }), 2, ',', '.') }}</h3>
    </div>
    <div class="footer">
        <p>Obrigado por fazer negócio conosco!</p>
    </div>
</body>
</html>
