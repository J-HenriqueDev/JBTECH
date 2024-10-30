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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<div class="d-flex justify-content-between align-items-center">
  <h1 class="mb-4 text-primary" style="font-size: 2.5rem; font-weight: bold; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);">
    <i class="fas fa-plus-circle"></i> Cadastro de Produtos
  </h1>
  <form action="{{ route('produtos.import') }}" method="POST" enctype="multipart/form-data" class="d-inline">
    @csrf
    <input type="file" name="xml_file" accept=".xml" required class="d-none" id="importXml">
    <label for="importXml" class="btn btn-primary">
        <i class="fas fa-plus-circle me-1"></i> Importar XML
    </label>
    <button type="submit" class="btn btn-success">Enviar XML</button>
  </form>
</div>

<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      <form action="{{ route('produtos.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
          @if(!empty($productsData)) {{-- Exibe os dados do XML se disponíveis --}}
            @foreach($productsData as $index => $product)
              <div class="row mb-4">
                <h4>Produto {{ $index + 1 }}</h4>

                <div class="col-md-6">
                  <div class="form-group">
                    <label for="nome_{{ $index }}">
                      <i class="fas fa-tag"></i> Nome do Produto
                    </label>
                    <input type="text" class="form-control" name="produtos[{{ $index }}][nome]" id="nome_{{ $index }}" value="{{ old('produtos.'.$index.'.nome', $product['nome']) }}" required>
                    @error('produtos.'.$index.'.nome')
                    <small class="text-danger fw-bold">{{ $message }}</small>
                    @enderror
                  </div>
                </div>

                <div class="col-md-2">
                  <div class="form-group">
                    <label for="preco_custo_{{ $index }}">
                      <i class="fas fa-dollar-sign"></i> Preço de Custo
                    </label>
                    <input type="text" class="form-control" name="produtos[{{ $index }}][preco_custo]" id="preco_custo_{{ $index }}" value="{{ old('produtos.'.$index.'.preco_custo', number_format($product['preco_custo'], 2, ',', '.')) }}" required oninput="formatCurrency(this); calculateProfit({{ $index }});">
                  </div>
                  @error('produtos.'.$index.'.preco_custo')
                  <small class="text-danger fw-bold">{{ $message }}</small>
                  @enderror
                </div>

                <div class="col-md-2">
                  <div class="form-group">
                    <label for="preco_venda_{{ $index }}">
                      <i class="fas fa-dollar-sign"></i> Preço de Venda
                    </label>
                    <input type="text" class="form-control" name="produtos[{{ $index }}][preco_venda]" id="preco_venda_{{ $index }}" value="{{ old('produtos.'.$index.'.preco_venda', number_format($product['preco_venda'], 2, ',', '.')) }}" required oninput="formatCurrency(this); calculateProfit({{ $index }});">
                  </div>
                  @error('produtos.'.$index.'.preco_venda')
                  <small class="text-danger fw-bold">{{ $message }}</small>
                  @enderror
                </div>

                <div class="col-md-2 d-flex align-items-center">
                  <div class="form-group">
                    <label for="lucro_{{ $index }}">
                      <i class="fas fa-dollar-sign"></i> Lucro
                    </label>
                    <h5 id="lucro_percentual_{{ $index }}" style="margin-top: 2px;">0% Lucro</h5>
                  </div>
                </div>
              </div>

              <div class="divider my-4">
                <div class="divider-text">
                  <i class="bx bx-package"></i> Informações Fiscais
                </div>
              </div>

              <div class="row mb-4">
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="categoria_id_{{ $index }}">
                      <i class="fas fa-list-alt"></i> Categoria
                    </label>
                    <select class="form-select" id="categoria_id_{{ $index }}" name="produtos[{{ $index }}][categoria_id]" required>
                      <option value="" disabled selected>Selecione uma categoria</option>
                      @foreach ($categorias as $categoria)
                      <option value="{{ $categoria->id }}">{{ $categoria->nome }}</option>
                      @endforeach
                    </select>
                    @error('produtos.'.$index.'.categoria_id')
                    <small class="text-danger fw-bold">{{ $message }}</small>
                    @enderror
                  </div>
                </div>

                <div class="col-md-3">
                  <div class="form-group">
                    <label for="codigo_barras_{{ $index }}">
                      <i class="fas fa-barcode"></i> Código de Barras
                    </label>
                    <input type="text" class="form-control" name="produtos[{{ $index }}][codigo_barras]" id="codigo_barras_{{ $index }}" value="{{ old('produtos.'.$index.'.codigo_barras', $product['codigo_barras']) }}" required>
                    @error('produtos.'.$index.'.codigo_barras')
                    <small class="text-danger fw-bold">{{ $message }}</small>
                    @enderror
                  </div>
                </div>

                <div class="col-md-3">
                  <div class="form-group">
                    <label for="ncm_{{ $index }}">
                      <i class="fas fa-barcode"></i> NCM
                    </label>
                    <input type="text" class="form-control" name="produtos[{{ $index }}][ncm]" id="ncm_{{ $index }}" value="{{ old('produtos.'.$index.'.ncm', $product['ncm']) }}" required>
                    @error('produtos.'.$index.'.ncm')
                    <small class="text-danger fw-bold">{{ $message }}</small>
                    @enderror
                  </div>
                </div>

                <div class="col-md-3">
                  <div class="form-group">
                    <label for="cfop_{{ $index }}">
                      <i class="fas fa-barcode"></i> CFOP
                    </label>
                    <input type="text" class="form-control" name="produtos[{{ $index }}][cfop]" id="cfop_{{ $index }}" value="{{ old('produtos.'.$index.'.cfop', $product['cfop']) }}" required>
                    @error('produtos.'.$index.'.cfop')
                    <small class="text-danger fw-bold">{{ $message }}</small>
                    @enderror
                  </div>
                </div>

                <div class="col-md-3">
                  <div class="form-group">
                    <label for="tipo_produto_{{ $index }}">
                      <i class="fas fa-tag"></i> Tipo de Produto
                    </label>
                    <input type="text" class="form-control" name="produtos[{{ $index }}][tipo_produto]" id="tipo_produto_{{ $index }}" value="{{ old('produtos.'.$index.'.tipo_produto', $product['tipo_produto']) }}" required>
                    @error('produtos.'.$index.'.tipo_produto')
                    <small class="text-danger fw-bold">{{ $message }}</small>
                    @enderror
                  </div>
                </div>

                <div class="col-md-3">
                  <div class="form-group">
                    <label for="estoque_{{ $index }}">
                      <i class="fas fa-cubes"></i> Estoque
                    </label>
                    <input type="number" class="form-control" name="produtos[{{ $index }}][estoque]" id="estoque_{{ $index }}" value="{{ old('produtos.'.$index.'.estoque', $product['estoque']) }}" required>
                    @error('produtos.'.$index.'.estoque')
                    <small class="text-danger fw-bold">{{ $message }}</small>
                    @enderror
                  </div>
                </div>
              </div>
              <div class="divider my-6">
                <div class="divider-text">
                  <i class="bx bx-package"></i> Produto
                </div>
              </div>
            @endforeach

            <div class="divider my-6">
              <div class="divider-text">
                <i class="bx bx-package"></i> Informações do Fornecedor
              </div>
            </div>

            <div class="row mb-4">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="fornecedor_cnpj">
                    <i class="fas fa-id-card"></i> CNPJ do Fornecedor
                  </label>
                  <input type="text" class="form-control" name="fornecedor_cnpj" id="fornecedor_cnpj" required placeholder="Ex.: 12.345.678/0001-90" value="{{ old('fornecedor_cnpj', $productsData[0]['fornecedor_cnpj'] ?? '') }}" oninput="formatCNPJ(this)">
                  @error('fornecedor_cnpj')
                  <small class="text-danger fw-bold">{{ $message }}</small>
                  @enderror
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label for="fornecedor_nome">
                    <i class="fas fa-user"></i> Nome do Fornecedor
                  </label>
                  <input type="text" class="form-control" name="fornecedor_nome" id="fornecedor_nome" required placeholder="Nome do fornecedor" value="{{ old('fornecedor_nome', $productsData[0]['fornecedor_nome'] ?? '') }}">
                  @error('fornecedor_nome')
                  <small class="text-danger fw-bold">{{ $message }}</small>
                  @enderror
                </div>
              </div>
            </div>

            <div class="row mb-4">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="fornecedor_telefone">
                    <i class="fas fa-phone"></i> Telefone do Fornecedor
                  </label>
                  <input type="text" class="form-control" name="fornecedor_telefone" id="fornecedor_telefone" required placeholder="Ex.: (11) 91234-5678" value="{{ old('fornecedor_telefone', $productsData[0]['fornecedor_telefone'] ?? '') }}" oninput="formatPhone(this)">
                  @error('fornecedor_telefone')
                  <small class="text-danger fw-bold">{{ $message }}</small>
                  @enderror
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label for="fornecedor_email">
                    <i class="fas fa-envelope"></i> E-mail do Fornecedor
                  </label>
                  <input type="email" class="form-control" name="fornecedor_email" id="fornecedor_email" required placeholder="fornecedor@example.com" value="{{ old('fornecedor_email', $productsData[0]['fornecedor_email'] ?? '') }}">
                  @error('fornecedor_email')
                  <small class="text-danger fw-bold">{{ $message }}</small>
                  @enderror
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-12 text-end">
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-save"></i> Salvar
                </button>
              </div>
            </div>
          @else
            <p>Nenhum produto foi importado ainda.</p>
          @endif
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  function formatCNPJ(input) {
    let value = input.value.replace(/\D/g, ''); // Remove caracteres não numéricos
    value = value.replace(/(\d{2})(\d)/, '$1.$2'); // Adiciona o primeiro ponto
    value = value.replace(/(\d{3})(\d)/, '$1.$2'); // Adiciona o segundo ponto
    value = value.replace(/(\d{3})(\d{1,2})$/, '$1/$2'); // Adiciona a barra
    value = value.replace(/(\d{2})$/, '-$1'); // Adiciona o hífen
    input.value = value; // Atualiza o valor do campo
  }

  function formatPhone(input) {
    let value = input.value.replace(/\D/g, ''); // Remove qualquer caractere que não seja dígito
    value = value.replace(/(\d{2})(\d)/, '($1) $2'); // Formata DDD
    value = value.replace(/(\d{5})(\d)/, '$1-$2'); // Formata telefone
    input.value = value; // Atualiza o valor do campo
  }

  function formatCurrency(input) {
    let value = input.value.replace(/\D/g, ''); // Remove qualquer caractere que não seja dígito
    value = (value / 100).toFixed(2) + ''; // Adiciona casas decimais
    value = value.replace('.', ','); // Troca ponto por vírgula
    value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.'); // Adiciona pontos como separador de milhar
    input.value = value;
  }

  function calculateProfit(index) {
    let precoVenda = parseFloat(document.getElementById('preco_venda_' + index).value.replace(/\./g, '').replace(',', '.')) || 0;
    let precoCusto = parseFloat(document.getElementById('preco_custo_' + index).value.replace(/\./g, '').replace(',', '.')) || 0;

    if (precoCusto > 0) {
      let lucroPercentual = ((precoVenda - precoCusto) / precoCusto) * 100;
      let lucroText = lucroPercentual.toFixed(2) + '% Lucro';

      // Estilo condicional para lucro negativo
      if (lucroPercentual < 0) {
        document.getElementById('lucro_percentual_' + index).style.color = 'red'; // cor de prejuízo
      } else {
        document.getElementById('lucro_percentual_' + index).style.color = 'black'; // cor de lucro
      }

      document.getElementById('lucro_percentual_' + index).innerText = lucroText;
    } else {
      document.getElementById('lucro_percentual_' + index).innerText = "N/A";
    }
  }
</script>
@endsection
