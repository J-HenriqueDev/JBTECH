<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>NF-e {{ $notaFiscal->chave_acesso ?? '' }}</title>
    <style>
        @page {
            margin: 1cm 1cm 3.5cm 1cm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
        }

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        .header {
            width: 100%;
            border: 1px solid #000;
            padding: 6px;
            margin-bottom: 4px;
        }

        .header-left {
            float: left;
            width: 25%;
            text-align: center;
        }

        .header-left img {
            max-width: 140px;
            max-height: 60px;
        }

        .header-center {
            float: left;
            width: 45%;
            text-align: center;
            border-left: 1px solid #000;
            border-right: 1px solid #000;
            padding: 0 4px;
        }

        .header-right {
            float: left;
            width: 30%;
            text-align: center;
        }

        .titulo-danfe {
            font-size: 14px;
            font-weight: bold;
        }

        .subtitulo-danfe {
            font-size: 9px;
        }

        .quadro {
            width: 100%;
            border: 1px solid #000;
            margin-bottom: 3px;
            padding: 3px;
        }

        .quadro-title {
            font-weight: bold;
            font-size: 9px;
            border-bottom: 1px solid #000;
            margin-bottom: 2px;
        }

        .linha {
            display: flex;
            flex-wrap: nowrap;
            margin-bottom: 2px;
        }

        .campo {
            font-size: 9px;
            padding-right: 4px;
        }

        .campo strong {
            font-size: 8px;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }

        table.items th,
        table.items td {
            border: 1px solid #000;
            padding: 2px 3px;
            font-size: 9px;
        }

        table.items th {
            background-color: #f0f0f0;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .footer {
            position: fixed;
            bottom: -3cm;
            left: 0;
            right: 0;
            height: 90px;
            border-top: 1px solid #ccc;
            padding-top: 6px;
            text-align: center;
            font-size: 9px;
            color: #555;
            background-color: #fff;
        }
    </style>
</head>
@php
    $emitente    = (object) ($notaFiscal->dados_emitente ?? []);
    $dest        = (object) ($notaFiscal->dados_destinatario ?? []);
    $produtos    = $notaFiscal->produtos ?? [];
    $emitenteNome = $emitente->xNome ?? \App\Models\Configuracao::get('empresa_nome', 'JBTECH Informática');
    $emitenteCnpj = $emitente->CNPJ ?? \App\Models\Configuracao::get('empresa_cnpj', '00.000.000/0001-00');
    $chave       = $notaFiscal->chave_acesso ?? '';
    $chaveFormatada = $chave ? trim(chunk_split($chave, 4, ' ')) : '';
    $logoPath = public_path('assets/img/front-pages/landing-page/jblogo_black.png');
    $logoBase64 = '';
    if (file_exists($logoPath)) {
        $logoBase64 = base64_encode(file_get_contents($logoPath));
    }
