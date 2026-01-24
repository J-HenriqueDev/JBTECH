@extends('layouts.layoutMaster')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/swiper/swiper.scss'])
@endsection

@php
    $metodoLucro = \App\Models\Configuracao::get('produtos_metodo_lucro', 'markup');
    $exibirLucroValor = \App\Models\Configuracao::get('produtos_exibir_lucro_valor', '0') == '1';
@endphp

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/swiper/swiper.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/forms-selects.js'])
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Verifica se jQuery está carregado
            if (typeof $ === 'undefined') {
                console.error('jQuery não está carregado!');
                return;
            }

            // Inicializa Select2
            $('.select2').select2({
                placeholder: "Selecione uma ou mais opções",
                allowClear: true,
                width: '100%'
            });

            calculateProfit();
            initCategoriaSugestao();
        });

        // Funções de formatação e cálculo
        function formatCurrency(input) {
            let value = input.value.replace(/\D/g, '');
            value = (value / 100).toFixed(2) + '';
            value = value.replace(".", ",");
            value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
            input.value = value;
        }

        const metodoLucro = "{{ $metodoLucro }}";
        const exibirLucroValor = {{ $exibirLucroValor ? 'true' : 'false' }};

        function calculateProfit() {
            const custoEl = document.getElementById('preco_custo');
            const vendaEl = document.getElementById('preco_venda');

            if (!custoEl || !vendaEl) return;

            let custo = parseFloat(custoEl.value.replace(/\./g, '').replace(',', '.')) || 0;
            let venda = parseFloat(vendaEl.value.replace(/\./g, '').replace(',', '.')) || 0;

            if (custo > 0 && venda > 0) {
                let lucro;
                let valorLucro = venda - custo;
                let isPrejuizo = valorLucro < 0;

                if (metodoLucro === 'margem') {
                    // Margem = (Venda - Custo) / Venda
                    lucro = ((venda - custo) / venda) * 100;
                } else {
                    // Markup = (Venda - Custo) / Custo
                    lucro = ((venda - custo) / custo) * 100;
                }

                let textoLucro = lucro.toFixed(2) + '%';

                if (exibirLucroValor) {
                    textoLucro += ' <span style="font-size: 0.7em;">(R$ ' + valorLucro.toLocaleString(
                        'pt-BR', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }) + ')</span>';
                }

                let el = document.getElementById('lucro_percentual');
                el.innerHTML = textoLucro;
                el.classList.remove('text-success', 'text-danger');
                el.classList.add(isPrejuizo ? 'text-danger' : 'text-success');
                el.style.setProperty('color', isPrejuizo ? '#ff3e1d' : '#71dd37', 'important');
            } else {
                let el = document.getElementById('lucro_percentual');
                el.innerText = '0%';
                el.classList.remove('text-danger');
                el.classList.add('text-success');
                el.style.setProperty('color', '#71dd37', 'important');
            }
        }

        // Códigos Adicionais
        let codigoIndex = {{ $produto->codigosAdicionais->count() }};

        function adicionarCodigo() {
            let tbody = document.querySelector('#tabelaCodigos tbody');
            let tr = document.createElement('tr');
            tr.innerHTML = `
        <td><input type="text" class="form-control" name="codigos_adicionais[${codigoIndex}][codigo]" placeholder="EAN / GTIN"></td>
        <td><input type="text" class="form-control" name="codigos_adicionais[${codigoIndex}][descricao]" placeholder="Ex: Caixa com 12"></td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove()"><i class="fas fa-trash"></i></button></td>
    `;
            tbody.appendChild(tr);
            codigoIndex++;
        }

        // Consulta Fiscal
        function consultarFiscal() {
            let codigo = document.getElementById('codigo_barras').value;
            if (!codigo) {
                alert('Digite um código de barras para consultar.');
                return;
            }

            let btn = event.currentTarget;
            let originalIcon = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            btn.disabled = true;

            fetch(`/dashboard/produtos/consultar-fiscal/${codigo}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let d = data.data;
                        document.getElementById('nome').value = d.nome || '';
                        document.getElementById('ncm').value = d.ncm || '';
                        document.getElementById('cest').value = d.cest || '';
                        document.getElementById('cfop_interno').value = d.cfop_interno || '';
                        document.getElementById('cfop_externo').value = d.cfop_externo || '';
                        document.getElementById('csosn_icms').value = d.csosn_icms || '';
                        document.getElementById('cst_icms').value = d.cst_icms || '';
                        document.getElementById('aliquota_icms').value = d.aliquota_icms || 0;
                        document.getElementById('aliquota_pis').value = d.aliquota_pis || 0;
                        document.getElementById('aliquota_cofins').value = d.aliquota_cofins || 0;

                        if (d.nome) {
                            sugerirCategoria(d.nome);
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

        function initCategoriaSugestao() {
            const nomeInput = document.getElementById('nome');
            if (!nomeInput) return;

            nomeInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    sugerirCategoria(this.value);
                }, 500);
            });

            const aplicarSugestaoBtn = document.getElementById('aplicar-sugestao');
            if (aplicarSugestaoBtn) {
                aplicarSugestaoBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    aplicarSugestao();
                });
            }
        }

        function sugerirCategoria(nome) {
            if (nome.length < 3) return;

            const sugestaoBox = document.getElementById('sugestao-categoria');
            const nomeCategoriaSugerida = document.getElementById('nome-categoria-sugerida');
            const aplicarSugestaoBtn = document.getElementById('aplicar-sugestao');

            fetch(`{{ route('produtos.sugerirCategoria') }}?nome=${encodeURIComponent(nome)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.categoria_id) {
                        const select = document.getElementById('categoria_id');
                        const option = select.querySelector(`option[value="${data.categoria_id}"]`);

                        if (option) {
                            aplicarSugestaoBtn.dataset.categoriaId = data.categoria_id;
                            nomeCategoriaSugerida.innerText = option.text;
                            sugestaoBox.style.display = 'block';
                        }
                    } else {
                        sugestaoBox.style.display = 'none';
                    }
                })
                .catch(err => console.error(err));
        }

        function aplicarSugestao() {
            const aplicarSugestaoBtn = document.getElementById('aplicar-sugestao');
            const categoriaId = aplicarSugestaoBtn.dataset.categoriaId;
            const sugestaoBox = document.getElementById('sugestao-categoria');

            if (categoriaId) {
                const select = document.getElementById('categoria_id');
                $(select).val(categoriaId).trigger('change');
                sugestaoBox.style.display = 'none';
            }
        }

        function buscarNcm() {
            buscarNCMPorNome('nome', 'ncm');
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
            <i class="fas fa-edit"></i> Editar Produto
        </h1>
        <div>
            <a href="{{ route('produtos.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Voltar
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header p-0">
                    <ul class="nav nav-tabs nav-fill" id="produtoTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="geral-tab" data-bs-toggle="tab" data-bs-target="#geral"
                                type="button" role="tab" aria-controls="geral" aria-selected="true">
                                <i class="fas fa-info-circle me-1"></i> Geral
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="estoque-tab" data-bs-toggle="tab" data-bs-target="#estoque"
                                type="button" role="tab" aria-controls="estoque" aria-selected="false">
                                <i class="fas fa-boxes me-1"></i> Estoque
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tributacao-tab" data-bs-toggle="tab" data-bs-target="#tributacao"
                                type="button" role="tab" aria-controls="tributacao" aria-selected="false">
                                <i class="fas fa-file-invoice-dollar me-1"></i> Tributação
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="fornecedores-tab" data-bs-toggle="tab"
                                data-bs-target="#fornecedores" type="button" role="tab" aria-controls="fornecedores"
                                aria-selected="false">
                                <i class="fas fa-truck me-1"></i> Fornecedores
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="codigos-tab" data-bs-toggle="tab" data-bs-target="#codigos"
                                type="button" role="tab" aria-controls="codigos" aria-selected="false">
                                <i class="fas fa-barcode me-1"></i> Códigos
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="outros-tab" data-bs-toggle="tab" data-bs-target="#outros"
                                type="button" role="tab" aria-controls="outros" aria-selected="false">
                                <i class="fas fa-ellipsis-h me-1"></i> Outros
                            </button>
                        </li>
                    </ul>
                </div>

                <form class="needs-validation" action="{{ route('produtos.update', $produto->id) }}" method="POST"
                    enctype="multipart/form-data" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="card-body">
                        <div class="tab-content pt-4" id="produtoTabsContent">

                            <!-- ABA GERAL -->
                            <div class="tab-pane fade show active" id="geral" role="tabpanel"
                                aria-labelledby="geral-tab">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="nome" class="form-label">Nome do Produto *</label>
                                        <input type="text" class="form-control form-control-lg" name="nome"
                                            id="nome" value="{{ old('nome', $produto->nome) }}"
                                            placeholder="Ex: Teclado Gamer USB" required>
                                        <div class="invalid-feedback">O nome é obrigatório.</div>
                                        <div id="sugestao-categoria" class="form-text text-info" style="display: none;">
                                            <i class="fas fa-magic"></i> Sugestão: <span
                                                id="nome-categoria-sugerida"></span>
                                            <a href="#" id="aplicar-sugestao" class="fw-bold">Aplicar</a>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="codigo_barras" class="form-label">Código de Barras (Principal)</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="codigo_barras"
                                                id="codigo_barras"
                                                value="{{ old('codigo_barras', $produto->codigo_barras) }}"
                                                placeholder="{{ $gerarCodigoBarras ? 'Gerado Autom.' : 'EAN-13' }}">
                                            <button class="btn btn-outline-primary" type="button"
                                                onclick="consultarFiscal()" title="Buscar na API">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Status</label>
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" type="checkbox" id="ativo"
                                                name="ativo" value="1"
                                                {{ old('ativo', $produto->ativo) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="ativo">Produto Ativo</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label for="preco_custo" class="form-label">Preço de Custo *</label>
                                        <div class="input-group">
                                            <span class="input-group-text">R$</span>
                                            <input type="text" class="form-control" name="preco_custo"
                                                id="preco_custo"
                                                value="{{ number_format($produto->preco_custo, 2, ',', '.') }}"
                                                placeholder="0,00" required
                                                oninput="formatCurrency(this); calculateProfit();">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="preco_venda" class="form-label">Preço de Venda *</label>
                                        <div class="input-group">
                                            <span class="input-group-text">R$</span>
                                            <input type="text" class="form-control" name="preco_venda"
                                                id="preco_venda"
                                                value="{{ number_format($produto->preco_venda, 2, ',', '.') }}"
                                                placeholder="0,00" required
                                                oninput="formatCurrency(this); calculateProfit();">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Margem de Lucro</label>
                                        <h4 class="text-success mt-1" id="lucro_percentual">0%</h4>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="categoria_id" class="form-label">Categoria</label>
                                        <select class="form-select" name="categoria_id" id="categoria_id">
                                            <option value="">Selecione...</option>
                                            @foreach ($categorias as $categoria)
                                                <option value="{{ $categoria->id }}"
                                                    {{ old('categoria_id', $produto->categoria_id) == $categoria->id ? 'selected' : '' }}>
                                                    {{ $categoria->nome }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <label for="tipo_item" class="form-label">Tipo do Item</label>
                                        <select class="form-select" name="tipo_item" id="tipo_item">
                                            @foreach (['00' => '00 - Mercadoria para Revenda', '01' => '01 - Matéria-Prima', '02' => '02 - Embalagem', '03' => '03 - Produto em Processo', '04' => '04 - Produto Acabado', '05' => '05 - Subproduto', '06' => '06 - Produto Intermediário', '07' => '07 - Material de Uso e Consumo', '08' => '08 - Ativo Imobilizado', '09' => '09 - Serviços', '99' => '99 - Outros'] as $key => $label)
                                                <option value="{{ $key }}"
                                                    {{ old('tipo_item', $produto->tipo_item) == $key ? 'selected' : '' }}>
                                                    {{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- ABA ESTOQUE -->
                            <div class="tab-pane fade" id="estoque" role="tabpanel" aria-labelledby="estoque-tab">
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label for="estoque" class="form-label">Estoque Atual</label>
                                        <input type="number" class="form-control" name="estoque" id="estoque"
                                            value="{{ old('estoque', $produto->estoque) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="estoque_minimo" class="form-label">Estoque Mínimo</label>
                                        <input type="number" class="form-control" name="estoque_minimo"
                                            id="estoque_minimo"
                                            value="{{ old('estoque_minimo', $produto->estoque_minimo) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="estoque_maximo" class="form-label">Estoque Máximo</label>
                                        <input type="number" class="form-control" name="estoque_maximo"
                                            id="estoque_maximo"
                                            value="{{ old('estoque_maximo', $produto->estoque_maximo) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="localizacao" class="form-label">Localização</label>
                                        <input type="text" class="form-control" name="localizacao" id="localizacao"
                                            value="{{ old('localizacao', $produto->localizacao) }}"
                                            placeholder="Corredor A, Prateleira 2">
                                    </div>
                                </div>
                            </div>

                            <!-- ABA TRIBUTAÇÃO -->
                            <div class="tab-pane fade" id="tributacao" role="tabpanel" aria-labelledby="tributacao-tab">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Use o botão de busca na aba "Geral" (ao lado do
                                    código de barras) para preencher automaticamente.
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label class="form-label">NCM</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="ncm" id="ncm"
                                                value="{{ old('ncm', $produto->ncm) }}">
                                            <button class="btn btn-outline-secondary" type="button"
                                                onclick="buscarNcm()" title="Buscar NCM Online">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Unid. Comercial</label>
                                        <input type="text" class="form-control" name="unidade_comercial"
                                            id="unidade_comercial"
                                            value="{{ old('unidade_comercial', $produto->unidade_comercial ?? 'UN') }}"
                                            maxlength="6">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">CEST</label>
                                        <input type="text" class="form-control" name="cest" id="cest"
                                            value="{{ old('cest', $produto->cest) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">CFOP Interno</label>
                                        <input type="text" class="form-control" name="cfop_interno" id="cfop_interno"
                                            value="{{ old('cfop_interno', $produto->cfop_interno) }}" placeholder="5102">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">CFOP Externo</label>
                                        <input type="text" class="form-control" name="cfop_externo" id="cfop_externo"
                                            value="{{ old('cfop_externo', $produto->cfop_externo) }}" placeholder="6102">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Origem</label>
                                        <select class="form-select" name="origem" id="origem">
                                            <option value="0"
                                                {{ old('origem', $produto->origem) == '0' ? 'selected' : '' }}>0 - Nacional
                                            </option>
                                            <option value="1"
                                                {{ old('origem', $produto->origem) == '1' ? 'selected' : '' }}>1 -
                                                Estrangeira (Imp. Direta)</option>
                                            <option value="2"
                                                {{ old('origem', $produto->origem) == '2' ? 'selected' : '' }}>2 -
                                                Estrangeira (Adq. no Int.)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">CSOSN (Simples)</label>
                                        <input type="text" class="form-control" name="csosn_icms" id="csosn_icms"
                                            value="{{ old('csosn_icms', $produto->csosn_icms) }}" placeholder="102">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">CST ICMS</label>
                                        <input type="text" class="form-control" name="cst_icms" id="cst_icms"
                                            value="{{ old('cst_icms', $produto->cst_icms) }}" placeholder="00">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Alíquota ICMS (%)</label>
                                        <input type="number" step="0.01" class="form-control" name="aliquota_icms"
                                            id="aliquota_icms"
                                            value="{{ old('aliquota_icms', $produto->aliquota_icms) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Alíquota PIS (%)</label>
                                        <input type="number" step="0.01" class="form-control" name="aliquota_pis"
                                            id="aliquota_pis" value="{{ old('aliquota_pis', $produto->aliquota_pis) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Alíquota COFINS (%)</label>
                                        <input type="number" step="0.01" class="form-control" name="aliquota_cofins"
                                            id="aliquota_cofins"
                                            value="{{ old('aliquota_cofins', $produto->aliquota_cofins) }}">
                                    </div>
                                </div>
                            </div>

                            <!-- ABA FORNECEDORES -->
                            <div class="tab-pane fade" id="fornecedores" role="tabpanel"
                                aria-labelledby="fornecedores-tab">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label class="form-label">Selecione os Fornecedores deste produto</label>
                                        <select class="select2 form-select" name="fornecedores[]" multiple>
                                            @php
                                                $fornecedoresSelecionados = $produto->fornecedores
                                                    ->pluck('id')
                                                    ->toArray();
                                            @endphp
                                            @foreach ($fornecedores as $fornecedor)
                                                <option value="{{ $fornecedor->id }}"
                                                    {{ in_array($fornecedor->id, $fornecedoresSelecionados) ? 'selected' : '' }}>
                                                    {{ $fornecedor->nome }} ({{ $fornecedor->cnpj ?? 'S/ CNPJ' }})
                                                </option>
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
                                            @foreach ($produto->codigosAdicionais as $index => $codigo)
                                                <tr>
                                                    <td><input type="text" class="form-control"
                                                            name="codigos_adicionais[{{ $index }}][codigo]"
                                                            value="{{ $codigo->codigo }}"></td>
                                                    <td><input type="text" class="form-control"
                                                            name="codigos_adicionais[{{ $index }}][descricao]"
                                                            value="{{ $codigo->descricao }}"></td>
                                                    <td><button type="button" class="btn btn-danger btn-sm"
                                                            onclick="this.closest('tr').remove()"><i
                                                                class="fas fa-trash"></i></button></td>
                                                </tr>
                                            @endforeach
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
                                        <input type="number" step="0.001" class="form-control" name="peso_liquido"
                                            value="{{ old('peso_liquido', $produto->peso_liquido) }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Peso Bruto (kg)</label>
                                        <input type="number" step="0.001" class="form-control" name="peso_bruto"
                                            value="{{ old('peso_bruto', $produto->peso_bruto) }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Largura (cm)</label>
                                        <input type="number" step="0.01" class="form-control" name="largura"
                                            value="{{ old('largura', $produto->largura) }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Altura (cm)</label>
                                        <input type="number" step="0.01" class="form-control" name="altura"
                                            value="{{ old('altura', $produto->altura) }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Comprimento (cm)</label>
                                        <input type="number" step="0.01" class="form-control" name="comprimento"
                                            value="{{ old('comprimento', $produto->comprimento) }}">
                                    </div>
                                </div>
                                <hr>
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <h5>Preços Avançados</h5>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Preço Atacado</label>
                                        <input type="text" class="form-control" name="preco_atacado"
                                            value="{{ number_format($produto->preco_atacado, 2, ',', '.') }}"
                                            oninput="formatCurrency(this)">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Qtd. Mín. Atacado</label>
                                        <input type="number" class="form-control" name="qtd_min_atacado"
                                            value="{{ old('qtd_min_atacado', $produto->qtd_min_atacado) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Preço Promocional</label>
                                        <input type="text" class="form-control" name="preco_promocional"
                                            value="{{ number_format($produto->preco_promocional, 2, ',', '.') }}"
                                            oninput="formatCurrency(this)">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <label class="form-label">Observações Internas</label>
                                        <textarea class="form-control" name="observacoes_internas" rows="3">{{ old('observacoes_internas', $produto->observacoes_internas) }}</textarea>
                                    </div>
                                </div>
                            </div>

                        </div> <!-- End Tab Content -->

                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-1"></i> Salvar Alterações
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
