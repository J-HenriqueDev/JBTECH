<!DOCTYPE html>
<html>

<head>
    <title>Sua Fatura Chegou</title>
</head>

<body style="font-family: Arial, sans-serif; color: #333;">
    <h2>Olá, {{ $cliente->nome }}!</h2>

    <p>Sua fatura referente ao contrato de prestação de serviços já está disponível.</p>

    <div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
        <p><strong>Vencimento:</strong> {{ $data_vencimento }}</p>
        <p><strong>Valor Total:</strong> R$ {{ $valor }}</p>
    </div>

    @if ($linha_digitavel)
        <p>Utilize o código abaixo para pagamento:</p>
        <div
            style="background-color: #e9ecef; padding: 10px; font-family: monospace; font-size: 1.2em; border: 1px dashed #ced4da;">
            {{ $linha_digitavel }}
        </div>
    @endif

    @if ($link_pagamento)
        <p>
            <a href="{{ $link_pagamento }}"
                style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">
                Pagar Agora
            </a>
        </p>
    @endif

    @if ($link_boleto)
        <p>
            <a href="{{ $link_boleto }}"
                style="background-color: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">
                Visualizar Boleto
            </a>
        </p>
    @endif

    @if ($codigo_pix)
        <div style="margin-top: 20px;">
            <p><strong>Pagamento via PIX (Copia e Cola):</strong></p>
            <div
                style="background-color: #e9ecef; padding: 10px; font-family: monospace; font-size: 0.9em; border: 1px solid #ced4da; word-break: break-all;">
                {{ $codigo_pix }}
            </div>
            <p><small>Copie o código acima e cole no aplicativo do seu banco.</small></p>
        </div>
    @endif

    <p>A Nota Fiscal de Serviço Eletrônica (NFS-e) referente a esta cobrança segue em anexo.</p>

    <p>Atenciosamente,<br>
        Equipe {{ config('app.name') }}</p>
</body>

</html>
