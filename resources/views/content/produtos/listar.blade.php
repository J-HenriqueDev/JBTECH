@extends('layouts.layoutMaster')

@section('title', 'Lista de Produtos')

@php
    $metodoLucro = \App\Models\Configuracao::get('produtos_metodo_lucro', 'markup');
    $estoqueMinimo = \App\Models\Configuracao::get('produtos_estoque_minimo', '10');
@endphp

@section('content')

    @if (session('success'))
        <div class="alert alert-primary alert-dismissible" role="alert">
            <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">
                <i class="fas fa-check-circle me-1"></i> Sucesso!
            </h6>
            <p class="mb-0">{!! session('success') !!}</p>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible" role="alert">
            <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">
                <i class="fas fa-exclamation-circle me-1"></i> Erro!
            </h6>
            <p class="mb-0">{!! session('error') !!}</p>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0 text-primary"
            style="font-size: 2.5rem; font-weight: bold; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);">
            <i class="fas fa-box"></i> Lista de Produtos
        </h1>
        <a href="{{ route('produtos.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle me-1"></i> Novo Produto
        </a>
    </div>

    <!-- Botões de Ações em Lote (IA) -->
    <div class="d-flex justify-content-end gap-2 mb-3">
        <form action="{{ route('produtos.categorizar-lote') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-secondary"
                title="Categorizar produtos sem categoria automaticamente via IA">
                <i class="fas fa-tags me-1"></i> Categorizar Produtos (IA)
            </button>
        </form>
        <form action="{{ route('produtos.fiscal-lote') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-secondary"
                title="Preencher NCM e CEST faltantes automaticamente via IA">
                <i class="fas fa-file-invoice-dollar me-1"></i> Consultar Dados Fiscais (IA)
            </button>
        </form>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="card-title mb-0">Produtos Cadastrados</h5>
                            @if ($edicaoInline)
                                <span class="badge bg-success d-flex align-items-center gap-1" style="font-size: 0.75rem;">
                                    <i class="fas fa-edit"></i> Modo Edição Ativo
                                </span>
                            @endif
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <select id="filterCategoria" class="form-select form-select-sm"
                                style="width: auto; min-width: 150px;" onchange="filterProducts()">
                                <option value="">Todas as categorias</option>
                                @foreach ($categorias as $categoria)
                                    <option value="{{ $categoria->id }}">{{ $categoria->nome }}</option>
                                @endforeach
                            </select>
                            <select id="filterEstoque" class="form-select form-select-sm" style="width: auto;"
                                onchange="filterProducts()">
                                <option value="">Todos</option>
                                <option value="baixo">Estoque baixo (≤ 10)</option>
                                <option value="medio">Estoque médio (11-50)</option>
                                <option value="alto">Estoque alto (> 50)</option>
                            </select>
                        </div>
                    </div>
                    <!-- Barra de Pesquisa -->
                    <div class="mt-3">
                        <input type="text" id="search" class="form-control"
                            placeholder="Pesquisar por nome, código de barras ou NCM..." onkeyup="filterProducts()">
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive text-nowrap">
                        <table class="table table-striped" id="productsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome do Produto</th>
                                    <th>Categoria</th>
                                    <th>Estoque</th>
                                    <th>Preço Custo</th>
                                    <th>Preço Venda</th>
                                    <th>Lucro</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($produtos as $produto)
                                    @php
                                        $lucro = $produto->preco_venda - $produto->preco_custo;
                                        if ($metodoLucro === 'margem') {
                                            $lucroPercentual =
                                                $produto->preco_venda > 0 ? ($lucro / $produto->preco_venda) * 100 : 0;
                                        } else {
                                            $lucroPercentual =
                                                $produto->preco_custo > 0 ? ($lucro / $produto->preco_custo) * 100 : 0;
                                        }
                                        $estoqueBaixo = $produto->estoque <= $estoqueMinimo;
                                    @endphp
                                    <tr data-categoria="{{ $produto->categoria_id }}" data-estoque="{{ $produto->estoque }}"
                                        data-produto-id="{{ $produto->id }}">
                                        <td>{{ $produto->id }}</td>
                                        <td class="product-name">
                                            @if ($edicaoInline)
                                                <div class="d-flex align-items-center gap-2">
                                                    <input type="text"
                                                        class="form-control form-control-sm editable-field"
                                                        data-field="nome" data-produto-id="{{ $produto->id }}"
                                                        value="{{ $produto->nome }}" placeholder="Nome do produto"
                                                        style="min-width: 200px; flex: 1;">
                                                    <i class="fas fa-edit text-muted" style="font-size: 0.75rem;"
                                                        title="Campo editável"></i>
                                                </div>
                                            @else
                                                <strong>{{ $produto->nome }}</strong>
                                            @endif
                                            @if ($produto->codigo_barras)
                                                <br><small class="text-muted">Código: {{ $produto->codigo_barras }}</small>
                                            @endif
                                        </td>
                                        <td class="product-categoria">
                                            @if ($edicaoInline)
                                                <select class="form-select form-select-sm editable-field"
                                                    data-field="categoria_id" data-produto-id="{{ $produto->id }}"
                                                    style="min-width: 150px;">
                                                    @foreach ($categorias as $categoria)
                                                        <option value="{{ $categoria->id }}"
                                                            {{ $produto->categoria_id == $categoria->id ? 'selected' : '' }}>
                                                            {{ $categoria->nome }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <span
                                                    class="badge bg-info">{{ $produto->categoria->nome ?? 'Sem categoria' }}</span>
                                            @endif
                                        </td>
                                        <td class="product-quantity">
                                            @if ($edicaoInline)
                                                <div class="d-flex align-items-center gap-1">
                                                    <input type="number"
                                                        class="form-control form-control-sm editable-field text-center"
                                                        data-field="estoque" data-produto-id="{{ $produto->id }}"
                                                        value="{{ $produto->estoque ?? 0 }}" min="0"
                                                        style="width: 90px;">
                                                    <i class="fas fa-box text-muted" style="font-size: 0.75rem;"
                                                        title="Estoque"></i>
                                                </div>
                                            @else
                                                <span
                                                    class="badge bg-{{ $estoqueBaixo ? 'danger' : ($produto->estoque <= 50 ? 'warning' : 'success') }}">
                                                    {{ $produto->estoque ?? 0 }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="product-price-custo">
                                            @if ($edicaoInline)
                                                <div class="d-flex align-items-center gap-1">
                                                    <span class="text-muted" style="font-size: 0.75rem;">R$</span>
                                                    <input type="text"
                                                        class="form-control form-control-sm editable-field money-input text-end"
                                                        data-field="preco_custo" data-produto-id="{{ $produto->id }}"
                                                        value="{{ number_format($produto->preco_custo, 2, ',', '.') }}"
                                                        placeholder="0,00" style="width: 110px;">
                                                </div>
                                            @else
                                                R$ {{ number_format($produto->preco_custo, 2, ',', '.') }}
                                            @endif
                                        </td>
                                        <td class="product-price">
                                            @if ($edicaoInline)
                                                <div class="d-flex align-items-center gap-1">
                                                    <span class="text-muted" style="font-size: 0.75rem;">R$</span>
                                                    <input type="text"
                                                        class="form-control form-control-sm editable-field money-input text-end"
                                                        data-field="preco_venda" data-produto-id="{{ $produto->id }}"
                                                        value="{{ number_format($produto->preco_venda, 2, ',', '.') }}"
                                                        placeholder="0,00" style="width: 110px;">
                                                </div>
                                            @else
                                                <strong>R$ {{ number_format($produto->preco_venda, 2, ',', '.') }}</strong>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $lucro >= 0 ? 'success' : 'danger' }}">
                                                {{ number_format($lucroPercentual, 2, ',', '.') }}%
                                            </span>
                                            <br><small>R$ {{ number_format($lucro, 2, ',', '.') }}</small>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                @if (!$edicaoInline)
                                                    <a href="{{ route('produtos.edit', $produto->id) }}"
                                                        class="btn btn-sm btn-icon btn-outline-primary" title="Editar">
                                                        <i class="bx bx-edit-alt"></i>
                                                    </a>
                                                @endif
                                                <form action="{{ route('produtos.destroy', $produto->id) }}"
                                                    method="POST" style="display:inline;"
                                                    onsubmit="return confirm('Tem certeza que deseja excluir este produto?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-icon btn-outline-danger"
                                                        title="Excluir">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const metodoLucro = "{{ $metodoLucro }}";

        function filterProducts() {
            const input = document.getElementById('search');
            const filter = input.value.toLowerCase();
            const filterCategoria = document.getElementById('filterCategoria').value;
            const filterEstoque = document.getElementById('filterEstoque').value;
            const table = document.getElementById('productsTable');
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                const row = tr[i];
                const tdName = row.getElementsByClassName("product-name")[0];
                const tdId = row.getElementsByTagName("td")[0];

                if (!tdName) continue;

                const categoriaId = row.getAttribute('data-categoria');
                const estoque = parseInt(row.getAttribute('data-estoque')) || 0;

                // Filtro de categoria
                if (filterCategoria && categoriaId !== filterCategoria) {
                    row.style.display = "none";
                    continue;
                }

                // Filtro de estoque
                if (filterEstoque) {
                    if (filterEstoque === 'baixo' && estoque > 10) {
                        row.style.display = "none";
                        continue;
                    } else if (filterEstoque === 'medio' && (estoque <= 10 || estoque > 50)) {
                        row.style.display = "none";
                        continue;
                    } else if (filterEstoque === 'alto' && estoque <= 50) {
                        row.style.display = "none";
                        continue;
                    }
                }

                // Filtro de texto
                const txtValue = tdName.textContent || tdName.innerText;
                const idValue = tdId.textContent || tdId.innerText;

                if (filter && txtValue.toLowerCase().indexOf(filter) === -1 && idValue.indexOf(filter) === -1) {
                    row.style.display = "none";
                } else {
                    row.style.display = "";
                }
            }
        }

        @if ($edicaoInline)
            // Edição Inline de Produtos
            document.addEventListener('DOMContentLoaded', function() {
                // Formatação de valores monetários
                function formatMoney(value) {
                    value = value.replace(/\D/g, '');
                    value = (value / 100).toFixed(2) + '';
                    value = value.replace('.', ',');
                    value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                    return value;
                }

                function parseMoney(value) {
                    return value.replace(/\./g, '').replace(',', '.');
                }

                // Aplica máscara de dinheiro nos inputs
                document.querySelectorAll('.money-input').forEach(input => {
                    input.addEventListener('blur', function() {
                        if (this.value) {
                            this.value = formatMoney(this.value);
                        }
                    });
                });

                // Salva alterações quando o campo perde o foco
                document.querySelectorAll('.editable-field').forEach(field => {
                    let originalValue = field.value;

                    field.addEventListener('focus', function() {
                        originalValue = this.value;
                        if (this.classList.contains('money-input')) {
                            this.value = parseMoney(this.value);
                        }
                    });

                    field.addEventListener('blur', function() {
                        const produtoId = this.getAttribute('data-produto-id');
                        const campo = this.getAttribute('data-field');
                        let valor = this.value;

                        // Formata valor monetário antes de enviar
                        if (this.classList.contains('money-input')) {
                            valor = parseMoney(valor);
                        }

                        // Só atualiza se o valor mudou
                        if (valor !== originalValue && valor !== '') {
                            updateProduto(produtoId, campo, valor, this);
                        } else {
                            // Restaura valor original se não mudou
                            if (this.classList.contains('money-input') && originalValue) {
                                this.value = formatMoney(originalValue);
                            } else {
                                this.value = originalValue;
                            }
                        }
                    });

                    // Salva ao pressionar Enter
                    field.addEventListener('keypress', function(e) {
                        if (e.key === 'Enter') {
                            this.blur();
                        }
                    });
                });

                function updateProduto(produtoId, campo, valor, element) {
                    const originalValue = element.value;
                    element.disabled = true;
                    element.classList.add('saving');

                    fetch(`{{ url('dashboard/produtos') }}/${produtoId}/update-inline`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                campo: campo,
                                valor: valor
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            element.disabled = false;
                            element.classList.remove('saving');

                            if (data.success) {
                                // Atualiza valores calculados (lucro)
                                if (campo === 'preco_custo' || campo === 'preco_venda') {
                                    updateLucro(produtoId);
                                }

                                // Formata valor monetário de volta
                                if (element.classList.contains('money-input')) {
                                    element.value = formatMoney(valor);
                                }

                                // Feedback visual de sucesso
                                element.classList.add('saved');
                                setTimeout(() => {
                                    element.classList.remove('saved');
                                }, 1500);

                                // Notificação toast (opcional)
                                showNotification('Produto atualizado com sucesso!', 'success');
                            } else {
                                element.value = originalValue;
                                showNotification('Erro ao atualizar: ' + data.message, 'error');
                            }
                        })
                        .catch(error => {
                            element.disabled = false;
                            element.classList.remove('saving');
                            element.value = originalValue;
                            console.error('Erro:', error);
                            showNotification('Erro ao atualizar produto. Tente novamente.', 'error');
                        });
                }

                function showNotification(message, type) {
                    // Remove notificações anteriores
                    const existing = document.querySelector('.inline-notification');
                    if (existing) existing.remove();

                    const notification = document.createElement('div');
                    notification.className =
                        `inline-notification alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
                    notification.style.cssText =
                        'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
                    notification.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
                    document.body.appendChild(notification);

                    setTimeout(() => {
                        notification.classList.remove('show');
                        setTimeout(() => notification.remove(), 300);
                    }, 3000);
                }

                function updateLucro(produtoId) {
                    const row = document.querySelector(`tr[data-produto-id="${produtoId}"]`);
                    if (!row) return;

                    const precoCustoInput = row.querySelector('[data-field="preco_custo"]');
                    const precoVendaInput = row.querySelector('[data-field="preco_venda"]');
                    const lucroCell = row.querySelector('td:nth-child(7)');

                    if (precoCustoInput && precoVendaInput && lucroCell) {
                        const precoCusto = parseFloat(parseMoney(precoCustoInput.value)) || 0;
                        const precoVenda = parseFloat(parseMoney(precoVendaInput.value)) || 0;
                        const lucro = precoVenda - precoCusto;
                        let lucroPercentual;
                        if (metodoLucro === 'margem') {
                            lucroPercentual = precoVenda > 0 ? ((lucro / precoVenda) * 100) : 0;
                        } else {
                            lucroPercentual = precoCusto > 0 ? ((lucro / precoCusto) * 100) : 0;
                        }

                        lucroCell.innerHTML = `
                    <span class="badge bg-${lucro >= 0 ? 'success' : 'danger'}">
                        ${lucroPercentual.toFixed(2).replace('.', ',')}%
                    </span>
                    <br><small>R$ ${lucro.toFixed(2).replace('.', ',')}</small>
                `;
                    }
                }
            });
        @endif
    </script>

@endsection
