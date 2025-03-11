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
    <i class="fas fa-edit"></i> Editar Produto
  </h1>
</div>

<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      <form class="needs-validation" action="{{ route('produtos.update', $produto->id) }}" method="POST" enctype="multipart/form-data" novalidate>
        @csrf
        @method('PUT')
        <div class="card-body">
          <div class="row mb-4">
            <div class="col-md-6">
              <div class="form-group">
                <label for="nome" class="form-label">
                  <i class="fas fa-tag"></i> Nome do Produto
                </label>
                <input type="text" class="form-control" name="nome" id="nome" value="{{ $produto->nome }}" placeholder="Digite o nome do produto" required>
                <div class="valid-feedback">Ok!!</div>
                <div class="invalid-feedback">Por favor, insira o nome do produto.</div>
              </div>
            </div>

            <div class="col-md-2">
              <div class="form-group">
                <label for="preco_custo" class="form-label">
                  <i class="fas fa-dollar-sign"></i> Preço de Custo
                </label>
                <input type="text" class="form-control" name="preco_custo" id="preco_custo" value="{{ number_format($produto->preco_custo, 2, ',', '.') }}" placeholder="0,00" required oninput="formatCurrency(this); calculateProfit();">
                <div class="valid-feedback">Ok!!</div>
                <div class="invalid-feedback">Por favor, insira o preço de custo.</div>
              </div>
            </div>

            <div class="col-md-2">
              <div class="form-group">
                <label for="preco_venda" class="form-label">
                  <i class="fas fa-dollar-sign"></i> Preço de Venda
                </label>
                <input type="text" class="form-control" name="preco_venda" id="preco_venda" value="{{ number_format($produto->preco_venda, 2, ',', '.') }}" placeholder="0,00" required oninput="formatCurrency(this); calculateProfit();">
                <div class="valid-feedback">Ok!!</div>
                <div class="invalid-feedback">Por favor, insira o preço de venda.</div>
              </div>
            </div>

            <div class="col-md-2 d-flex align-items-center">
              <div class="form-group">
                <label for="lucro" class="form-label">
                  <i class="fas fa-dollar-sign"></i> Lucro
                </label>
                <h5 id="lucro_percentual" style="margin-top: 2px;">0% Lucro</h5>
              </div>
            </div>
          </div>

          <!-- Linha com a seta para expandir -->
          <div class="row mb-4">
            <div class="col-md-12 text-center">
              <button type="button" class="btn btn-link" onclick="toggleAdditionalFields()">
                <i class="fas fa-chevron-down"></i> Cadastro completo
              </button>
            </div>
          </div>

          <!-- Campos adicionais (inicialmente ocultos) -->
          <div id="additionalFields" style="display: none;">
            <div class="divider my-4">
              <div class="divider-text">
                <i class="bx bx-package"></i> Informações Fiscais
              </div>
            </div>

            <div class="row mb-4">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="categoria_id" class="form-label">
                    <i class="fas fa-list-alt"></i> Categoria
                  </label>
                  <select class="form-select" id="categoria_id" name="categoria_id">
                    <option value="" disabled>Selecione uma categoria</option>
                    @foreach ($categorias as $categoria)
                    <option value="{{ $categoria->id }}" {{ $produto->categoria_id == $categoria->id ? 'selected' : '' }}>{{ $categoria->nome }}</option>
                    @endforeach
                  </select>
                  <div class="valid-feedback">Ok!!</div>
                  <div class="invalid-feedback">Por favor, selecione uma categoria.</div>
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label for="codigo_barras" class="form-label">
                    <i class="fas fa-barcode"></i> Código de Barras
                  </label>
                  <input type="text" class="form-control" name="codigo_barras" id="codigo_barras" value="{{ $produto->codigo_barras }}" placeholder="Digite o código de barras">
                  <div class="valid-feedback">Ok!!</div>
                  <div class="invalid-feedback">Por favor, insira o código de barras.</div>
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label for="ncm" class="form-label">
                    <i class="fas fa-barcode"></i> NCM
                  </label>
                  <input type="text" class="form-control" name="ncm" id="ncm" value="{{ $produto->ncm }}" placeholder="Digite o NCM">
                  <div class="valid-feedback">Ok!!</div>
                  <div class="invalid-feedback">Por favor, insira o NCM.</div>
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label for="estoque" class="form-label">
                    <i class="fas fa-cubes"></i> Estoque
                  </label>
                  <input type="number" class="form-control" name="estoque" id="estoque" value="{{ $produto->estoque }}" placeholder="Digite a quantidade em estoque">
                  <div class="valid-feedback">Ok!!</div>
                  <div class="invalid-feedback">Por favor, insira a quantidade em estoque.</div>
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
                  <label for="fornecedor_cnpj" class="form-label">
                    <i class="fas fa-id-card"></i> CNPJ do Fornecedor
                  </label>
                  <input type="text" class="form-control" name="fornecedor_cnpj" id="fornecedor_cnpj" value="{{ $produto->fornecedor_cnpj }}" placeholder="Ex.: 12.345.678/0001-90" oninput="formatCNPJ(this)">
                  <div class="valid-feedback">Ok!!</div>
                  <div class="invalid-feedback">Por favor, insira um CNPJ válido.</div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label for="fornecedor_nome" class="form-label">
                    <i class="fas fa-user"></i> Nome do Fornecedor
                  </label>
                  <input type="text" class="form-control" name="fornecedor_nome" id="fornecedor_nome" value="{{ $produto->fornecedor_nome }}" placeholder="Digite o nome do fornecedor">
                  <div class="valid-feedback">Ok!!</div>
                  <div class="invalid-feedback">Por favor, insira o nome do fornecedor.</div>
                </div>
              </div>
            </div>

            <div class="row mb-4">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="fornecedor_telefone" class="form-label">
                    <i class="fas fa-phone"></i> Telefone do Fornecedor
                  </label>
                  <input type="text" class="form-control" name="fornecedor_telefone" id="fornecedor_telefone" value="{{ $produto->fornecedor_telefone }}" placeholder="Ex.: (11) 91234-5678" oninput="formatPhone(this)">
                  <div class="valid-feedback">Ok!!</div>
                  <div class="invalid-feedback">Por favor, insira um telefone válido.</div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label for="fornecedor_email" class="form-label">
                    <i class="fas fa-envelope"></i> E-mail do Fornecedor
                  </label>
                  <input type="email" class="form-control" name="fornecedor_email" id="fornecedor_email" value="{{ $produto->fornecedor_email }}" placeholder="Ex.: fornecedor@example.com">
                  <div class="valid-feedback">Ok!!</div>
                  <div class="invalid-feedback">Por favor, insira um e-mail válido.</div>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12 text-end">
              <button type="button" class="btn btn-secondary me-2" onclick="window.history.back();">
                <i class="bx bx-x"></i> Cancelar
              </button>

              <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Salvar Alterações
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>


  <script>
    // Função para formatar CNPJ
    function formatCNPJ(input) {
      let value = input.value.replace(/\D/g, ''); // Remove caracteres não numéricos
      value = value.replace(/(\d{2})(\d)/, '$1.$2'); // Adiciona o primeiro ponto
      value = value.replace(/(\d{3})(\d)/, '$1.$2'); // Adiciona o segundo ponto
      value = value.replace(/(\d{3})(\d{1,2})$/, '$1/$2'); // Adiciona a barra
      value = value.replace(/(\d{2})$/, '-$1'); // Adiciona o hífen
      input.value = value; // Atualiza o valor do campo
    }

    // Função para formatar telefone
    function formatPhone(input) {
      let value = input.value.replace(/\D/g, ''); // Remove qualquer caractere que não seja dígito
      value = value.replace(/(\d{2})(\d)/, '($1) $2'); // Formata DDD
      value = value.replace(/(\d{5})(\d)/, '$1-$2'); // Formata telefone
      input.value = value; // Atualiza o valor do campo
    }

    // Função para formatar moeda
    function formatCurrency(input) {
      let value = input.value.replace(/\D/g, ''); // Remove qualquer caractere que não seja dígito
      value = (value / 100).toFixed(2) + ''; // Adiciona casas decimais
      value = value.replace('.', ','); // Troca ponto por vírgula
      value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.'); // Adiciona pontos como separador de milhar
      input.value = value;
    }

    // Função para calcular o lucro
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

    // Função para alternar a visibilidade dos campos adicionais
    function toggleAdditionalFields() {
      const additionalFields = document.getElementById('additionalFields');
      const toggleButton = document.querySelector('.btn-link i');

      if (additionalFields.style.display === 'none') {
        additionalFields.style.display = 'block';
        toggleButton.classList.remove('fa-chevron-down');
        toggleButton.classList.add('fa-chevron-up');
      } else {
        additionalFields.style.display = 'none';
        toggleButton.classList.remove('fa-chevron-up');
        toggleButton.classList.add('fa-chevron-down');
      }
    }

    // Validação do formulário
    (function () {
      'use strict';

      // Seleciona todos os formulários com a classe .needs-validation
      var forms = document.querySelectorAll('.needs-validation');

      // Itera sobre os formulários e previne o envio se houver campos inválidos
      Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
          if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
          }

          form.classList.add('was-validated');
        }, false);
      });
    })();
  </script>
@endsection
