@extends('layouts.layoutMaster')

@section('title', 'Processar Nota de Entrada')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/select2/select2.scss'
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/select2/select2.js'
])
@endsection

@section('page-script')
<script>
    $(document).ready(function() {
        // Carrega produtos para o Select2
        let produtosOptions = [];

        $.ajax({
            url: '{{ route("produtos.lista") }}',
            method: 'GET',
            success: function(data) {
                // Adiciona opção vazia inicial
                produtosOptions.push({
                    id: '',
                    text: ''
                });

                data.forEach(function(item) {
                    produtosOptions.push({
                        id: item.id,
                        text: item.nome + (item.codigo_barras ? ' (' + item.codigo_barras + ')' : ''),
                        estoque: item.estoque,
                        preco: item.preco_venda
                    });
                });

                // Inicializa Select2 nos campos
                initSelect2();
            },
            error: function() {
                console.error('Erro ao carregar lista de produtos.');
                // Não exibir alerta intrusivo, apenas logar
            }
        });

        function initSelect2() {
            $('.select2-produto').each(function() {
                if ($(this).hasClass("select2-hidden-accessible")) return;

                $(this).select2({
                    data: produtosOptions,
                    placeholder: "Selecione um produto...",
                    allowClear: true,
                    width: '100%'
                });
            });
        }

        // Monitora mudança na ação
        $(document).on('change', '.acao-select', function() {
            let index = $(this).data('index');
            let acao = $(this).val();
            let container = $(this).closest('td');

            if (acao === 'associar') {
                container.find('.produto-novo-container').addClass('d-none');
                container.find('.produto-associar-container').removeClass('d-none');

                // Garante inicialização
                if (produtosOptions.length > 0) initSelect2();
            } else if (acao === 'criar') {
                container.find('.produto-novo-container').removeClass('d-none');
                container.find('.produto-associar-container').addClass('d-none');
            } else {
                container.find('.produto-novo-container').addClass('d-none');
                container.find('.produto-associar-container').addClass('d-none');
            }
        });
    });
</script>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Processar Nota: {{ $nota->numero_nfe ? 'NFe '.$nota->numero_nfe : $nota->chave_acesso }}</h5>
                <small class="text-muted">{{ $nota->emitente_nome }}</small>
            </div>
            <div class="card-body">
                <form action="{{ route('notas-entrada.confirmar', $nota->id) }}" method="POST">
                    @csrf

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 5%">Item</th>
                                    <th style="width: 30%">Produto (XML)</th>
                                    <th style="width: 30%">Produto (Sistema)</th>
                                    <th style="width: 10%">Qtd</th>
                                    <th style="width: 10%">Custo Unit.</th>
                                    <th style="width: 10%">Preço Venda</th>
                                    <th style="width: 5%">Estoque</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($itens as $index => $item)
                                <tr>
                                    <td>{{ $item['nItem'] }}</td>
                                    <td>
                                        <strong>{{ $item['xProd'] }}</strong><br>
                                        <small class="text-muted">EAN: {{ $item['cEAN'] }}</small><br>
                                        <small class="text-muted">NCM: {{ $item['NCM'] }}</small>
                                        <input type="hidden" name="itens[{{ $index }}][nItem]" value="{{ $item['nItem'] }}">
                                        <input type="hidden" name="itens[{{ $index }}][cProd]" value="{{ $item['cProd'] }}">
                                        <input type="hidden" name="itens[{{ $index }}][xProd]" value="{{ $item['xProd'] }}">
                                        <input type="hidden" name="itens[{{ $index }}][cEAN]" value="{{ $item['cEAN'] }}">
                                        <input type="hidden" name="itens[{{ $index }}][NCM]" value="{{ $item['NCM'] }}">
                                    </td>
                                    <td>
                                        @if($item['produto_existente'])
                                        <div class="alert alert-success mb-0 p-2">
                                            <i class="fas fa-check-circle me-1"></i> Encontrado: {{ $item['produto_existente']->nome }}
                                        </div>
                                        <input type="hidden" name="itens[{{ $index }}][produto_id]" value="{{ $item['produto_existente']->id }}">
                                        <input type="hidden" name="itens[{{ $index }}][acao]" value="atualizar">
                                        @else
                                        <select class="form-select mb-2 acao-select" name="itens[{{ $index }}][acao]" data-index="{{ $index }}">
                                            <option value="criar" selected>Criar Novo Produto</option>
                                            <option value="associar">Associar a Produto Existente</option>
                                            <option value="ignorar">Ignorar Item</option>
                                        </select>

                                        <div class="produto-novo-container" id="produto-novo-{{ $index }}">
                                            <input type="text" class="form-control form-control-sm" name="itens[{{ $index }}][nome_novo]" value="{{ $item['xProd'] }}" placeholder="Nome do Produto no Sistema">
                                        </div>

                                        <div class="produto-associar-container d-none" id="produto-associar-{{ $index }}">
                                            <select class="select2-produto form-select" name="itens[{{ $index }}][produto_id]">
                                                <option></option>
                                            </select>
                                        </div>
                                        @endif
                                    </td>
                                    <td>
                                        <input type="number" class="form-control" name="itens[{{ $index }}][quantidade]" value="{{ $item['qCom'] }}" step="0.0001" readonly>
                                        <small class="text-muted">{{ $item['uCom'] }}</small>
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">R$</span>
                                            <input type="number" class="form-control" name="itens[{{ $index }}][preco_custo]" value="{{ number_format($item['vUnCom'], 4, '.', '') }}" step="0.0001">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">R$</span>
                                            <input type="number" class="form-control" name="itens[{{ $index }}][preco_venda]" value="{{ $item['produto_existente'] ? $item['produto_existente']->preco_venda : number_format($item['vUnCom'] * 1.5, 2, '.', '') }}" step="0.01">
                                        </div>
                                        <small class="text-muted">Margem sug. 50%</small>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check d-flex justify-content-center">
                                            <input class="form-check-input" type="checkbox" name="itens[{{ $index }}][atualizar_estoque]" value="1" checked>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 text-end">
                        <a href="{{ route('notas-entrada.index') }}" class="btn btn-outline-secondary me-2">Cancelar</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Confirmar Entrada
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection