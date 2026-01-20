@extends('layouts.layoutMaster')

@section('title', 'Gestão de Compras')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0 d-inline-block me-2">Compras {{ $showCompleted ? '(Concluídas)' : '(Pendentes)' }}</h5>
            @if($showCompleted)
            <a href="{{ route('compras.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bx bx-filter-alt me-1"></i> Mostrar Pendentes
            </a>
            @else
            <a href="{{ route('compras.index', ['completed' => 1]) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bx bx-check-circle me-1"></i> Mostrar Concluídas
            </a>
            @endif
        </div>
        <a href="{{ route('compras.create') }}" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i> Nova Compra
        </a>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tipo</th>
                    <th>Prioridade</th>
                    <th>Fornecedor</th>
                    <th>Data</th>
                    <th>Valor Total</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($compras as $compra)
                <tr>
                    <td>#{{ $compra->id }}</td>
                    <td>
                        @switch($compra->tipo)
                        @case('reposicao') <span class="badge bg-label-primary">Reposição</span> @break
                        @case('inovacao') <span class="badge bg-label-success">Inovação</span> @break
                        @case('uso_interno') <span class="badge bg-label-info">Uso Interno</span> @break
                        @default {{ ucfirst($compra->tipo) }}
                        @endswitch
                    </td>
                    <td>
                        @switch($compra->prioridade)
                        @case('alta') <span class="badge bg-danger">Alta</span> @break
                        @case('media') <span class="badge bg-warning">Média</span> @break
                        @case('baixa') <span class="badge bg-secondary">Baixa</span> @break
                        @default {{ ucfirst($compra->prioridade) }}
                        @endswitch
                    </td>
                    <td>{{ $compra->fornecedor->nome ?? 'N/A' }}</td>
                    <td>{{ $compra->data_compra->format('d/m/Y') }}</td>
                    <td>
                        @if($compra->valor_total > 0)
                        R$ {{ number_format($compra->valor_total, 2, ',', '.') }}
                        @else
                        <span class="text-muted">A definir</span>
                        @endif
                    </td>
                    <td>
                        @switch($compra->status)
                        @case('recebido') <span class="badge bg-success">Recebido</span> @break
                        @case('comprado') <span class="badge bg-info">Comprado</span> @break
                        @case('pendente') <span class="badge bg-warning">Pendente</span> @break
                        @case('solicitado') <span class="badge bg-label-warning">Solicitado</span> @break
                        @case('cotacao') <span class="badge bg-label-info">Em Cotação</span> @break
                        @case('cancelado') <span class="badge bg-danger">Cancelado</span> @break
                        @default <span class="badge bg-secondary">{{ ucfirst($compra->status) }}</span>
                        @endswitch
                    </td>
                    <td>
                        <a href="{{ route('compras.show', $compra->id) }}" class="btn btn-sm btn-info icon-btn" title="Detalhes">
                            <i class="bx bx-show-alt"></i>
                        </a>
                        <a href="{{ route('compras.edit', $compra->id) }}" class="btn btn-sm btn-warning icon-btn" title="Editar">
                            <i class="bx bx-edit-alt"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center">Nenhuma solicitação ou compra registrada.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $compras->appends(['completed' => $showCompleted])->links() }}
    </div>
</div>
@endsection