@endphp
<body>
    <div class="footer">
        <p><strong>Powered by JBTECH Informática</strong> - Tecnologia ao seu alcance</p>
        <p>{{ $emitenteNome }} - CNPJ: {{ $emitenteCnpj }}</p>
        <p>Documento gerado em {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="header clearfix">
        <div class="header-left">
            @if($logoBase64)
                <img src="data:image/png;base64,{{ $logoBase64 }}" alt="Logo JBTECH">
            @else
                <strong>{{ $emitenteNome }}</strong>
            @endif
        </div>
        <div class="header-center">
            <div class="titulo-danfe">DANFE - Documento Auxiliar da NF-e</div>
            <div class="subtitulo-danfe">Visualização gerada pelo sistema JBTECH (não substitui o XML autorizado)</div>
            @if($chaveFormatada)
                <div style="margin-top: 4px; font-size: 9px;">
                    <strong>Chave de Acesso:</strong><br>
                    {{ $chaveFormatada }}
                </div>
            @endif
        </div>
        <div class="header-right">
            <div style="font-size: 9px; text-align: left;">
                <strong>NF-e Nº:</strong> {{ $notaFiscal->numero_nfe ?? '-' }}<br>
                <strong>Série:</strong> {{ $notaFiscal->serie ?? '-' }}<br>
                <strong>Emissão:</strong> {{ $notaFiscal->data_emissao ? $notaFiscal->data_emissao->format('d/m/Y') : '-' }}<br>
                <strong>Valor Total:</strong> R$ {{ number_format($notaFiscal->valor_total ?? 0, 2, ',', '.') }}<br>
                <strong>Status:</strong> {{ strtoupper($notaFiscal->status) }}
            </div>
        </div>
    </div>

    <div class="quadro">
        <div class="quadro-title">DESTINATÁRIO / REMETENTE</div>
        <div class="linha">
            <div class="campo" style="flex: 3;">
                <strong>Nome / Razão Social:</strong><br>
                {{ $dest->xNome ?? 'NÃO INFORMADO' }}
            </div>
            <div class="campo" style="flex: 1.5;">
                <strong>CPF/CNPJ:</strong><br>
                {{ $dest->cpf_cnpj ?? '-' }}
            </div>
            <div class="campo" style="flex: 1;">
                <strong>IE:</strong><br>
                {{ $dest->IE ?? '-' }}
            </div>
        </div>
        <div class="linha">
            <div class="campo" style="flex: 3;">
                <strong>Endereço:</strong><br>
                {{ ($dest->xLgr ?? '') . ', ' . ($dest->nro ?? '') . ' ' . ($dest->xCpl ?? '') }}
            </div>
            <div class="campo" style="flex: 2;">
                <strong>Bairro:</strong><br>
                {{ $dest->xBairro ?? '' }}
            </div>
        </div>
        <div class="linha">
            <div class="campo" style="flex: 2;">
                <strong>Município:</strong><br>
                {{ $dest->xMun ?? '' }}
            </div>
            <div class="campo" style="flex: 0.8;">
                <strong>UF:</strong><br>
                {{ $dest->UF ?? '' }}
            </div>
            <div class="campo" style="flex: 1.2;">
                <strong>CEP:</strong><br>
                {{ $dest->CEP ?? '' }}
            </div>
            <div class="campo" style="flex: 1.5;">
                <strong>Email:</strong><br>
                {{ $dest->email ?? '' }}
            </div>
        </div>
    </div>

    <div class="quadro">
        <div class="quadro-title">PRODUTOS / SERVIÇOS</div>
        <table class="items">
            <thead>
                <tr>
                    <th class="text-center" width="5%">Item</th>
                    <th width="35%">Descrição</th>
                    <th class="text-center" width="10%">NCM</th>
                    <th class="text-center" width="10%">CFOP</th>
                    <th class="text-center" width="5%">UN</th>
                    <th class="text-center" width="10%">Qtde</th>
                    <th class="text-right" width="10%">V. Unit.</th>
                    <th class="text-right" width="15%">V. Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($produtos as $index => $p)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $p['xProd'] ?? $p['descricao'] ?? 'Item ' . ($index + 1) }}</td>
                        <td class="text-center">{{ $p['NCM'] ?? '' }}</td>
                        <td class="text-center">{{ $p['CFOP'] ?? '' }}</td>
                        <td class="text-center">{{ $p['uCom'] ?? $p['unidade'] ?? '' }}</td>
                        <td class="text-center">{{ number_format((float)($p['qCom'] ?? $p['quantidade'] ?? 0), 2, ',', '.') }}</td>
                        <td class="text-right">R$ {{ number_format((float)($p['vUnCom'] ?? $p['valor_unitario'] ?? 0), 2, ',', '.') }}</td>
                        <td class="text-right">R$ {{ number_format((float)($p['vProd'] ?? $p['valor_total'] ?? 0), 2, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">Nenhum produto vinculado à nota.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>

