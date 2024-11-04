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
<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="text-primary" style="font-size: 2.5rem; font-weight: bold; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);">
    <i class="fas fa-plus-circle"></i> Cadastro de Produtos
  </h1>
  <form action="{{ route('produtos.import') }}" method="POST" enctype="multipart/form-data" class="d-inline">
    @csrf
    <input type="file" name="xml_file" accept=".xml" required class="d-none" id="importXml">
    <label for="importXml" class="btn btn-primary me-2">
        <i class="fas fa-upload me-1"></i> Importar XML
    </label>
    <button type="submit" class="btn btn-success">
      <i class="fas fa-paper-plane"></i> Enviar XML
    </button>
  </form>
</div>

<div class="card">
  <form action="{{ route('produtos.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="card-body">
      @if(!empty($productsData))
        @foreach($productsData as $index => $product)
          @if($index > 0)
            <div class="divider my-6">
              <div class="divider-text">
                <i class="bx bx-package"></i> Produto {{ $index + 1 }}
              </div>
            </div>
          @endif
          <div class="row g-3 mb-4">
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
                <div class="input-group">
                  <span class="input-group-text">R$</span>
                  <input type="text" class="form-control" name="produtos[{{ $index }}][preco_custo]" id="preco_custo_{{ $index }}" value="{{ old('produtos.'.$index.'.preco_custo', number_format($product['preco_custo'], 2, ',', '.')) }}" required oninput="formatCurrency(this); calculateProfit({{ $index }});">
                </div>
                @error('produtos.'.$index.'.preco_custo')
                <small class="text-danger fw-bold">{{ $message }}</small>
                @enderror
              </div>
            </div>

            <div class="col-md-2">
              <div class="form-group">
                <label for="preco_venda_{{ $index }}">
                  <i class="fas fa-dollar-sign"></i> Preço de Venda
                </label>
                <div class="input-group">
                  <span class="input-group-text">R$</span>
                  <input type="text" class="form-control" name="produtos[{{ $index }}][preco_venda]" id="preco_venda_{{ $index }}" value="{{ old('produtos.'.$index.'.preco_venda', number_format($product['preco_venda'], 2, ',', '.')) }}" required oninput="formatCurrency(this); calculateProfit({{ $index }});">
                </div>
                @error('produtos.'.$index.'.preco_venda')
                <small class="text-danger fw-bold">{{ $message }}</small>
                @enderror
              </div>
            </div>

            <div class="col-md-2 d-flex align-items-center">
              <div class="form-group">
                <label for="lucro_{{ $index }}">
                  <i class="fas fa-percentage"></i> Lucro
                </label>
                <h5 id="lucro_percentual_{{ $index }}" class="mt-1 mb-0" style="font-weight: bold;">0% Lucro</h5>
              </div>
            </div>
          </div>

          <div class="divider my-4">
            <div class="divider-text">
              <i class="bx bx-package"></i> Informações Fiscais
            </div>
          </div>

          <div class="row g-3 mb-4">
            <div class="col-md-4">
              <div class="form-group">
                <label for="categoria_id_{{ $index }}">
                  <i class="fas fa-list-alt"></i> Categoria
                </label>
                <select class="form-select" id="categoria_id_{{ $index }}" name="produtos[{{ $index }}][categoria_id]" required autofocus>
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

            <div class="col-md-4">
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

            <div class="col-md-4">
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

            <div class="col-md-4">
              <div class="form-group">
                <label for="unidade_medida_{{ $index }}">
                  <i class="fas fa-balance-scale"></i> Unidade de Medida
                </label>
                <select class="form-select" id="unidade_medida_{{ $index }}" name="produtos[{{ $index }}][unidade_medida]" required>
                  <option value="" disabled selected>Selecione a unidade</option>
                  <option value="Unidade">Unidade</option>
                  <option value="Kg">Kg</option>
                  <option value="Litro">Litro</option>
                  <option value="Metro">Metro</option>
                  <!-- Adicione outras opções conforme necessário -->
                </select>
                @error('produtos.'.$index.'.unidade_medida')
                <small class="text-danger fw-bold">{{ $message }}</small>
                @enderror
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-group">
                <label for="fabricante_{{ $index }}">
                  <i class="fas fa-industry"></i> Fabricante
                </label>
                <input type="text" class="form-control" name="produtos[{{ $index }}][fabricante]" id="fabricante_{{ $index }}" value="{{ old('produtos.'.$index.'.fabricante') }}" required>
                @error('produtos.'.$index.'.fabricante')
                <small class="text-danger fw-bold">{{ $message }}</small>
                @enderror
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-group">
                <label for="estoque_{{ $index }}">
                  <i class="fas fa-cubes"></i> Estoque
                </label>
                <input type="number" class="form-control" name="produtos[{{ $index }}][estoque]" id="estoque_{{ $index }}" value="{{ old('produtos.'.$index.'.estoque', $product['estoque']) }}" required>
                <input type="hidden" name="usuario_id" value="{{ auth()->user()->id }}">
                @error('produtos.'.$index.'.estoque')
                <small class="text-danger fw-bold">{{ $message }}</small>
                @enderror
              </div>
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
        {{--  <div class="divider my-6">
          <div class="divider-text">
            <i class="bx bx-package"></i> Cadastro Manual de Produtos
          </div>
        </div>  --}}

        <div id="product-container">
          <div class="row g-3 mb-4" id="product_0">
            <div class="col-md-6">
              <div class="form-group">
                <label for="nome_0">
                  <i class="fas fa-tag"></i> Nome do Produto
                </label>
                <input type="text" class="form-control" name="produtos[0][nome]" id="nome_0" required placeholder="Digite o nome do produto">
              </div>
            </div>

            <div class="col-md-2">
              <div class="form-group">
                <label for="preco_custo_0">
                  <i class="fas fa-dollar-sign"></i> Preço de Custo
                </label>
                <div class="input-group">
                  <span class="input-group-text">R$</span>
                  <input type="text" class="form-control" name="produtos[0][preco_custo]" id="preco_custo_0" required placeholder="0,00" oninput="formatCurrency(this); calculateProfit(0);">
                </div>
              </div>
            </div>


          <div class="col-md-2">
              <div class="form-group">
                  <label for="preco_venda_0">
                      <i class="fas fa-dollar-sign"></i> Preço de Venda
                  </label>
                  <input type="number" class="form-control" name="produtos[0][preco_venda]" id="preco_venda_0" required step="0.01">

              </div>
          </div>


          <div class="divider my-4">
            <div class="divider-text">
              <i class="bx bx-package"></i> Informações Fiscais
            </div>
          </div>

          <div class="row g-3 mb-4">
            <div class="col-md-4">
              <div class="form-group">
                <label for="categoria_id_0">
                  <i class="fas fa-list-alt"></i> Categoria
                </label>
                <select class="form-select" id="categoria_id_0" name="produtos[0][categoria_id]" required>
                  <option value="" disabled selected>Selecione uma categoria</option>
                  @foreach ($categorias as $categoria)
                  <option value="{{ $categoria->id }}">{{ $categoria->nome }}</option>
                  @endforeach
                </select>
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-group">
                <label for="codigo_barras_0">
                  <i class="fas fa-barcode"></i> Código de Barras
                </label>
                <input type="text" class="form-control" name="produtos[0][codigo_barras]" id="codigo_barras_0" required placeholder="Digite o código de barras">
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-group">
                <label for="ncm_0">
                  <i class="fas fa-barcode"></i> NCM
                </label>
                <input type="text" class="form-control" name="produtos[0][ncm]" id="ncm_0" required placeholder="Digite o NCM">
              </div>
            </div>
          </div>

          <div class="row g-3 mb-4">
            <div class="col-md-4">
              <div class="form-group">
                <label for="unidade_medida_0">
                  <i class="fas fa-balance-scale"></i> Unidade de Medida
                </label>
                <select class="form-select" id="unidade_medida_0" name="produtos[0][unidade_medida]" required>
                  <option value="" disabled selected>Selecione a unidade</option>
                  <option value="Unidade">Unidade</option>
                  <option value="Kg">Kg</option>
                  <option value="Litro">Litro</option>
                  <option value="Metro">Metro</option>
                </select>
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-group">
                <label for="fabricante_0">
                  <i class="fas fa-industry"></i> Fabricante
                </label>
                <input type="text" class="form-control" name="produtos[0][fabricante]" id="fabricante_0" required placeholder="Digite o nome do fabricante">
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-group">
                <label for="estoque_0">
                  <i class="fas fa-cubes"></i> Estoque
                </label>
                <input type="hidden" name="usuario_id" value="{{ auth()->user()->id }}">
                <input type="number" class="form-control" name="produtos[0][estoque]" id="estoque_0" required placeholder="Quantidade em estoque">
              </div>
            </div>
          </div>
        </div>

        <div class="row mb-4">
          <div class="col-md-12 text-end">
            <button type="button" class="btn btn-secondary me-2" onclick="addProduct()">
              <i class="fas fa-plus-circle"></i> Adicionar Outro Produto
            </button>
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save"></i> Salvar
            </button>
          </div>
        </div>

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
              <input type="text" class="form-control" name="fornecedor_cnpj" id="fornecedor_cnpj" required placeholder="Ex.: 12.345.678/0001-90" oninput="formatCNPJ(this)">
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label for="fornecedor_nome">
                <i class="fas fa-user"></i> Nome do Fornecedor
              </label>
              <input type="text" class="form-control" name="fornecedor_nome" id="fornecedor_nome" required placeholder="Nome do fornecedor">
            </div>
          </div>
        </div>

        <div class="row mb-4">
          <div class="col-md-6">
            <div class="form-group">
              <label for="fornecedor_telefone">
                <i class="fas fa-phone"></i> Telefone do Fornecedor
              </label>
              <input type="text" class="form-control" name="fornecedor_telefone" id="fornecedor_telefone" required placeholder="Ex.: (11) 91234-5678" oninput="formatPhone(this)">
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label for="fornecedor_email">
                <i class="fas fa-envelope"></i> E-mail do Fornecedor
              </label>
              <input type="email" class="form-control" name="fornecedor_email" id="fornecedor_email" required placeholder="fornecedor@example.com">
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

        <script>
          let productCount = 1; // Contador de produtos

          function addProduct() {
            const productContainer = document.getElementById('product-container');

            const productHtml = `
              <div class="row g-3 mb-4" id="product_${productCount}">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="nome_${productCount}">
                      <i class="fas fa-tag"></i> Nome do Produto
                    </label>
                    <input type="text" class="form-control" name="produtos[${productCount}][nome]" id="nome_${productCount}" required placeholder="Digite o nome do produto">
                  </div>
                </div>

                <div class="col-md-2">
                  <div class="form-group">
                      <label for="preco_custo_0">
                          <i class="fas fa-dollar-sign"></i> Preço de Custo
                      </label>
                      <input type="number" class="form-control" name="produtos[0][preco_custo]" id="preco_custo_0" required step="0.01">

                  </div>
              </div>

              <div class="col-md-2">
                  <div class="form-group">
                      <label for="preco_venda_0">
                          <i class="fas fa-dollar-sign"></i> Preço de Venda
                      </label>
                      <input type="number" class="form-control" name="produtos[0][preco_venda]" id="preco_venda_0" required step="0.01">
                      
                  </div>
              </div>


                <div class="col-md-2 d-flex align-items-center">
                  <div class="form-group">
                    <label for="lucro_${productCount}">
                      <i class="fas fa-percentage"></i> Lucro
                    </label>
                    <h5 id="lucro_percentual_${productCount}" class="mt-1 mb-0" style="font-weight: bold;">0% Lucro</h5>
                  </div>
                </div>

                <div class="divider my-4">
                  <div class="divider-text">
                    <i class="bx bx-package"></i> Informações Fiscais
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="form-group">
                    <label for="categoria_id_${productCount}">
                      <i class="fas fa-list-alt"></i> Categoria
                    </label>
                    <select class="form-select" id="categoria_id_${productCount}" name="produtos[${productCount}][categoria_id]" required>
                      <option value="" disabled selected>Selecione uma categoria</option>
                      @foreach ($categorias as $categoria)
                      <option value="{{ $categoria->id }}">{{ $categoria->nome }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="form-group">
                    <label for="codigo_barras_${productCount}">
                      <i class="fas fa-barcode"></i> Código de Barras
                    </label>
                    <input type="text" class="form-control" name="produtos[${productCount}][codigo_barras]" id="codigo_barras_${productCount}" required placeholder="Digite o código de barras">
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="form-group">
                    <label for="ncm_${productCount}">
                      <i class="fas fa-barcode"></i> NCM
                    </label>
                    <input type="text" class="form-control" name="produtos[${productCount}][ncm]" id="ncm_${productCount}" required placeholder="Digite o NCM">
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="form-group">
                    <label for="unidade_medida_${productCount}">
                      <i class="fas fa-balance-scale"></i> Unidade de Medida
                    </label>
                    <select class="form-select" id="unidade_medida_${productCount}" name="produtos[${productCount}][unidade_medida]" required>
                      <option value="" disabled selected>Selecione a unidade</option>
                      <option value="Unidade">Unidade</option>
                      <option value="Kg">Kg</option>
                      <option value="Litro">Litro</option>
                      <option value="Metro">Metro</option>
                    </select>
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="form-group">
                    <label for="fabricante_${productCount}">
                      <i class="fas fa-industry"></i> Fabricante
                    </label>
                    <input type="text" class="form-control" name="produtos[${productCount}][fabricante]" id="fabricante_${productCount}" required placeholder="Digite o nome do fabricante">
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="form-group">
                    <label for="estoque_${productCount}">
                      <i class="fas fa-cubes"></i> Estoque
                    </label>
                    <input type="hidden" name="usuario_id" value="{{ auth()->user()->id }}">
                    <input type="number" class="form-control" name="produtos[${productCount}][estoque]" id="estoque_${productCount}" required placeholder="Quantidade em estoque">
                  </div>
                </div>
              </div>
            `;

            productContainer.insertAdjacentHTML('beforeend', productHtml);
            productCount++; // Incrementa o contador
          }
        </script>

      @endif

    </div>
  </form>
