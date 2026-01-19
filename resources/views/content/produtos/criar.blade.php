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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
  $(document).ready(function() {
    $('.select2').select2({
      placeholder: "Selecione uma ou mais opções",
      allowClear: true,
      width: '100%'
    });

    initCategoriaSugestao(0);

    const nomeInput = document.getElementById('nome_0');
    if (nomeInput) {
      nomeInput.addEventListener('blur', function() {
        const ncmInput = document.getElementById('ncm_0');
        if (ncmInput && !ncmInput.value) {
          buscarNCMPorNome('nome_0', 'ncm_0');
        }
      });
    }
  });

  // Funções de formatação e cálculo
  function formatCurrency(input) {
    let value = input.value.replace(/\D/g, '');
    value = (value / 100).toFixed(2) + '';
    value = value.replace(".", ",");
    value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
    input.value = value;
  }

  function calculateProfit(index) {
    let custo = parseFloat(document.getElementById('preco_custo_' + index).value.replace(/\./g, '').replace(',', '.')) || 0;
    let venda = parseFloat(document.getElementById('preco_venda_' + index).value.replace(/\./g, '').replace(',', '.')) || 0;

    if (custo > 0 && venda > 0) {
      let lucro = ((venda - custo) / custo) * 100;
      document.getElementById('lucro_percentual_' + index).innerText = lucro.toFixed(2) + '% Lucro';
    } else {
      document.getElementById('lucro_percentual_' + index).innerText = '0%';
    }
  }

  // Códigos Adicionais
  let codigoIndex = 0;

  function adicionarCodigo() {
    let tbody = document.querySelector('#tabelaCodigos tbody');
    let tr = document.createElement('tr');
    tr.innerHTML = `
        <td><input type="text" class="form-control" name="produtos[0][codigos_adicionais][${codigoIndex}][codigo]" placeholder="EAN / GTIN"></td>
        <td><input type="text" class="form-control" name="produtos[0][codigos_adicionais][${codigoIndex}][descricao]" placeholder="Ex: Caixa com 12"></td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove()"><i class="fas fa-trash"></i></button></td>
    `;
    tbody.appendChild(tr);
    codigoIndex++;
  }

  // Consulta Fiscal (Mock/API)
  function consultarFiscal(index) {
    let codigo = document.getElementById('codigo_barras_' + index).value;
    if (!codigo) {
      alert('Digite um código de barras para consultar.');
      return;
    }

    // Feedback visual
    let btn = event.currentTarget;
    let originalIcon = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;

    fetch(`/dashboard/produtos/consultar-fiscal/${codigo}`)
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          let d = data.data;
          // Preencher campos
          document.getElementById('nome_' + index).value = d.nome || '';
          document.getElementById('ncm_' + index).value = d.ncm || '';
          document.getElementById('cest_' + index).value = d.cest || '';
          document.getElementById('cfop_interno_' + index).value = d.cfop_interno || '';
          document.getElementById('cfop_externo_' + index).value = d.cfop_externo || '';
          document.getElementById('csosn_icms_' + index).value = d.csosn_icms || '';
          document.getElementById('cst_icms_' + index).value = d.cst_icms || '';
          document.getElementById('aliquota_icms_' + index).value = d.aliquota_icms || 0;
          document.getElementById('aliquota_pis_' + index).value = d.aliquota_pis || 0;
          document.getElementById('aliquota_cofins_' + index).value = d.aliquota_cofins || 0;

          // Trigger intelligent categorization if name is populated
          if (d.nome) {
            sugerirCategoria(index, d.nome);
          }

          alert('Dados fiscais preenchidos com sucesso!');
        } else {
          alert('Produto não encontrado na base fiscal.');
        }
      })
      .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao consultar dados fiscais.');
      })
      .finally(() => {
        btn.innerHTML = originalIcon;
        btn.disabled = false;
      });
  }

  // Intelligent Categorization Logic
  let debounceTimer;

  function initCategoriaSugestao(index) {
    const nomeInput = document.getElementById('nome_' + index);
    if (!nomeInput) return;

    nomeInput.addEventListener('input', function() {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(() => {
        sugerirCategoria(index, this.value);
      }, 500);
    });
  }

  function sugerirCategoria(index, nome) {
    if (nome.length < 3) return;

    fetch(`{{ route('produtos.sugerirCategoria') }}?nome=${encodeURIComponent(nome)}`)
      .then(response => response.json())
      .then(data => {
        const sugestaoBox = document.getElementById('sugestao-categoria-' + index);
        const nomeCategoriaSugerida = document.getElementById('nome-categoria-sugerida-' + index);

        if (data.success && data.categoria_id) {
          // Find category name
          const select = document.getElementById('categoria_id_' + index);
          const option = select.querySelector(`option[value="${data.categoria_id}"]`);

          if (option) {
            // Store the ID in a data attribute on the apply button
            const applyBtn = sugestaoBox.querySelector('a');
            applyBtn.dataset.categoriaId = data.categoria_id;

            nomeCategoriaSugerida.innerText = option.text;
            sugestaoBox.style.display = 'block';
          }
        } else {
          sugestaoBox.style.display = 'none';
        }
      })
      .catch(err => console.error(err));
  }

  function aplicarSugestao(index, event) {
    event.preventDefault();
    const btn = event.currentTarget;
    const categoriaId = btn.dataset.categoriaId;
    const sugestaoBox = document.getElementById('sugestao-categoria-' + index);

    if (categoriaId) {
      const select = document.getElementById('categoria_id_' + index);
      // For Select2/Regular select, trigger change
      $(select).val(categoriaId).trigger('change');
      sugestaoBox.style.display = 'none';
    }
  }
