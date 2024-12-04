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
    <i class="fas fa-edit"></i> Edição de Produtos
  </h1>
</div>

<div class="card">
  <form action="{{ route('produtos.update', $produto->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="card-body">
      <div class="divider my-6">
        <div class="divider-text">
          <i class="bx bx-package"></i> Informações do Produto
        </div>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <div class="form-group">
            <label for="nome">
              <i class="fas fa-tag"></i> Nome do Produto
            </label>
            <input type="text" class="form-control" name="nome" id="nome" value="{{ old('nome', $produto->nome) }}" required>
            @error('nome')
            <small class="text-danger fw-bold">{{ $message }}</small>
            @enderror
          </div>
        </div>

        <div class="col-md-2">
          <div class="form-group">
            <label for="preco_custo">
              <i class="fas fa-dollar-sign"></i> Preço de Custo
            </label>
            <div class="input-group">
              <span class="input-group-text">R$</span>
              <input type="text" class="form-control" name="preco_custo" id="preco_custo" value="{{ old('preco_custo', number_format($produto->preco_custo, 2, ',', '.')) }}" required oninput="formatCurrency(this); calculateProfit(0);">
            </div>
            @error('preco_custo')
            <small class="text-danger fw-bold">{{ $message }}</small>
            @enderror
          </div>
        </div>

        <div class="col-md-2">
          <div class="form-group">
            <label for="preco_venda">
              <i class="fas fa-dollar-sign"></i> Preço de Venda
            </label>
            <div class="input-group">
              <span class="input-group-text">R$</span>
              <input type="text" class="form-control" name="preco_venda" id="preco_venda" value="{{ old('preco_venda', number_format($produto->preco_venda, 2, ',', '.')) }}" required oninput="formatCurrency(this); calculateProfit(0);">
            </div>
            @error('preco_venda')
            <small class="text-danger fw-bold">{{ $message }}</small>
            @enderror
          </div>
        </div>

        <div class="col-md-2 d-flex align-items-center">
          <div class="form-group">
            <label for="lucro">
              <i class="fas fa-percentage"></i> Lucro
            </label>
            <h5 id="lucro_percentual" class="mt-1 mb-0" style="font-weight: bold;">0% Lucro</h5>
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
            <label for="categoria_id">
              <i class="fas fa-list-alt"></i> Categoria
            </label>
            <select class="form-select" id="categoria_id" name="categoria_id" required>
              <option value="" disabled>Selecione uma categoria</option>
              @foreach ($categorias as $categoria)
              <option value="{{ $categoria->id }}" {{ $categoria->id == $produto->categoria_id ? 'selected' : '' }}>{{ $categoria->nome }}</option>
              @endforeach
            </select>
            @error('categoria_id')
            <small class="text-danger fw-bold">{{ $message }}</small>
            @enderror
          </div>
        </div>

        <div class="col-md-4">
          <div class="form-group">
            <label for="codigo_barras">
              <i class="fas fa-barcode"></i> Código de Barras
            </label>
            <input type="text" class="form-control" name="codigo_barras" id="codigo_barras" value="{{ old('codigo_barras', $produto->codigo_barras) }}" required>
            @error('codigo_barras')
            <small class="text-danger fw-bold">{{ $message }}</small>
            @enderror
          </div>
        </div>

        <div class="col-md-4">
          <div class="form-group">
            <label for="ncm">
              <i class="fas fa-barcode"></i> NCM
            </label>
            <input type="text" class="form-control" name="ncm" id="ncm" value="{{ old('ncm', $produto->ncm) }}" required>
            @error('ncm')
            <small class="text-danger fw-bold">{{ $message }}</small>
            @enderror
          </div>
        </div>

        <div class="col-md-4">
          <div class="form-group">
            <label for="unidade_medida">
              <i class="fas fa-balance-scale"></i> Unidade de Medida
            </label>
            <select class="form-select" id="unidade_medida" name="unidade_medida" required>
              <option value="" disabled>Selecione a unidade</option>
              <option value="Unidade" {{ $produto->unidade_medida == 'Unidade' ? 'selected' : '' }}>Unidade</option>
              <option value="Kg" {{ $produto->unidade_medida == 'Kg' ? 'selected' : '' }}>Kg</option>
              <option value="Litro" {{ $produto->unidade_medida == 'Litro' ? 'selected' : '' }}>Litro</option>
              <option value="Metro" {{ $produto->unidade_medida == 'Metro' ? 'selected' : '' }}>Metro</option>
            </select>
            @error('unidade_medida')
            <small class="text-danger fw-bold">{{ $message }}</small>
            @enderror
          </div>
        </div>

        <div class="col-md-4">
          <div class="form-group">
            <label for="fabricante">
              <i class="fas fa-industry"></i> Fabricante
            </label>
            <input type="text" class="form-control" name="fabricante" id="fabricante" value="{{ old('fabricante', $produto->fabricante) }}" required>
            @error('fabricante')
            <small class="text-danger fw-bold">{{ $message }}</small>
            @enderror
          </div>
        </div>

        <div class="col-md-4">
          <div class="form-group">
            <label for="estoque">
              <i class="fas fa-cubes"></i> Estoque
            </label>
            <input type="number" class="form-control" name="estoque" id="estoque" value="{{ old('estoque', $produto->estoque) }}" required>
            @error('estoque')
            <small class="text-danger fw-bold">{{ $message }}</small>
            @enderror
          </div>
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
            <input type="text" class="form-control" name="fornecedor_cnpj" id="fornecedor_cnpj" required placeholder="Ex.: 12.345.678/0001-90" value="{{ old('fornecedor_cnpj', $produto->fornecedor_cnpj) }}" oninput="formatCNPJ(this)">
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
            <input type="text" class="form-control" name="fornecedor_nome" id="fornecedor_nome" required placeholder="Nome do fornecedor" value="{{ old('fornecedor_nome', $produto->fornecedor_nome) }}">
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
            <input type="text" class="form-control" name="fornecedor_telefone" id="fornecedor_telefone" required placeholder="Ex.: (11) 91234-5678" value="{{ old('fornecedor_telefone', $produto->fornecedor_telefone) }}" oninput="formatPhone(this)">
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
            <input type="email" class="form-control" name="fornecedor_email" id="fornecedor_email" required placeholder="fornecedor@example.com" value="{{ old('fornecedor_email', $produto->fornecedor_email) }}">
            @error('fornecedor_email')
            <small class="text-danger fw-bold">{{ $message }}</small>
            @enderror
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-12 text-end">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Atualizar
          </button>
        </div>
      </div>
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