</div>

<style>
  /* Efeito de fade-in nos produtos */
  .fade-in {
    opacity: 0;
    transition: opacity 0.6s ease-in-out;
  }
  .fade-in.show {
    opacity: 1;
  }
  /* Animação para o botão de upload */
  .btn-loading {
    position: relative;
  }
  .btn-loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 1rem;
    height: 1rem;
    margin-left: -0.5rem;
    margin-top: -0.5rem;
    border: 2px solid #fff;
    border-top: 2px solid #000;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
  }
  @keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    setTimeout(() => {
      document.querySelectorAll('.fade-in').forEach(el => el.classList.add('show'));
    }, 100);
  });

  function formatCNPJ(input) {
    let value = input.value.replace(/\D/g, '');
    value = value.replace(/(\d{2})(\d)/, '$1.$2');
    value = value.replace(/(\d{3})(\d)/, '$1.$2');
    value = value.replace(/(\d{3})(\d{1,2})$/, '$1/$2');
    value = value.replace(/(\d{2})$/, '-$1');
    input.value = value;
  }

  function formatCurrency(input) {
    let value = input.value.replace(/\D/g, ''); // Remove caracteres não numéricos
    value = (value / 100).toFixed(2) + ''; // Converte para formato monetário
    input.value = value.replace('.', ','); // Troca ponto por vírgula
}


  function calculateProfit(index) {
    let precoVenda = parseFloat(document.getElementById('preco_venda_' + index).value.replace(/\./g, '').replace(',', '.')) || 0;
    let precoCusto = parseFloat(document.getElementById('preco_custo_' + index).value.replace(/\./g, '').replace(',', '.')) || 0;

    if (precoCusto > 0) {
      let lucroPercentual = ((precoVenda - precoCusto) / precoCusto) * 100;
      let lucroText = lucroPercentual.toFixed(2) + '% Lucro';
      document.getElementById('lucro_percentual_' + index).style.color = lucroPercentual < 0 ? 'red' : 'green';
      document.getElementById('lucro_percentual_' + index).innerText = lucroText;
    } else {
      document.getElementById('lucro_percentual_' + index).innerText = "N/A";
    }
  }
</script>
@endsection
