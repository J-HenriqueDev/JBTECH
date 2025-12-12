@extends('layouts.layoutMaster')

@section('title', 'Detalhes da Cobrança')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0 text-primary" style="font-size: 2.5rem; font-weight: bold; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);">
        <i class="fas fa-money-check-alt"></i> Detalhes da Cobrança #{{ $cobranca->id }}
    </h1>
    <div>
        <a href="{{ route('cobrancas.pdf', $cobranca->id) }}" class="btn btn-danger" target="_blank">
            <i class="fas fa-file-pdf"></i> Baixar PDF
        </a>
        <a href="{{ route('cobrancas.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Informações da Cobrança</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Status:</strong>
                        <span class="badge bg-{{ $cobranca->status == 'pago' ? 'success' : ($cobranca->status == 'cancelado' ? 'danger' : 'warning') }} ms-2">
                            {{ ucfirst($cobranca->status) }}
                        </span>
                    </div>
                    <div class="col-md-6">
                        <strong>Método de Pagamento:</strong>
                        <span class="badge bg-info ms-2">{{ strtoupper($cobranca->metodo_pagamento) }}</span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Valor:</strong>
                        <h4 class="text-primary">R$ {{ number_format($cobranca->valor, 2, ',', '.') }}</h4>
                    </div>
                    <div class="col-md-6">
                        <strong>Data de Vencimento:</strong>
                        <p class="mb-0">{{ $cobranca->data_vencimento ? \Carbon\Carbon::parse($cobranca->data_vencimento)->format('d/m/Y') : 'N/A' }}</p>
                    </div>
                </div>
                @if($cobranca->codigo_pix)
                <div class="row mb-3">
                    <div class="col-md-12">
                        <strong>Código PIX:</strong>
                        <div class="input-group mt-2">
                            <input type="text" class="form-control" id="codigoPix" value="{{ $cobranca->codigo_pix }}" readonly>
                            <button class="btn btn-outline-secondary" type="button" onclick="copiarPix()">
                                <i class="fas fa-copy"></i> Copiar
                            </button>
                        </div>
                    </div>
                </div>
                @endif
                @if($cobranca->link_boleto)
                <div class="row mb-3">
                    <div class="col-md-12">
                        <strong>Link do Boleto:</strong>
                        <a href="{{ $cobranca->link_boleto }}" target="_blank" class="btn btn-warning mt-2">
                            <i class="fas fa-external-link-alt"></i> Acessar Boleto
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Informações da Venda</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Venda ID:</strong>
                        <p class="mb-0">#{{ $cobranca->venda->id }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Data da Venda:</strong>
                        <p class="mb-0">{{ \Carbon\Carbon::parse($cobranca->venda->created_at)->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
                @if($cobranca->venda->cliente)
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Cliente:</strong>
                        <p class="mb-0">{{ $cobranca->venda->cliente->nome }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Email:</strong>
                        <p class="mb-0">{{ $cobranca->venda->cliente->email }}</p>
                    </div>
                </div>
                @endif
                @if($cobranca->venda->produtos && $cobranca->venda->produtos->count() > 0)
                <div class="row">
                    <div class="col-md-12">
                        <strong>Produtos:</strong>
                        <table class="table table-sm mt-2">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Quantidade</th>
                                    <th>Valor Unitário</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cobranca->venda->produtos as $produto)
                                <tr>
                                    <td>{{ $produto->nome }}</td>
                                    <td>{{ $produto->pivot->quantidade }}</td>
                                    <td>R$ {{ number_format($produto->pivot->valor_unitario, 2, ',', '.') }}</td>
                                    <td>R$ {{ number_format($produto->pivot->valor_total, 2, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Ações</h5>
            </div>
            <div class="card-body">
                @if($cobranca->status == 'pendente')
                <form action="{{ route('cobrancas.marcar-paga', $cobranca->id) }}" method="POST" class="mb-2">
                    @csrf
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-check"></i> Marcar como Paga
                    </button>
                </form>
                <form action="{{ route('cobrancas.cancelar', $cobranca->id) }}" method="POST" class="mb-2">
                    @csrf
                    <button type="submit" class="btn btn-secondary w-100">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                </form>
                @endif
                <a href="{{ route('cobrancas.edit', $cobranca->id) }}" class="btn btn-warning w-100 mb-2">
                    <i class="fas fa-edit"></i> Editar
                </a>
                @if($cobranca->status != 'pago')
                <form action="{{ route('cobrancas.destroy', $cobranca->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta cobrança?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger w-100">
                        <i class="fas fa-trash"></i> Excluir
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    function copiarPix() {
        const codigoPix = document.getElementById('codigoPix');
        codigoPix.select();
        codigoPix.setSelectionRange(0, 99999);
        document.execCommand('copy');
        alert('Código PIX copiado!');
    }
</script>

@endsection



