@extends('layouts.layoutMaster')
@section('title', 'Espelho da Entrada')

@section('content')
    <style>
        .compact-table td,
        .compact-table th {
            padding: 6px 8px;
            font-size: 0.86rem;
        }

        .text-body {
            color: var(--bs-body-color) !important;
        }

        .badge {
            font-size: 0.75rem;
        }
    </style>
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center bg-primary">
                    <div>
                        <h5 class="mb-0 text-white" style="color: #ffffff !important;">NF-e {{ $cabecalho['numero_nfe'] }}
                            (Série {{ $cabecalho['serie'] }})
                        </h5>
                        <small class="text-white-50"
                            style="color: rgba(255,255,255,0.8) !important;">{{ $cabecalho['emitente_nome'] }} •
                            {{ $cabecalho['emitente_cnpj'] }}</small>
                    </div>
                    <div>
                        <h4 class="mb-0 text-white" style="color: #ffffff !important;">R$
                            {{ number_format($cabecalho['valor_total'], 2, ',', '.') }}</h4>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row text-body">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Data Emissão:</strong>
                                {{ \Carbon\Carbon::parse($cabecalho['data_emissao'])->format('d/m/Y H:i') }}</p>
                            <p class="mb-1"><strong>Chave:</strong> {{ $nota->chave_acesso }}</p>
                            <p class="mb-1"><strong>Importada em:</strong>
                                {{ $nota->created_at ? \Carbon\Carbon::parse($nota->created_at)->format('d/m/Y H:i') : '-' }}
                            </p>
                            <p class="mb-1"><strong>Importada por:</strong> {{ $nota->user->name ?? 'Sistema' }}</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <span class="badge bg-label-primary">Espelho de Entrada</span>
                            <a href="{{ route('admin.fiscal.espelho.xml-pdf', $nota->id) }}" target="_blank"
                                class="btn btn-sm btn-outline-danger ms-2">
                                <i class="bx bxs-file-pdf me-1"></i> Espelho XML
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Itens (De/Para)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0 compact-table">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40%">XML</th>
                                    <th style="width: 40%">Estoque</th>
                                    <th style="width: 20%">Precificação</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($itens as $item)
                                    <tr>
                                        <td class="align-top">
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold text-primary">{{ $item['xml_nome'] }}</span>
                                                <div class="text-muted small">
                                                    <span class="me-2">EAN: {{ $item['xml_ean'] ?: 'SEM GTIN' }}</span>
                                                    <span class="me-2">Un: {{ $item['xml_unidade'] }}</span>
                                                    <span>Qtd: {{ number_format($item['xml_qtd'], 2, ',', '.') }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-top">
                                            @if ($item['produto_interno'])
                                                <div class="d-flex flex-column">
                                                    <span class="fw-bold">{{ $item['produto_interno']->nome }}</span>
                                                    <small class="text-muted">Cód:
                                                        {{ $item['produto_interno']->codigo_barras ?? $item['produto_interno']->id }}</small>
                                                </div>
                                            @else
                                                <span class="badge bg-label-warning">Não associado</span>
                                            @endif
                                        </td>
                                        <td class="align-top">
                                            @php
                                                $custo = (float) $item['xml_custo'];
                                                $markup = $markupPadrao;
                                                $preco = $custo * (1 + $markup / 100);
                                            @endphp
                                            <div class="d-flex flex-column">
                                                <small class="text-muted">Custo XML</small>
                                                <span class="fw-bold">R$ {{ number_format($custo, 4, ',', '.') }}</span>
                                                <small class="text-muted mt-2">Markup Padrão</small>
                                                <span
                                                    class="badge bg-label-info">{{ number_format($markup, 2, ',', '.') }}%</span>
                                                <small class="text-muted mt-2">Sugestão de Venda</small>
                                                <span class="fw-bold text-success mb-2">R$
                                                    {{ number_format($preco, 2, ',', '.') }}</span>
                                                @if ($item['produto_interno'])
                                                    <div class="input-group input-group-sm" style="max-width: 220px;">
                                                        <span class="input-group-text">Venda</span>
                                                        <input type="text" class="form-control"
                                                            value="{{ number_format($item['produto_interno']->preco_venda ?? 0, 2, ',', '.') }}"
                                                            data-produto-id="{{ $item['produto_interno']->id }}"
                                                            data-field="preco_venda">
                                                        <button class="btn btn-success btn-save-preco" type="button"
                                                            title="Salvar preço">
                                                            <i class="bx bx-save"></i>
                                                        </button>
                                                    </div>
                                                    <small class="text-muted mt-1">Atualiza o preço diretamente no
                                                        produto.</small>
                                                @else
                                                    <small class="text-muted">Associe o item para editar preço de
                                                        venda.</small>
                                                @endif
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

        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Financeiro (Duplicatas)</h5>
                    <span class="badge bg-label-secondary">{{ count($duplicatas) }} parcelas</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped mb-0 compact-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Número</th>
                                    <th>Vencimento</th>
                                    <th>Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($duplicatas as $dup)
                                    <tr>
                                        <td>{{ $dup['nDup'] }}</td>
                                        <td>{{ \Carbon\Carbon::parse($dup['dVenc'])->format('d/m/Y') }}</td>
                                        <td>R$ {{ number_format($dup['vDup'], 2, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">Sem duplicatas informadas no
                                            XML</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const csrf = '{{ csrf_token() }}';
            document.querySelectorAll('.btn-save-preco').forEach(btn => {
                btn.addEventListener('click', function() {
                    const group = this.closest('.input-group');
                    const input = group.querySelector('input[data-produto-id]');
                    const produtoId = input.getAttribute('data-produto-id');
                    const valor = input.value;
                    this.disabled = true;
                    fetch('{{ url('/dashboard/produtos') }}/' + produtoId + '/update-inline', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        },
                        body: JSON.stringify({
                            campo: 'preco_venda',
                            valor
                        })
                    }).then(r => r.json()).then(data => {
                        this.disabled = false;
                        if (data.success) {
                            this.classList.remove('btn-success');
                            this.classList.add('btn-primary');
                        } else {
                            alert(data.message || 'Falha ao salvar preço');
                        }
                    }).catch(() => {
                        this.disabled = false;
                        alert('Erro ao salvar preço');
                    });
                });
            });
        });
    </script>
@endsection
