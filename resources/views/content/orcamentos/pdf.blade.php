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
            background-color: #f5f5f5;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            background: #fff;
            border-radius: 6px;
            padding: 20px 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
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
            border-bottom: 1px solid #ddd;
            padding-bottom: 15px;
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
            padding: 10px;
            text-align: left;
            font-size: 14px;
        }
        table th {
            background-color: #f9f9f9;
            font-weight: bold;
        }
        .divider {
            margin: 20px 0;
            border-bottom: 1px solid #ddd;
        }
        .payment-methods {
            margin-top: 20px;
            font-size: 14px;
        }
        .payment-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .payment-table th, .payment-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
            font-size: 14px;
        }
        .payment-table th {
            background-color: #f9f9f9;
        }
        .summary {
            text-align: right;
            margin-top: 20px;
        }
        .summary p {
            margin: 5px 0;
            font-size: 14px;
        }
        .summary .total {
            font-size: 16px;
            font-weight: bold;
            color: #007bff;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #666;
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Cabeçalho -->
        <div class="header">
            <img src="data:image/png;base64,{{ $logoBase64 }}" alt="JBTECH Logo">
            <h1>Orçamento #{{ $orcamento->id }}</h1>
        </div>

        <!-- Informações do Cliente -->
        <div class="info-section">
          <p><strong>Cliente:</strong> {{ $orcamento->cliente->nome }}</p>
          <p><strong>CPF/CNPJ:</strong> {{ $orcamento->cliente->cpf_cnpj }}</p>
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

        <!-- Divider -->
        <div class="divider"></div>

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

        <!-- Rodapé -->
        <div class="footer">
            <p>JBTECH Informática - Tecnologia ao Seu Alcance</p>
            <p>Av. Tocantinha, 470, Sala 02, Resende, RJ | CNPJ: 54.819.910/0001-20</p>
            <p>Telefone: +55 (24) 98113-2097 | E-mail: contato@jbtech.com</p>
        </div>
    </div>
</body>
</html>
