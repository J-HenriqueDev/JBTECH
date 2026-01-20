@extends('layouts.layoutMaster')

@section('title', 'Detalhes da Compra')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Compra #{{ $compra->id }}</h5>
                <div>
                    <a href="{{ route('compras.edit', $compra->id) }}" class="btn btn-warning">Editar</a>
                    <a href="{{ route('compras.index') }}" class="btn btn-secondary">Voltar</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-3">
                        <h6>Tipo</h6>
                        <p>{{ ucfirst($compra->tipo) }}</p>
                    </div>
                    <div class="col-md-3">
                        <h6>Prioridade</h6>
                        <p>{{ ucfirst($compra->prioridade) }}</p>
                    </div>
                    <div class="col-md-3">
                        <h6>Data Solicitação</h6>
                        <p>{{ $compra->data_compra->format('d/m/Y') }}</p>
                    </div>
                    <div class="col-md-3">
                        <h6>Previsão de Entrega</h6>
                        <p>{{ $compra->data_prevista_entrega ? $compra->data_prevista_entrega->format('d/m/Y') : '-' }}</p>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-3">
                        <h6>Status</h6>
                        <span class="badge bg-{{ $compra->status == 'recebido' ? 'success' : ($compra->status == 'cancelado' ? 'danger' : 'warning') }}">
                            {{ ucfirst($compra->status) }}
                        </span>
                    </div>
                    <div class="col-md-3">
                        <h6>Fornecedor</h6>
                        <p>{{ $compra->fornecedor->nome ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-3">
                        <h6>Local de Compra</h6>
                        <p>{{ $compra->local_compra ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-3">
                        <h6>Cliente (Encomenda)</h6>
                        <p>{{ $compra->cliente->nome ?? 'N/A' }}</p>
                    </div>
                </div>

                <h6>Itens</h6>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Produto / Descrição</th>
                                <th>Quantidade</th>
                                <th>Valor Unit.</th>
                                <th>Total</th>
                                <th>Status Item</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($compra->items as $item)
                            <tr>
                                <td>
                                    @if($item->produto)
                                    {{ $item->produto->nome }}
                                    @else
                                    {{ $item->descricao_livre }} <span class="badge bg-label-secondary ms-1">Item Livre</span>
                                    @endif
                                </td>
                                <td>{{ $item->quantidade }}</td>
                                <td>
                                    @if($item->valor_unitario)
                                    R$ {{ number_format($item->valor_unitario, 2, ',', '.') }}
                                    @else
                                    <span class="text-muted">A definir</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->valor_total)
                                    R$ {{ number_format($item->valor_total, 2, ',', '.') }}
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $item->status == 'aprovado' ? 'success' : ($item->status == 'recusado' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($item->status == 'pendente')
                                    <form action="{{ route('compras.updateItemStatus', $item->id) }}" method="POST" class="d-inline">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="status" value="aprovado">
                                        <button type="submit" class="btn btn-sm btn-success" title="Aprovar Item"><i class="bx bx-check"></i></button>
                                    </form>
                                    <form action="{{ route('compras.updateItemStatus', $item->id) }}" method="POST" class="d-inline">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="status" value="recusado">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Recusar Item"><i class="bx bx-x"></i></button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Total Geral:</th>
                                <th>R$ {{ number_format($compra->valor_total, 2, ',', '.') }}</th>
                                <th colspan="2"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if($compra->observacoes)
                <div class="mt-4">
                    <h6>Observações</h6>
                    <p>{{ $compra->observacoes }}</p>
                </div>
                @endif

                @if($compra->status == 'cancelado' && $compra->motivo_recusa)
                <div class="mt-4 alert alert-danger">
                    <h6>Motivo do Cancelamento/Recusa</h6>
                    <p class="mb-0">{{ $compra->motivo_recusa }}</p>
                </div>
                @endif
            </div>

            <div class="card-footer border-top">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Gerenciar Solicitação</h6>
                        <small class="text-muted">Ações disponíveis para o status atual: <strong>{{ ucfirst($compra->status) }}</strong></small>
                    </div>

                    <div class="d-flex gap-2">
                        <!-- Ações para Status Solicitado -->
                        @if($compra->status == 'solicitado')
                        <div class="btn-group">
                            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bx bx-cog me-1"></i> Processar
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <form action="{{ route('compras.update', $compra->id) }}" method="POST">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="status" value="cotacao">
                                        <button type="submit" class="dropdown-item"><i class="bx bx-file-find me-2"></i> Iniciar Cotação</button>
                                    </form>
                                </li>
                                <li>
                                    <form action="{{ route('compras.update', $compra->id) }}" method="POST">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="status" value="pendente">
                                        <button type="submit" class="dropdown-item"><i class="bx bx-check me-2"></i> Aprovar (Pendente)</button>
                                    </form>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <button type="button" class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#modalRecusar">
                                        <i class="bx bx-x me-2"></i> Recusar
                                    </button>
                                </li>
                            </ul>
                        </div>
                        @endif

                        <!-- Ações para Status Cotação -->
                        @if($compra->status == 'cotacao')
                        <form action="{{ route('compras.update', $compra->id) }}" method="POST">
                            @csrf @method('PUT')
                            <input type="hidden" name="status" value="pendente">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-check me-1"></i> Aprovar Compra
                            </button>
                        </form>
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalRecusar">
                            <i class="bx bx-x me-1"></i> Recusar
                        </button>
                        @endif

                        <!-- Ações para Status Pendente (Aprovado) -->
                        @if($compra->status == 'pendente')
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalComprado">
                            <i class="bx bx-cart me-1"></i> Marcar como Comprado
                        </button>
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalRecusar">
                            <i class="bx bx-x me-1"></i> Cancelar
                        </button>
                        @endif

                        <!-- Ações para Status Comprado -->
                        @if($compra->status == 'comprado')
                        <form action="{{ route('compras.update', $compra->id) }}" method="POST">
                            @csrf @method('PUT')
                            <input type="hidden" name="status" value="recebido">
                            <button type="submit" class="btn btn-dark">
                                <i class="bx bx-package me-1"></i> Confirmar Recebimento
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Marcar como Comprado -->
<div class="modal fade" id="modalComprado" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Compra</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('compras.update', $compra->id) }}" method="POST">
                @csrf @method('PUT')
                <input type="hidden" name="status" value="comprado">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="data_prevista_entrega" class="form-label">Previsão de Entrega</label>
                        <input type="date" class="form-control" id="data_prevista_entrega" name="data_prevista_entrega">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Recusar -->
<div class="modal fade" id="modalRecusar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Recusar ou Cancelar Solicitação</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('compras.update', $compra->id) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="cancelado">
                <div class="modal-body">
                    <div class="row">
                        <div class="col mb-3">
                            <label for="motivo_recusa" class="form-label">Motivo da Recusa/Cancelamento</label>
                            <textarea class="form-control" id="motivo_recusa" name="motivo_recusa" rows="3" required placeholder="Ex: Item muito caro, fora do orçamento, ou não é prioritário agora."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-danger">Confirmar Cancelamento</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection