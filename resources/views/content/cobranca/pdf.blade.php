<!DOCTYPE html>
<html>
<head>
    <title>Cobrança #{{ $cobranca->id }}</title>
</head>
<body>
    <h1>Cobrança #{{ $cobranca->id }}</h1>
    <p><strong>Venda:</strong> #{{ $cobranca->venda->id }}</p>
    <p><strong>Método:</strong> {{ ucfirst($cobranca->metodo_pagamento) }}</p>
    <p><strong>Valor:</strong> R$ {{ number_format($cobranca->valor, 2, ',', '.') }}</p>
    <p><strong>Status:</strong> {{ ucfirst($cobranca->status) }}</p>
    @if ($cobranca->codigo_pix)
        <p><strong>Código PIX:</strong> {{ $cobranca->codigo_pix }}</p>
    @endif
    @if ($cobranca->link_boleto)
        <p><strong>Link do Boleto:</strong> {{ $cobranca->link_boleto }}</p>
    @endif
    @if ($cobranca->link_pagamento)
        <p><strong>Link de Pagamento:</strong> {{ $cobranca->link_pagamento }}</p>
    @endif
    @if ($cobranca->recorrente)
        <p><strong>Recorrente:</strong> Sim ({{ $cobranca->frequencia_recorrencia }})</p>
        <p><strong>Próxima Cobrança:</strong> {{ $cobranca->proxima_cobranca->format('d/m/Y') }}</p>
    @endif
</body>
</html>
