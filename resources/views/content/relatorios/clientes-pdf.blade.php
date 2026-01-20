<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Relatório de Clientes</title>
    <style>
        @page {
            margin: 1cm 1cm 3.5cm 1cm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.4;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-center {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo {
            max-width: 150px;
            max-height: 80px;
        }

        .document-title {
            text-align: center;
            margin: 10px 0;
            padding: 5px;
            background-color: #f5f5f5;
            border-top: 1px solid #ddd;
            border-bottom: 1px solid #ddd;
        }

        .document-title h1 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
        }

        .document-title p {
            margin: 5px 0 0;
            font-size: 12px;
            color: #666;
        }

        .box {
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 20px;
            background-color: #fff;
        }

        .box-title {
            font-weight: bold;
            font-size: 13px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
            margin-bottom: 10px;
            display: block;
        }

        .items-table {
            margin-bottom: 20px;
        }

        .items-table th {
            background-color: #eee;
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }

        .items-table td {
            border: 1px solid #ccc;
            padding: 8px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }
    </style>
</head>

<body>
    @include('layouts.pdf_footer')


    <!-- Header -->
    <div class="header-center">
        <img src="{{ public_path('assets/img/front-pages/landing-page/jblogo_black.png') }}" class="logo" alt="Logo">
    </div>

    <!-- Title -->
    <div class="document-title">
        <h1>Relatório de Clientes</h1>
        <p>Emissão: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    </div>

    <!-- Summary Box -->
    <div class="box">
        <span class="box-title">RESUMO</span>
        <table style="width: 100%;">
            <tr>
                <td><strong>Total de Clientes:</strong> {{ $clientes->count() }}</td>
                <td class="text-right"><strong>Total de Vendas (Acumulado):</strong> R$ {{ number_format($totalVendas ?? 0, 2, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <!-- Clients Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th width="5%" class="text-center">ID</th>
                <th width="30%">Nome</th>
                <th width="20%">CPF/CNPJ</th>
                <th width="25%">Email</th>
                <th width="20%">Telefone</th>
            </tr>
        </thead>
        <tbody>
            @forelse($clientes as $cliente)
            <tr>
                <td class="text-center">{{ $cliente->id }}</td>
                <td>{{ $cliente->nome }}</td>
                <td>{{ formatarCpfCnpj($cliente->cpf_cnpj) }}</td>
                <td>{{ $cliente->email }}</td>
                <td>{{ $cliente->telefone }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">Nenhum cliente encontrado.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

</body>

</html>