</script>
@endsection

@section('content')
@php
use App\Models\Configuracao;
$gerarCodigoBarras = Configuracao::get('produtos_gerar_codigo_barras', '1') == '1';
@endphp

<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="text-primary" style="font-size: 2.5rem; font-weight: bold; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);">
    <i class="fas fa-plus-circle"></i> Cadastro de Produto
  </h1>
  <div>
    <label for="importXml" class="btn btn-primary" onclick="window.location.href='{{ route('produtos.importar') }}'">
      <i class="fas fa-file-import me-1"></i> Importar XML
    </label>
  </div>
</div>

<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      <div class="card-header p-0">
        <ul class="nav nav-tabs nav-fill" id="produtoTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="geral-tab" data-bs-toggle="tab" data-bs-target="#geral" type="button" role="tab" aria-controls="geral" aria-selected="true">
              <i class="fas fa-info-circle me-1"></i> Geral
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="estoque-tab" data-bs-toggle="tab" data-bs-target="#estoque" type="button" role="tab" aria-controls="estoque" aria-selected="false">
              <i class="fas fa-boxes me-1"></i> Estoque
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="tributacao-tab" data-bs-toggle="tab" data-bs-target="#tributacao" type="button" role="tab" aria-controls="tributacao" aria-selected="false">
              <i class="fas fa-file-invoice-dollar me-1"></i> Tributação
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="fornecedores-tab" data-bs-toggle="tab" data-bs-target="#fornecedores" type="button" role="tab" aria-controls="fornecedores" aria-selected="false">
              <i class="fas fa-truck me-1"></i> Fornecedores
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="codigos-tab" data-bs-toggle="tab" data-bs-target="#codigos" type="button" role="tab" aria-controls="codigos" aria-selected="false">
              <i class="fas fa-barcode me-1"></i> Códigos
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="outros-tab" data-bs-toggle="tab" data-bs-target="#outros" type="button" role="tab" aria-controls="outros" aria-selected="false">
              <i class="fas fa-ellipsis-h me-1"></i> Outros
            </button>
          </li>
        </ul>
      </div>

      <form class="needs-validation" action="{{ route('produtos.store') }}" method="POST" enctype="multipart/form-data" novalidate>
        @csrf
        <!-- Campo oculto para o usuario_id -->
        <input type="hidden" name="produtos[0][usuario_id]" value="{{ auth()->id() }}">

        <div class="card-body">
          <div class="tab-content pt-4" id="produtoTabsContent">

            <!-- ABA GERAL -->
            <div class="tab-pane fade show active" id="geral" role="tabpanel" aria-labelledby="geral-tab">
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="nome_0" class="form-label">Nome do Produto *</label>
                  <input type="text" class="form-control form-control-lg" name="produtos[0][nome]" id="nome_0" placeholder="Ex: Teclado Gamer USB" required>
                  <div class="invalid-feedback">O nome é obrigatório.</div>
                  <div id="sugestao-categoria-0" class="form-text text-info" style="display: none;">
                    <i class="fas fa-magic"></i> Sugestão: <span id="nome-categoria-sugerida-0"></span>
                    <a href="#" onclick="aplicarSugestao(0, event)" class="fw-bold">Aplicar</a>
                  </div>
                </div>
                <div class="col-md-3">
                  <label for="codigo_barras_0" class="form-label">Código de Barras (Principal)</label>
                  <div class="input-group">
                    <input type="text" class="form-control" name="produtos[0][codigo_barras]" id="codigo_barras_0" placeholder="{{ $gerarCodigoBarras ? 'Gerado Autom.' : 'EAN-13' }}">
                    <button class="btn btn-outline-primary" type="button" onclick="consultarFiscal(0)" title="Buscar na API">
                      <i class="fas fa-search"></i>
                    </button>
                  </div>
                </div>
                <div class="col-md-3">
                  <label class="form-label">Status</label>
                  <div class="form-check form-switch mt-2">
                    <input class="form-check-input" type="checkbox" id="ativo_0" name="produtos[0][ativo]" value="1" checked>
                    <label class="form-check-label" for="ativo_0">Produto Ativo</label>
                  </div>
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-3">
                  <label for="preco_custo_0" class="form-label">Preço de Custo *</label>
                  <div class="input-group">
                    <span class="input-group-text">R$</span>
                    <input type="text" class="form-control" name="produtos[0][preco_custo]" id="preco_custo_0" placeholder="0,00" required oninput="formatCurrency(this); calculateProfit(0);">
                  </div>
                </div>
                <div class="col-md-3">
                  <label for="preco_venda_0" class="form-label">Preço de Venda *</label>
                  <div class="input-group">
                    <span class="input-group-text">R$</span>
                    <input type="text" class="form-control" name="produtos[0][preco_venda]" id="preco_venda_0" placeholder="0,00" required oninput="formatCurrency(this); calculateProfit(0);">
                  </div>
                </div>
                <div class="col-md-3">
                  <label class="form-label">Margem de Lucro</label>
                  <h4 class="text-success mt-1" id="lucro_percentual_0">0%</h4>
                </div>
                <div class="col-md-3">
                  <label for="categoria_id_0" class="form-label">Categoria</label>
                  <select class="form-select" name="produtos[0][categoria_id]" id="categoria_id_0">
                    @foreach ($categorias as $categoria)
                    <option value="{{ $categoria->id }}">{{ $categoria->nome }}</option>
                    @endforeach
                  </select>
                </div>
              </div>

              <div class="row">
                <div class="col-md-4">
                  <label for="tipo_item_0" class="form-label">Tipo do Item</label>
                  <select class="form-select" name="produtos[0][tipo_item]" id="tipo_item_0">
                    <option value="00" selected>00 - Mercadoria para Revenda</option>
                    <option value="01">01 - Matéria-Prima</option>
                    <option value="02">02 - Embalagem</option>
                    <option value="03">03 - Produto em Processo</option>
                    <option value="04">04 - Produto Acabado</option>
                    <option value="05">05 - Subproduto</option>
                    <option value="06">06 - Produto Intermediário</option>
                    <option value="07">07 - Material de Uso e Consumo</option>
                    <option value="08">08 - Ativo Imobilizado</option>
                    <option value="09">09 - Serviços</option>
                    <option value="99">99 - Outros</option>
                  </select>
                </div>
              </div>
            </div>

            <!-- ABA ESTOQUE -->
            <div class="tab-pane fade" id="estoque" role="tabpanel" aria-labelledby="estoque-tab">
              <div class="row mb-3">
                <div class="col-md-3">
                  <label for="estoque_0" class="form-label">Estoque Atual</label>
                  <input type="number" class="form-control" name="produtos[0][estoque]" id="estoque_0" value="0">
                </div>
                <div class="col-md-3">
                  <label for="estoque_minimo_0" class="form-label">Estoque Mínimo</label>
                  <input type="number" class="form-control" name="produtos[0][estoque_minimo]" id="estoque_minimo_0" value="0">
                </div>
                <div class="col-md-3">
                  <label for="estoque_maximo_0" class="form-label">Estoque Máximo</label>
                  <input type="number" class="form-control" name="produtos[0][estoque_maximo]" id="estoque_maximo_0">
                </div>
                <div class="col-md-3">
                  <label for="localizacao_0" class="form-label">Localização</label>
                  <input type="text" class="form-control" name="produtos[0][localizacao]" id="localizacao_0" placeholder="Corredor A, Prateleira 2">
                </div>
              </div>
            </div>

            <!-- ABA TRIBUTAÇÃO -->
            <div class="tab-pane fade" id="tributacao" role="tabpanel" aria-labelledby="tributacao-tab">
              <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Use o botão de busca na aba "Geral" (ao lado do código de barras) para preencher automaticamente.
              </div>
              <div class="row mb-3">
                <div class="col-md-3">
                  <label class="form-label">NCM</label>
                  <div class="input-group">
                    <input type="text" class="form-control" name="produtos[0][ncm]" id="ncm_0">
                    <button class="btn btn-outline-secondary" type="button" onclick="buscarNCMPorNome('nome_0', 'ncm_0')" title="Buscar NCM por nome">
                        <i class="fas fa-search"></i>
                    </button>
                  </div>
                </div>
                <div class="col-md-3">
                  <label class="form-label">Unid. Comercial</label>
                  <input type="text" class="form-control" name="produtos[0][unidade_comercial]" id="unidade_comercial_0" value="UN" maxlength="6">
                </div>
                <div class="col-md-3">
                  <label class="form-label">CEST</label>
                  <input type="text" class="form-control" name="produtos[0][cest]" id="cest_0">
                </div>
                <div class="col-md-3">
                  <label class="form-label">CFOP Interno</label>
                  <input type="text" class="form-control" name="produtos[0][cfop_interno]" id="cfop_interno_0" placeholder="5102">
                </div>
                <div class="col-md-3">
                  <label class="form-label">CFOP Externo</label>
                  <input type="text" class="form-control" name="produtos[0][cfop_externo]" id="cfop_externo_0" placeholder="6102">
                </div>
              </div>
              <div class="row mb-3">
                <div class="col-md-3">
                  <label class="form-label">Origem</label>
                  <select class="form-select" name="produtos[0][origem]" id="origem_0">
                    <option value="0">0 - Nacional</option>
                    <option value="1">1 - Estrangeira (Imp. Direta)</option>
                    <option value="2">2 - Estrangeira (Adq. no Int.)</option>
                  </select>
                </div>
                <div class="col-md-3">
                  <label class="form-label">CSOSN (Simples)</label>
                  <input type="text" class="form-control" name="produtos[0][csosn_icms]" id="csosn_icms_0" placeholder="102">
                </div>
                <div class="col-md-3">
                  <label class="form-label">CST ICMS</label>
                  <input type="text" class="form-control" name="produtos[0][cst_icms]" id="cst_icms_0" placeholder="00">
                </div>
              </div>
              <div class="row mb-3">
                <div class="col-md-4">
                  <label class="form-label">Alíquota ICMS (%)</label>
                  <input type="number" step="0.01" class="form-control" name="produtos[0][aliquota_icms]" id="aliquota_icms_0" value="0.00">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Alíquota PIS (%)</label>
                  <input type="number" step="0.01" class="form-control" name="produtos[0][aliquota_pis]" id="aliquota_pis_0" value="0.00">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Alíquota COFINS (%)</label>
                  <input type="number" step="0.01" class="form-control" name="produtos[0][aliquota_cofins]" id="aliquota_cofins_0" value="0.00">
                </div>
              </div>
            </div>

            <!-- ABA FORNECEDORES -->
            <div class="tab-pane fade" id="fornecedores" role="tabpanel" aria-labelledby="fornecedores-tab">
              <div class="row">
                <div class="col-md-12">
                  <label class="form-label">Selecione os Fornecedores deste produto</label>
                  <select class="select2 form-select" name="produtos[0][fornecedores][]" multiple>
                    @foreach($fornecedores as $fornecedor)
                    <option value="{{ $fornecedor->id }}">{{ $fornecedor->nome }} ({{ $fornecedor->cnpj ?? 'S/ CNPJ' }})</option>
                    @endforeach
                  </select>
                  <small class="text-muted">Você pode selecionar múltiplos fornecedores.</small>
                </div>
              </div>
            </div>

            <!-- ABA CÓDIGOS ADICIONAIS -->
            <div class="tab-pane fade" id="codigos" role="tabpanel" aria-labelledby="codigos-tab">
              <div class="table-responsive">
                <table class="table table-bordered" id="tabelaCodigos">
                  <thead>
                    <tr>
                      <th>Código de Barras</th>
                      <th>Descrição (Ex: Caixa, Unidade)</th>
                      <th style="width: 50px;">Ação</th>
                    </tr>
                  </thead>
                  <tbody>
                    <!-- Linhas adicionadas via JS -->
                  </tbody>
                </table>
              </div>
              <button type="button" class="btn btn-outline-primary mt-2" onclick="adicionarCodigo()">
                <i class="fas fa-plus"></i> Adicionar Código
              </button>
            </div>

            <!-- ABA OUTROS -->
            <div class="tab-pane fade" id="outros" role="tabpanel" aria-labelledby="outros-tab">
              <div class="row mb-3">
                <div class="col-md-12">
                  <h5>Dimensões e Peso (Para Frete)</h5>
                </div>
                <div class="col-md-2">
                  <label class="form-label">Peso Líq. (kg)</label>
                  <input type="number" step="0.001" class="form-control" name="produtos[0][peso_liquido]">
                </div>
                <div class="col-md-2">
                  <label class="form-label">Peso Bruto (kg)</label>
                  <input type="number" step="0.001" class="form-control" name="produtos[0][peso_bruto]">
                </div>
                <div class="col-md-2">
                  <label class="form-label">Largura (cm)</label>
                  <input type="number" step="0.01" class="form-control" name="produtos[0][largura]">
                </div>
                <div class="col-md-2">
                  <label class="form-label">Altura (cm)</label>
                  <input type="number" step="0.01" class="form-control" name="produtos[0][altura]">
                </div>
                <div class="col-md-2">
                  <label class="form-label">Comprimento (cm)</label>
                  <input type="number" step="0.01" class="form-control" name="produtos[0][comprimento]">
                </div>
              </div>
              <hr>
              <div class="row mb-3">
                <div class="col-md-12">
                  <h5>Preços Avançados</h5>
                </div>
                <div class="col-md-3">
                  <label class="form-label">Preço Atacado</label>
                  <input type="text" class="form-control" name="produtos[0][preco_atacado]" oninput="formatCurrency(this)">
                </div>
                <div class="col-md-3">
                  <label class="form-label">Qtd. Mín. Atacado</label>
                  <input type="number" class="form-control" name="produtos[0][qtd_min_atacado]">
                </div>
                <div class="col-md-3">
                  <label class="form-label">Preço Promocional</label>
                  <input type="text" class="form-control" name="produtos[0][preco_promocional]" oninput="formatCurrency(this)">
                </div>
              </div>
              <div class="row">
                <div class="col-md-12">
                  <label class="form-label">Observações Internas</label>
                  <textarea class="form-control" name="produtos[0][observacoes_internas]" rows="3"></textarea>
                </div>
              </div>
            </div>

          </div> <!-- End Tab Content -->

          <div class="mt-4 text-end">
            <button type="submit" class="btn btn-primary btn-lg">
              <i class="fas fa-save me-1"></i> Salvar Produto
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection
