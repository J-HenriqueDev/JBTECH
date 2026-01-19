<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>DANFE - Documento Auxiliar da Nota Fiscal Eletrônica</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        td, th { border: 1px solid #000; padding: 2px; vertical-align: top; }
        .no-border { border: none; }
        .bold { font-weight: bold; }
        .center { text-align: center; }
        .right { text-align: right; }
        .title { font-size: 12px; font-weight: bold; }
        .box { border: 1px solid #000; padding: 5px; margin-bottom: 5px; }
        .header-box { min-height: 100px; }
    </style>
</head>
<body>
    <!-- Canhoto -->
    <table>
        <tr>
            <td width="80%">
                <div class="bold">RECEBEMOS DE {{ $notaFiscal->dados_emitente['xNome'] }} OS PRODUTOS/SERVIÇOS CONSTANTES DA NOTA FISCAL INDICADA AO LADO</div>
                <br>
                DATA DE RECEBIMENTO: _________________________ IDENTIFICAÇÃO E ASSINATURA DO RECEBEDOR: _________________________
            </td>
            <td width="20%" class="center">
                <div class="bold">NF-e</div>
                <div class="title">Nº {{ $notaFiscal->numero_nfe }}</div>
                <div class="bold">SÉRIE {{ $notaFiscal->serie ?? '1' }}</div>
            </td>
        </tr>
    </table>

    <div style="border-bottom: 1px dashed #000; margin: 10px 0;"></div>

    <!-- Cabeçalho -->
    <table>
        <tr>
            <td width="40%">
                <div class="bold">{{ $notaFiscal->dados_emitente['xNome'] }}</div>
                <div>{{ $notaFiscal->dados_emitente['xLgr'] }}, {{ $notaFiscal->dados_emitente['nro'] }}</div>
                <div>{{ $notaFiscal->dados_emitente['xBairro'] }} - {{ $notaFiscal->dados_emitente['xMun'] }} - {{ $notaFiscal->dados_emitente['UF'] }}</div>
                <div>Fone: {{ $notaFiscal->dados_emitente['fone'] }}</div>
            </td>
            <td width="20%" class="center">
                <div class="title bold">DANFE</div>
                <div>Documento Auxiliar da Nota Fiscal Eletrônica</div>
                <br>
                <div class="bold">0 - Entrada</div>
                <div class="bold">1 - Saída</div>
                <div class="box bold" style="font-size: 14px; width: 20px; margin: 0 auto;">1</div>
                <br>
                <div class="bold">Nº {{ $notaFiscal->numero_nfe }}</div>
                <div class="bold">SÉRIE {{ $notaFiscal->serie ?? '1' }}</div>
            </td>
            <td width="40%">
                <div class="bold">CHAVE DE ACESSO</div>
                <div class="center" style="font-size: 11px;">{{ preg_replace('/(\d{4})/', '$1 ', $notaFiscal->chave_acesso) }}</div>
                <br>
                <div class="center">Consulta de autenticidade no portal nacional da NF-e www.nfe.fazenda.gov.br/portal ou no site da Sefaz Autorizadora</div>
            </td>
        </tr>
    </table>

    <!-- Natureza da Operação e Protocolo -->
    <table>
        <tr>
            <td width="60%">
                <div class="bold">NATUREZA DA OPERAÇÃO</div>
                <div>Venda de Mercadoria</div>
            </td>
            <td width="40%">
                <div class="bold">PROTOCOLO DE AUTORIZAÇÃO DE USO</div>
                <div>{{ $notaFiscal->protocolo }} - {{ $notaFiscal->data_emissao->format('d/m/Y H:i:s') }}</div>
            </td>
        </tr>
    </table>

    <!-- Inscrição Estadual -->
    <table>
        <tr>
            <td>
                <div class="bold">INSCRIÇÃO ESTADUAL</div>
                <div>{{ $notaFiscal->dados_emitente['IE'] }}</div>
            </td>
            <td>
                <div class="bold">INSCRIÇÃO ESTADUAL DO SUBST. TRIB.</div>
                <div></div>
            </td>
            <td>
                <div class="bold">CNPJ</div>
                <div>{{ $notaFiscal->dados_emitente['CNPJ'] }}</div>
            </td>
        </tr>
    </table>

    <!-- Destinatário/Remetente -->
    <div class="bold" style="background-color: #eee; padding: 2px;">DESTINATÁRIO / REMETENTE</div>
    <table>
        <tr>
            <td width="60%">
                <div class="bold">NOME/RAZÃO SOCIAL</div>
                <div>{{ $notaFiscal->dados_destinatario['xNome'] }}</div>
            </td>
            <td width="30%">
                <div class="bold">CNPJ/CPF</div>
                <div>{{ $notaFiscal->dados_destinatario['CNPJ'] ?? $notaFiscal->dados_destinatario['CPF'] ?? '' }}</div>
            </td>
            <td width="10%">
                <div class="bold">DATA DA EMISSÃO</div>
                <div>{{ $notaFiscal->data_emissao->format('d/m/Y') }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="bold">ENDEREÇO</div>
                <div>{{ $notaFiscal->dados_destinatario['xLgr'] }}, {{ $notaFiscal->dados_destinatario['nro'] }}</div>
            </td>
            <td>
                <div class="bold">BAIRRO/DISTRITO</div>
                <div>{{ $notaFiscal->dados_destinatario['xBairro'] }}</div>
            </td>
            <td>
                <div class="bold">CEP</div>
                <div>{{ $notaFiscal->dados_destinatario['CEP'] }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="bold">MUNICÍPIO</div>
                <div>{{ $notaFiscal->dados_destinatario['xMun'] }}</div>
            </td>
            <td>
                <div class="bold">FONE/FAX</div>
                <div>{{ $notaFiscal->dados_destinatario['fone'] ?? '' }}</div>
            </td>
            <td>
                <div class="bold">UF</div>
                <div>{{ $notaFiscal->dados_destinatario['UF'] }}</div>
            </td>
        </tr>
    </table>

    <!-- Cálculo do Imposto -->
    <div class="bold" style="background-color: #eee; padding: 2px;">CÁLCULO DO IMPOSTO</div>
    <table>
        <tr>
            <td><div class="bold">BASE DE CÁLCULO DO ICMS</div><div class="right">0,00</div></td>
            <td><div class="bold">VALOR DO ICMS</div><div class="right">0,00</div></td>
            <td><div class="bold">BASE DE CÁLC. ICMS S.T.</div><div class="right">0,00</div></td>
            <td><div class="bold">VALOR DO ICMS S.T.</div><div class="right">0,00</div></td>
            <td><div class="bold">VALOR TOTAL DOS PRODUTOS</div><div class="right">{{ number_format($notaFiscal->valor_total, 2, ',', '.') }}</div></td>
        </tr>
        <tr>
            <td><div class="bold">VALOR DO FRETE</div><div class="right">0,00</div></td>
            <td><div class="bold">VALOR DO SEGURO</div><div class="right">0,00</div></td>
            <td><div class="bold">DESCONTO</div><div class="right">0,00</div></td>
            <td><div class="bold">OUTRAS DESPESAS</div><div class="right">0,00</div></td>
            <td><div class="bold">VALOR TOTAL DA NOTA</div><div class="right">{{ number_format($notaFiscal->valor_total, 2, ',', '.') }}</div></td>
        </tr>
    </table>

    <!-- Transportador/Volumes -->
    <div class="bold" style="background-color: #eee; padding: 2px;">TRANSPORTADOR / VOLUMES TRANSPORTADOS</div>
    <table>
        <tr>
            <td width="50%">
                <div class="bold">RAZÃO SOCIAL</div>
                <div>O Mesmo</div>
            </td>
            <td width="15%">
                <div class="bold">FRETE POR CONTA</div>
                <div>9 - Sem Frete</div>
            </td>
            <td width="15%">
                <div class="bold">CÓDIGO ANTT</div>
                <div></div>
            </td>
            <td width="20%">
                <div class="bold">PLACA DO VEÍCULO</div>
                <div></div>
            </td>
        </tr>
    </table>

    <!-- Dados do Produto/Serviço -->
    <div class="bold" style="background-color: #eee; padding: 2px;">DADOS DO PRODUTO / SERVIÇO</div>
    <table>
        <thead>
            <tr>
                <th>CÓDIGO</th>
                <th>DESCRIÇÃO</th>
                <th>NCM/SH</th>
                <th>CST</th>
                <th>CFOP</th>
                <th>UNID.</th>
                <th>QTD.</th>
                <th>V.UNIT.</th>
                <th>V.TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($notaFiscal->produtos as $prod)
            <tr>
                <td>{{ $prod['id'] }}</td>
                <td>{{ $prod['nome'] }}</td>
                <td>{{ $prod['ncm'] }}</td>
                <td>0102</td>
                <td>5102</td>
                <td>UN</td>
                <td class="right">{{ number_format($prod['quantidade'], 2, ',', '.') }}</td>
                <td class="right">{{ number_format($prod['valor_unitario'], 2, ',', '.') }}</td>
                <td class="right">{{ number_format($prod['valor_total'], 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Dados Adicionais -->
    <div class="bold" style="background-color: #eee; padding: 2px;">DADOS ADICIONAIS</div>
    <div class="box" style="min-height: 50px;">
        <div class="bold">INFORMAÇÕES COMPLEMENTARES</div>
        <div>Documento emitido por ME ou EPP optante pelo Simples Nacional. Não gera direito a crédito fiscal de IPI.</div>
    </div>
</body>
</html>
