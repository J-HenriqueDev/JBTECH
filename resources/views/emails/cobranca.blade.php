<!DOCTYPE html>
<html>
<head>
    <title>Cobrança Gerada</title>
</head>
<body>
    <h1>Cobrança Gerada</h1>
    <p>Olá, {{ $venda->cliente->nome }}!</p>
    <p>Segue em anexo o comprovante da venda #{{ $venda->id }}.</p>
    <p>Valor Total: R$ {{ number_format($venda->valor_total, 2, ',', '.') }}</p>
</body>
</html>
