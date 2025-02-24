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
            padding-bottom: 100px; /* Reduzido para aproximar o rodapé */
        }
        .header {
            text-align: center;
            margin-bottom: 15px; /* Reduzido */
        }
        .header img {
            max-width: 120px; /* Reduzido */
            margin-bottom: 5px; /* Reduzido */
        }
        .header h1 {
            font-size: 20px; /* Reduzido */
            color: #333;
            margin: 0;
        }
        .info-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px; /* Reduzido */
            margin-bottom: 15px; /* Reduzido */
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px; /* Reduzido */
        }
        .info-section p {
            margin: 3px 0; /* Reduzido */
            font-size: 13px; /* Reduzido */
        }
        h2 {
            font-size: 16px; /* Reduzido */
            margin-bottom: 10px; /* Reduzido */
            color: #007bff;
            border-bottom: 2px solid #007bff;
            padding-bottom: 3px; /* Reduzido */
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px; /* Reduzido */
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px; /* Reduzido */
            text-align: left;
            font-size: 13px; /* Reduzido */
        }
        table th {
            background-color: #f9f9f9;
            font-weight: bold;
        }
        .divider {
            margin: 10px 0; /* Reduzido */
            border-bottom: 1px solid #ddd;
        }
        .payment-methods {
            margin-top: 15px; /* Reduzido */
            font-size: 13px; /* Reduzido */
        }
        .payment-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px; /* Reduzido */
        }
        .payment-table th, .payment-table td {
            border: 1px solid #ddd;
            padding: 8px; /* Reduzido */
            text-align: left;
            font-size: 13px; /* Reduzido */
        }
        .payment-table th {
            background-color: #f9f9f9;
        }
        .summary {
            text-align: right;
            margin-top: 15px; /* Reduzido */
        }
        .summary p {
            margin: 3px 0; /* Reduzido */
            font-size: 13px; /* Reduzido */
        }
        .summary .total {
            font-size: 14px; /* Reduzido */
            font-weight: bold;
            color: #007bff;
        }
        .observacao {
            margin-top: 15px; /* Reduzido */
            padding: 10px; /* Reduzido */
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 6px;
        }
        .observacao h2 {
            margin-top: 0;
        }
        .footer {
            text-align: center;
            font-size: 11px; /* Reduzido */
            color: #666;
            padding: 8px 0; /* Reduzido */
            border-top: 1px solid #ddd;
            background-color: #fff;
            position: absolute;
            bottom: 10px; /* Reduzido */
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Cabeçalho -->
        <div class="header">
            <img src="https://jbtechresende.com.br/assets/img/front-pages/landing-page/jblogo_black.png" alt="JBTECH Logo">
            <h1>Orçamento #{{ $orcamento->id }}</h1>
        </div>

        <!-- Informações do Cliente -->
        <div class="info-section">
            <p><strong>Cliente:</strong> {{ $orcamento->cliente->nome }}</p>
            <p><strong>CPF/CNPJ:</strong> {{ formatarCpfCnpj($orcamento->cliente->cpf_cnpj) }}</p>
            <div style="display: flex; justify-content: space-between;">
                <p><strong>Data de Emissão:</strong> {{ Carbon\Carbon::parse($orcamento->data)->translatedFormat('d \d\e F \d\e Y') }}</p>
                <p><strong>Validade:</strong> {{ Carbon\Carbon::parse($orcamento->validade)->translatedFormat('d \d\e F \d\e Y') }}</p>
            </div>
        </div>

        <!-- Produtos e Serviços -->
        <h2>Produtos e Serviços</h2>
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
                @php
                    $subtotalProdutos = 0;
                    $valorServico = $orcamento->produtos->firstWhere('id', 1)?->pivot?->valor_unitario ?? 0;
                @endphp

                @foreach($orcamento->produtos as $produto)
                    @php
                        $valorTotalProduto = $produto->pivot->quantidade * $produto->pivot->valor_unitario;
                        if ($produto->id != 1) {
                            $subtotalProdutos += $valorTotalProduto;
                        }
                    @endphp
                    <tr>
                        <td>{{ $produto->id }}</td>
                        <td>{{ $produto->nome }}</td>
                        <td>{{ $produto->pivot->quantidade }}</td>
                        <td>R$ {{ number_format($produto->pivot->valor_unitario, 2, ',', '.') }}</td>
                        <td>R$ {{ number_format($valorTotalProduto, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Resumo Financeiro -->
        <div class="summary">
            <p><strong>Subtotal de Produtos (sem serviço):</strong> R$ {{ number_format($subtotalProdutos, 2, ',', '.') }}</p>
            <p><strong>Valor do Serviço:</strong> R$ {{ number_format($valorServico, 2, ',', '.') }}</p>
            <p class="total">Total Geral: R$ {{ number_format($subtotalProdutos + $valorServico, 2, ',', '.') }}</p>
        </div>

        <!-- Observação -->
        @if (!empty($orcamento->observacoes))
        <div class="observacao">
            <h2>Descrição do Orçamento</h2>
            <p>{{ $orcamento->observacoes }}</p>
        </div>
        @endif

        <!-- Formas de Pagamento -->
        <div class="payment-methods">
            <h2>Formas de Pagamento</h2>
            <table class="payment-table">
                <thead>
                    <tr>
                        <th>Forma</th>
                        <th>Condição</th>
                        <th>Taxa</th>
                        <th>Valor Final</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $valorPix = $subtotalProdutos + $valorServico;
                        $valor10x = $valorPix * (1 + 0.12436);
                    @endphp
                    <tr>
                        <td>Pix</td>
                        <td>À vista</td>
                        <td>0%</td>
                        <td>R$ {{ number_format($valorPix, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Cartão</td>
                        <td>10x</td>
                        <td>12,44%</td>
                        <td>R$ {{ number_format($valor10x, 2, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
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
