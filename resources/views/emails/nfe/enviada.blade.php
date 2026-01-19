<!DOCTYPE html>
<html>
<head>
    <title>Nota Fiscal Eletrônica</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #f8f9fa; padding: 10px; text-align: center; border-bottom: 1px solid #ddd; }
        .content { padding: 20px 0; }
        .footer { font-size: 12px; color: #777; text-align: center; margin-top: 20px; border-top: 1px solid #ddd; padding-top: 10px; }
        .info-box { background-color: #f0f4ff; padding: 15px; border-radius: 5px; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Nota Fiscal Eletrônica</h2>
        </div>
        <div class="content">
            <p>Olá, <strong>{{ $cliente->nome }}</strong>,</p>
            
            <p>Segue em anexo a Nota Fiscal Eletrônica (NF-e) referente à sua compra.</p>
            
            <div class="info-box">
                <p style="margin: 5px 0;"><strong>Número da Nota:</strong> {{ $notaFiscal->numero_nfe }}</p>
                <p style="margin: 5px 0;"><strong>Série:</strong> {{ $notaFiscal->serie ?? '1' }}</p>
                <p style="margin: 5px 0;"><strong>Data de Emissão:</strong> {{ $notaFiscal->data_emissao ? $notaFiscal->data_emissao->format('d/m/Y') : date('d/m/Y') }}</p>
                <p style="margin: 5px 0;"><strong>Valor Total:</strong> R$ {{ number_format($notaFiscal->valor_total, 2, ',', '.') }}</p>
                <p style="margin: 5px 0;"><strong>Chave de Acesso:</strong> <br><small>{{ $notaFiscal->chave_acesso }}</small></p>
            </div>
            
            <p>Os arquivos XML e PDF (DANFE) estão anexados a este email.</p>
            
            <p>Atenciosamente,<br>
            {{ config('app.name') }}</p>
        </div>
        <div class="footer">
            <p>Este é um email automático, por favor não responda.</p>
        </div>
    </div>
</body>
</html>
