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
          @include('components.produtos.product-form', ['product' => $product, 'index' => $index, 'categorias' => $categorias])
        @endforeach
      @else
        <div id="product-container">
          @include('components.produtos.product-form', ['product' => [], 'index' => 0, 'categorias' => $categorias])
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
      @endif
    </div>
  </form>
</div>

@include('components.produtos.supplier-form', ['supplierData' => $productsData[0] ?? []])

<style>
  /* Responsividade garantida */
  .form-group {
    margin-bottom: 1rem;
  }
  .fade-in {
    opacity: 0;
    transition: opacity 0.6s ease-in-out;
  }
  .fade-in.show {
    opacity: 1;
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    setTimeout(() => {
      document.querySelectorAll('.fade-in').forEach(el => el.classList.add('show'));
    }, 100);
  });

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

  let productCount = 1; // Contador de produtos

  function addProduct() {
    const productContainer = document.getElementById('product-container');

    const newProductHtml = `
      <div class="row g-3 mb-4 fade-in" id="product_${productCount}">
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
            <label for="preco_custo_${productCount}">
              <i class="fas fa-dollar-sign"></i> Preço de Custo
            </label>
            <div class="input-group">
              <span class="input-group-text">R$</span>
              <input type="text" class="form-control" name="produtos[${productCount}][preco_custo]" id="preco_custo_${productCount}" required oninput="formatCurrency(this); calculateProfit(${productCount});">
            </div>
          </div>
        </div>
        <div class="col-md-2">
          <div class="form-group">
            <label for="preco_venda_${productCount}">
              <i class="fas fa-dollar-sign"></i> Preço de Venda
            </label>
            <div class="input-group">
              <span class="input-group-text">R$</span>
              <input type="text" class="form-control" name="produtos[${productCount}][preco_venda]" id="preco_venda_${productCount}" required oninput="formatCurrency(this); calculateProfit(${productCount});">
            </div>
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
      </div>
    `;

    productContainer.insertAdjacentHTML('beforeend', newProductHtml);
    productCount++;
  }
</script>
@endsection
