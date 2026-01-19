@extends('layouts.layoutMaster')

@section('title', 'Detalhes do Orçamento')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0 text-primary" style="font-size: 2.5rem; font-weight: bold; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);">
        <i class="fas fa-file-alt"></i> Orçamento #{{ $orcamento->id }}
        <span class="badge bg-{{ $orcamento->status == 'autorizado' ? 'success' : ($orcamento->status == 'recusado' ? 'danger' : ($orcamento->status == 'apagado' ? 'secondary' : 'warning')) }} ms-2">
            {{ ucfirst($orcamento->status) }}
        </span>
        @if($orcamento->isVencido() && $orcamento->status == 'pendente')
        <span class="badge bg-danger ms-2">Vencido</span>
        @endif
    </h1>
    <div class="btn-group">
        <a href="{{ route('orcamentos.edit', $orcamento->id) }}" class="btn btn-warning">
            <i class="fas fa-edit"></i> Editar
        </a>
        <a href="{{ route('orcamentos.gerarPdf', $orcamento->id) }}" class="btn btn-danger" target="_blank">
            <i class="fas fa-file-pdf"></i> PDF
        </a>
        <a href="{{ route('orcamentos.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<!-- Alerta de Estoque Insuficiente -->
@if(!$podeAutorizar && $orcamento->status == 'pendente')
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">
        <i class="fas fa-exclamation-triangle me-2"></i> Atenção: Estoque Insuficiente
    </h6>
    <p class="mb-2">Não é possível autorizar este orçamento. Os seguintes produtos não possuem estoque suficiente:</p>
    <ul class="mb-0">
        @foreach($produtosSemEstoque as $item)
        <li>
            <strong>{{ $item['produto']->nome }}</strong> -
            Estoque disponível: <span class="badge bg-danger">{{ $item['estoque_disponivel'] }}</span> |
            Solicitado: <span class="badge bg-warning">{{ $item['quantidade_solicitada'] }}</span> |
            Faltam: <span class="badge bg-danger">{{ $item['faltam'] }}</span>
        </li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="row">
    <!-- Informações Principais -->
    <div class="col-lg-8">
        <!-- Informações do Orçamento -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-info-circle"></i> Informações do Orçamento</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong><i class="fas fa-user"></i> Cliente:</strong><br>
                        <span class="text-muted">{{ $orcamento->cliente->nome ?? 'N/A' }}</span>
                    </div>
                    <div class="col-md-6">
                        <strong><i class="fas fa-calendar"></i> Data:</strong><br>
                        <span class="text-muted">{{ $orcamento->data->format('d/m/Y') }}</span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong><i class="fas fa-calendar-check"></i> Validade:</strong><br>
                        <span class="text-muted {{ $orcamento->isVencido() && $orcamento->status == 'pendente' ? 'text-danger' : '' }}">
                            {{ $orcamento->validade->format('d/m/Y') }}
                            @if($orcamento->isVencido() && $orcamento->status == 'pendente')
                            <span class="badge bg-danger">Vencido</span>
                            @endif
                        </span>
                    </div>
                    <div class="col-md-6">
                        <strong><i class="fas fa-dollar-sign"></i> Valor Total:</strong><br>
                        <span class="text-success" style="font-size: 1.2rem; font-weight: bold;">
                            R$ {{ number_format($orcamento->valor_total, 2, ',', '.') }}
                        </span>
                    </div>
                </div>
                @if($orcamento->observacoes)
                <div class="row mb-3">
                    <div class="col-md-12">
                        <strong><i class="fas fa-comment"></i> Observações:</strong><br>
                        <span class="text-muted">{{ $orcamento->observacoes }}</span>
                    </div>
                </div>
                @endif
                @if($orcamento->usuario)
                <div class="row">
                    <div class="col-md-6">
                        <strong><i class="fas fa-user-tie"></i> Criado por:</strong><br>
                        <span class="text-muted">{{ $orcamento->usuario->name ?? 'N/A' }}</span>
                    </div>
                    <div class="col-md-6">
                        <strong><i class="fas fa-clock"></i> Criado em:</strong><br>
                        <span class="text-muted">{{ $orcamento->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Endereço do Cliente -->
        @if($orcamento->cliente && $orcamento->cliente->endereco)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-map-marker-alt"></i> Endereço do Cliente</h5>
            </div>
            <div class="card-body">
                <p class="mb-0">
                    {{ $orcamento->cliente->endereco->endereco }}, {{ $orcamento->cliente->endereco->numero }}<br>
                    {{ $orcamento->cliente->endereco->bairro }}, {{ $orcamento->cliente->endereco->cidade }}/{{ $orcamento->cliente->endereco->estado }}<br>
                    CEP: {{ \Illuminate\Support\Str::substr($orcamento->cliente->endereco->cep, 0, 5) . '-' . \Illuminate\Support\Str::substr($orcamento->cliente->endereco->cep, 5) }}
                </p>
            </div>
        </div>
        @endif

        <!-- Produtos do Orçamento -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-box"></i> Produtos do Orçamento</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Categoria</th>
                                <th>Quantidade</th>
                                <th>Valor Unitário</th>
                                <th>Valor Total</th>
                                <th>Estoque</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orcamento->produtos as $produto)
                            <tr class="{{ $produto->estoque < $produto->pivot->quantidade ? 'table-danger' : '' }}">
                                <td><strong>{{ $produto->nome }}</strong></td>
                                <td>{{ $produto->categoria->nome ?? 'N/A' }}</td>
                                <td>{{ $produto->pivot->quantidade }}</td>
                                <td>R$ {{ number_format($produto->pivot->valor_unitario, 2, ',', '.') }}</td>
                                <td><strong>R$ {{ number_format($produto->pivot->valor_total, 2, ',', '.') }}</strong></td>
                                <td>
                                    <span class="badge bg-{{ $produto->estoque < $produto->pivot->quantidade ? 'danger' : ($produto->estoque <= 10 ? 'warning' : 'success') }}">
                                        {{ $produto->estoque }}
                                    </span>
                                    @if($produto->estoque < $produto->pivot->quantidade)
                                        <small class="text-danger d-block">Faltam {{ $produto->pivot->quantidade - $produto->estoque }} unidades</small>
                                        @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                <td><strong class="text-success" style="font-size: 1.1rem;">R$ {{ number_format($orcamento->valor_total, 2, ',', '.') }}</strong></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar com Ações -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-bolt"></i> Ações Rápidas</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if($orcamento->status == 'pendente')
                    <form id="formAutorizar" action="{{ route('orcamentos.autorizar', $orcamento->id) }}" method="POST">
                        @csrf
                        <button type="button" class="btn btn-success w-100" {{ !$podeAutorizar ? 'disabled' : '' }} onclick="confirmAutorizar()">
                            <i class="fas fa-check"></i> Autorizar Orçamento
                        </button>
                    </form>
                    <form id="formRecusar" action="{{ route('orcamentos.recusar', $orcamento->id) }}" method="POST">
                        @csrf
                        <button type="button" class="btn btn-danger w-100" onclick="confirmRecusar()">
                            <i class="fas fa-times"></i> Recusar Orçamento
                        </button>
                    </form>
                    @endif
                    <a href="{{ route('orcamentos.edit', $orcamento->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Editar Orçamento
                    </a>
                    <a href="{{ route('orcamentos.gerarPdf', $orcamento->id) }}" class="btn btn-danger" target="_blank">
                        <i class="fas fa-file-pdf"></i> Gerar PDF
                    </a>
                    @if($orcamento->status == 'autorizado')
                    <a href="{{ route('vendas.index', ['cliente_id' => $orcamento->cliente_id]) }}" class="btn btn-info">
                        <i class="fas fa-shopping-cart"></i> Ver Venda Gerada
                    </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Informações Adicionais -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-info"></i> Informações Adicionais</h5>
            </div>
            <div class="card-body">
                <p><strong>Status:</strong><br>
                    <span class="badge bg-{{ $orcamento->status == 'autorizado' ? 'success' : ($orcamento->status == 'recusado' ? 'danger' : ($orcamento->status == 'apagado' ? 'secondary' : 'warning')) }}">
                        {{ ucfirst($orcamento->status) }}
                    </span>
                </p>
                <p><strong>Última atualização:</strong><br>
                    <small class="text-muted">{{ $orcamento->updated_at->format('d/m/Y H:i') }}</small>
                </p>
                @if($orcamento->isVencido() && $orcamento->status == 'pendente')
                <p class="text-danger"><strong>⚠ Este orçamento está vencido!</strong></p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Autorizar -->
<div class="modal fade" id="modalAutorizar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Autorização</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja autorizar este orçamento? <strong>Uma venda será criada automaticamente.</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="document.getElementById('formAutorizar').submit()">Sim, Autorizar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Recusar -->
<div class="modal fade" id="modalRecusar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Recusa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja recusar este orçamento?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="document.getElementById('formRecusar').submit()">Sim, Recusar</button>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmAutorizar() {
        var myModal = new bootstrap.Modal(document.getElementById('modalAutorizar'));
        myModal.show();
    }

    function confirmRecusar() {
        var myModal = new bootstrap.Modal(document.getElementById('modalRecusar'));
        myModal.show();
    }
</script>

@endsection