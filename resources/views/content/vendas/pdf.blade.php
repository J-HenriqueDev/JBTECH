<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Venda #{{ $venda->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .logo { width: 150px; margin-bottom: 20px; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { border: 1px solid #000; padding: 8px; text-align: left; }
        .table th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <img src="data:image/png;base64,{{ $logoBase64 }}" alt="Logo" class="logo">
    <h1>Relatório de Venda #{{ $venda->id }}</h1>
    <p><strong>Cliente:</strong> {{ $venda->cliente->nome }}</p>
    <p><strong>Data da Venda:</strong> {{ $venda->data_venda->format('d/m/Y') }}</p>
    <p><strong>Observações:</strong> {{ $venda->observacoes }}</p>

    <h2>Produtos</h2>
    <table class="table">
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
</body>
</html>
