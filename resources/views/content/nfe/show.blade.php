@extends('layouts.layoutMaster')

@section('title', 'Detalhes da NF-e #' . $notaFiscal->id)

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible" role="alert">
    <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">
        <i class="bx bx-check-circle me-1"></i> Sucesso!
    </h6>
    <p class="mb-0">{!! session('success') !!}</p>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('info'))
<div class="alert alert-info alert-dismissible" role="alert">
    <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">
        <i class="bx bx-info-circle me-1"></i> Informação!
    </h6>
    <p class="mb-0">{!! session('info') !!}</p>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0 text-primary">
        <i class="bx bx-receipt"></i> NF-e #{{ $notaFiscal->numero_nfe ?? $notaFiscal->id }}
        @php
            $badgeColor = match($notaFiscal->status) {
                'autorizada' => 'success',
                'rejeitada' => 'danger',
                'cancelada' => 'warning',
                'processando' => 'info',
                default => 'secondary'
            };
        @endphp
        <span class="badge bg-{{ $badgeColor }} ms-2">
            {{ ucfirst($notaFiscal->status) }}
        </span>
    </h1>
    <div class="d-flex gap-2">
        @if($notaFiscal->xml)
            <a href="{{ route('nfe.downloadXml', $notaFiscal->id) }}" class="btn btn-success">
                <i class="bx bx-download"></i> Baixar XML
            </a>
        @endif
        @if($notaFiscal->podeCancelar())
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalCancelar">
                <i class="bx bx-x-circle"></i> Cancelar NF-e
            </button>
        @endif
        <a href="{{ route('nfe.consultarStatus', $notaFiscal->id) }}" class="btn btn-info">
            <i class="bx bx-refresh"></i> Consultar Status
        </a>
        <a href="{{ route('nfe.index') }}" class="btn btn-secondary">
            <i class="bx bx-arrow-back"></i> Voltar
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Dados da NF-e</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="40%">Número:</th>
                        <td>{{ $notaFiscal->numero_nfe ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Chave de Acesso:</th>
                        <td><small>{{ $notaFiscal->chave_acesso ?? 'Pendente' }}</small></td>
                    </tr>
                    <tr>
                        <th>Protocolo:</th>
                        <td>{{ $notaFiscal->protocolo ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            <span class="badge bg-{{ $badgeColor }}">
                                {{ ucfirst($notaFiscal->status) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Valor Total:</th>
                        <td><strong>R$ {{ number_format($notaFiscal->valor_total, 2, ',', '.') }}</strong></td>
                    </tr>
                    <tr>
                        <th>Data de Emissão:</th>
                        <td>{{ $notaFiscal->data_emissao ? $notaFiscal->data_emissao->format('d/m/Y H:i:s') : 'N/A' }}</td>
                    </tr>
                    @if($notaFiscal->motivo_rejeicao)
                    <tr>
                        <th>Motivo Rejeição:</th>
                        <td class="text-danger">{{ $notaFiscal->motivo_rejeicao }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Dados do Cliente</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="40%">Nome:</th>
                        <td>{{ $notaFiscal->cliente->nome ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>CPF/CNPJ:</th>
                        <td>{{ $notaFiscal->cliente->cpf_cnpj ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td>{{ $notaFiscal->cliente->email ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Telefone:</th>
                        <td>{{ $notaFiscal->cliente->telefone ?? 'N/A' }}</td>
                    </tr>
                    @if($notaFiscal->cliente->endereco)
                    <tr>
                        <th>Endereço:</th>
                        <td>
                            {{ $notaFiscal->cliente->endereco->endereco }}, {{ $notaFiscal->cliente->endereco->numero }}<br>
                            {{ $notaFiscal->cliente->endereco->bairro }}<br>
                            {{ $notaFiscal->cliente->endereco->cidade }}/{{ $notaFiscal->cliente->endereco->estado }}<br>
                            CEP: {{ $notaFiscal->cliente->endereco->cep }}
                        </td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

@if($notaFiscal->venda)
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Venda Relacionada</h5>
            </div>
            <div class="card-body">
                <p>
                    <strong>Venda:</strong> 
                    <a href="{{ route('vendas.edit', $notaFiscal->venda->id) }}" class="text-primary">
                        #{{ $notaFiscal->venda->id }}
                    </a>
                    - Valor: R$ {{ number_format($notaFiscal->venda->valor_total, 2, ',', '.') }}
                </p>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Produtos</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>NCM</th>
                                <th>Quantidade</th>
                                <th>Valor Unitário</th>
                                <th>Valor Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($notaFiscal->produtos)
                                @foreach($notaFiscal->produtos as $produto)
                                <tr>
                                    <td>{{ $produto['id'] ?? 'N/A' }}</td>
                                    <td>{{ $produto['nome'] ?? 'N/A' }}</td>
                                    <td>{{ $produto['ncm'] ?? 'N/A' }}</td>
                                    <td>{{ $produto['quantidade'] ?? 'N/A' }}</td>
                                    <td>R$ {{ number_format($produto['valor_unitario'] ?? 0, 2, ',', '.') }}</td>
                                    <td>R$ {{ number_format($produto['valor_total'] ?? 0, 2, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6" class="text-center">Nenhum produto encontrado</td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-end"><strong>Total:</strong></td>
                                <td><strong>R$ {{ number_format($notaFiscal->valor_total, 2, ',', '.') }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cancelar NF-e -->
@if($notaFiscal->podeCancelar())
<div class="modal fade" id="modalCancelar" tabindex="-1" aria-labelledby="modalCancelarLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('nfe.cancelar', $notaFiscal->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCancelarLabel">Cancelar NF-e</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bx bx-error"></i> Esta ação não pode ser desfeita!
                    </div>
                    <div class="mb-3">
                        <label for="justificativa" class="form-label">Justificativa do Cancelamento *</label>
                        <textarea name="justificativa" id="justificativa" class="form-control" rows="4" 
                                  placeholder="Informe a justificativa para o cancelamento (mínimo 15 caracteres)" 
                                  required minlength="15" maxlength="255"></textarea>
                        <small class="form-text text-muted">Mínimo de 15 caracteres</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Confirmar Cancelamento</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection



