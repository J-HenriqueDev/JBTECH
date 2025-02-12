@extends('layouts.layoutMaster')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/select2/select2.scss',
  'resources/assets/vendor/libs/swiper/swiper.scss'
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/select2/select2.js',
  'resources/assets/vendor/libs/swiper/swiper.js'
])
@endsection

@section('page-script')
@vite([
  'resources/assets/js/forms-selects.js'
])
@endsection

@section('content')
<h1 class="mb-4 text-primary" style="font-size: 2.5rem; font-weight: bold; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);">
  <i class="fas fa-plus-circle"></i> Importar Produtos via XML
</h1>
<div class="col-md-12">
    <div class="card p-4 shadow-sm">
        <form action="{{ route('produtos.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="xml_file" class="form-label"><i class="bx bx-file"></i> Carregar Arquivo XML</label>
                <input type="file" name="xml_file" id="xml_file" class="form-control" accept=".xml" required>
            </div>
            <button type="submit" class="btn btn-primary w-100"><i class="bx bx-import"></i> Importar</button>
        </form>

        @if (!empty($productsData))
        <h2 class="mt-5 text-center"><i class="bx bx-list-ul"></i> Produtos Importados</h2>
        <form action="{{ route('produtos.store') }}" method="POST">
            @csrf
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th class="col-md-3">Nome</th>
                            <th class="d-none d-md-table-cell">Preço Custo</th>
                            <th class="d-none d-md-table-cell">Preço Venda</th>
                            <th class="d-none d-md-table-cell">Código de Barras</th>
                            <th class="d-none d-md-table-cell">NCM</th>
                            <th class="col-md-1">Estoque</th> <!-- Reduzido o tamanho da coluna -->
                            <th>Categoria</th>
                            <th>Lucro (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($productsData as $index => $product)
                        <tr>
                            <td>
                                <input type="text" name="produtos[{{ $index }}][nome]"
                                       value="{{ $product['nome'] }}"
                                       class="form-control"
                                       style="width: 100%;" required>
                            </td>
                            <td class="d-none d-md-table-cell">
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    <input type="text" name="produtos[{{ $index }}][preco_custo]"
                                           value="{{ number_format($product['preco_custo'], 2, ',', '.') }}"
                                           class="form-control" required oninput="formatCurrency(this)">
                                </div>
                            </td>
                            <td class="d-none d-md-table-cell">
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    <input type="text" name="produtos[{{ $index }}][preco_venda]"
                                           value="{{ number_format($product['preco_venda'], 2, ',', '.') }}"
                                           class="form-control" required oninput="formatCurrency(this)">
                                </div>
                            </td>
                            <td class="d-none d-md-table-cell">
                                <input type="text" name="produtos[{{ $index }}][codigo_barras]"
                                       value="{{ $product['codigo_barras'] }}"
                                       class="form-control">
                            </td>
                            <td class="d-none d-md-table-cell">
                                <input type="text" name="produtos[{{ $index }}][ncm]"
                                       value="{{ $product['ncm'] }}"
                                       class="form-control" required>
                            </td>
                            <td>
                                <input type="number" name="produtos[{{ $index }}][estoque]"
                                       value="{{ $product['estoque'] }}"
                                       class="form-control" style="width: 80px;" required> <!-- Reduzido o tamanho do campo -->
                            </td>
                            <td>
                                <select name="produtos[{{ $index }}][categoria_id]" class="form-control">
                                    @foreach ($categorias as $categoria)
                                    <option value="{{ $categoria->id }}">{{ $categoria->nome }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <div class="form-group">
                                    <h5 id="lucro_percentual_{{ $index }}" style="margin-top: 2px;">
                                        {{ number_format(($product['preco_venda'] - $product['preco_custo']) / $product['preco_custo'] * 100, 2) }}%
                                    </h5>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="divider my-4">
                <h4><i class="bx bx-buildings"></i> Informações do Fornecedor</h4>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label for="fornecedor_cnpj" class="form-label"><i class="bx bx-id-card"></i> CNPJ do Fornecedor</label>
                    <input type="text" class="form-control" name="fornecedor_cnpj" id="fornecedor_cnpj"
                          value="{{ $fornecedor['cnpj'] ?? '' }}" required oninput="formatCNPJ(this)">
                </div>
                <div class="col-md-6">
                    <label for="fornecedor_nome" class="form-label"><i class="bx bx-user"></i> Nome do Fornecedor</label>
                    <input type="text" class="form-control" name="fornecedor_nome" id="fornecedor_nome"
                          value="{{ $fornecedor['nome'] ?? '' }}" required>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-md-6">
                    <label for="fornecedor_telefone" class="form-label"><i class="bx bx-phone"></i> Telefone do Fornecedor</label>
                    <input type="text" class="form-control" name="fornecedor_telefone" id="fornecedor_telefone"
                          value="{{ $fornecedor['telefone'] ?? '' }}" required oninput="formatTelefone(this)">
                </div>
                <div class="col-md-6">
                    <label for="fornecedor_email" class="form-label"><i class="bx bx-envelope"></i> E-mail do Fornecedor</label>
                    <input type="email" class="form-control" name="fornecedor_email" id="fornecedor_email"
                          value="{{ $fornecedor['email'] ?? '' }}">
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 text-end">
                    <button type="button" class="btn btn-secondary me-2" onclick="window.history.back();">
                        <i class="bx bx-x"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-save"></i> Salvar Produtos
                    </button>
                </div>
            </div>
        </form>
        @endif
    </div>
</div>

<style>
    .input-group-text {
        font-size: 16px; /* Ajuste o tamanho da fonte do "R$" */
    }
    .table-responsive {
        overflow-x: auto;
    }
    .table th, .table td {
        vertical-align: middle; /* Centraliza o conteúdo das células */
    }
    .table thead th {
        white-space: nowrap; /* Evita quebra de linha no cabeçalho */
    }
    .table tbody td {
        font-size: 14px; /* Reduz o tamanho da fonte em dispositivos móveis */
    }
    @media (max-width: 768px) {
        .table tbody td {
            font-size: 12px; /* Reduz ainda mais o tamanho da fonte em telas pequenas */
        }
        .btn {
            width: 100%; /* Botões ocupam toda a largura em dispositivos móveis */
            margin-bottom: 10px; /* Adiciona espaçamento entre os botões */
        }
    }
</style>

<script>
    // Função para formatar CNPJ
    function formatCNPJ(input) {
        let value = input.value.replace(/\D/g, ''); // Remove caracteres não numéricos
        if (value.length > 14) value = value.slice(0, 14); // Limita a 14 dígitos
        value = value.replace(/^(\d{2})(\d)/, '$1.$2'); // Adiciona o primeiro ponto
        value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3'); // Adiciona o segundo ponto
        value = value.replace(/\.(\d{3})(\d)/, '.$1/$2'); // Adiciona a barra
        value = value.replace(/(\d{4})(\d)/, '$1-$2'); // Adiciona o traço
        input.value = value;
    }

    // Função para formatar telefone
    function formatTelefone(input) {
        let value = input.value.replace(/\D/g, ''); // Remove caracteres não numéricos
        if (value.length > 11) value = value.slice(0, 11); // Limita a 11 dígitos
        if (value.length > 10) {
            value = value.replace(/^(\d{2})(\d{5})(\d{4})/, '($1) $2-$3'); // Formato para celular
        } else {
            value = value.replace(/^(\d{2})(\d{4})(\d{4})/, '($1) $2-$3'); // Formato para telefone fixo
        }
        input.value = value;
    }

    // Função para formatar valores monetários
    function formatCurrency(input) {
        let value = input.value.replace(/\D/g, ''); // Remove caracteres não numéricos
        value = (value / 100).toFixed(2) + ''; // Adiciona casas decimais
        value = value.replace('.', ','); // Troca ponto por vírgula
        value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.'); // Adiciona pontos como separador de milhar
        input.value = value;
    }

    document.addEventListener('DOMContentLoaded', function () {
        const inputsPreco = document.querySelectorAll('input[name*="[preco_custo]"], input[name*="[preco_venda]"]');
        inputsPreco.forEach(input => {
            input.addEventListener('input', function () {
                const index = this.name.match(/\[(\d+)\]/)[1];
                const precoCusto = parseFloat(document.querySelector(`input[name="produtos[${index}][preco_custo]"]`).value.replace('R$ ', '').replace('.', '').replace(',', '.'));
                const precoVenda = parseFloat(document.querySelector(`input[name="produtos[${index}][preco_venda]"]`).value.replace('R$ ', '').replace('.', '').replace(',', '.'));

                if (!isNaN(precoCusto) && !isNaN(precoVenda) && precoCusto > 0) {
                    const lucro = ((precoVenda - precoCusto) / precoCusto) * 100;
                    document.querySelector(`#lucro_percentual_${index}`).textContent = `${lucro.toFixed(2)}%`;
                } else {
                    document.querySelector(`#lucro_percentual_${index}`).textContent = '0%';
                }
            });
        });
    });
</script>
@endsection
