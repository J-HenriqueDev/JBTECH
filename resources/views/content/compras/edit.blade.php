@extends('layouts.layoutMaster')

@section('title', 'Editar Compra')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Editar Compra #{{ $compra->id }}</h5>
                <small class="text-muted">Atualize as informações</small>
            </div>
            <div class="card-body">
                <form action="{{ route('compras.update', $compra->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Seção 1: Informações Básicas -->
                    <h6 class="mb-3 text-primary"><i class="bx bx-info-circle me-1"></i> Dados da Solicitação</h6>
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label class="form-label fw-bold" for="tipo">Tipo de Solicitação</label>
                            <select class="form-select" id="tipo" name="tipo" required>
                                <option value="reposicao" {{ $compra->tipo == 'reposicao' ? 'selected' : '' }}>Reposição de Estoque</option>
                                <option value="inovacao" {{ $compra->tipo == 'inovacao' ? 'selected' : '' }}>Inovação / Novos Produtos</option>
                                <option value="uso_interno" {{ $compra->tipo == 'uso_interno' ? 'selected' : '' }}>Uso Interno / Material</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold" for="prioridade">Prioridade</label>
                            <select class="form-select" id="prioridade" name="prioridade" required>
                                <option value="baixa" {{ $compra->prioridade == 'baixa' ? 'selected' : '' }}>Baixa</option>
                                <option value="media" {{ $compra->prioridade == 'media' ? 'selected' : '' }}>Média</option>
                                <option value="alta" {{ $compra->prioridade == 'alta' ? 'selected' : '' }}>Alta</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold" for="data_compra">Data da Solicitação</label>
                            <input type="date" class="form-control" id="data_compra" name="data_compra" value="{{ $compra->data_compra->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold" for="status">Status Atual</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="solicitado" {{ $compra->status == 'solicitado' ? 'selected' : '' }}>Solicitado</option>
                                <option value="cotacao" {{ $compra->status == 'cotacao' ? 'selected' : '' }}>Em Cotação</option>
                                <option value="pendente" {{ $compra->status == 'pendente' ? 'selected' : '' }}>Pendente (Aprovado)</option>
                                <option value="comprado" {{ $compra->status == 'comprado' ? 'selected' : '' }}>Comprado</option>
                                <option value="recebido" {{ $compra->status == 'recebido' ? 'selected' : '' }}>Recebido</option>
                                <option value="cancelado" {{ $compra->status == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                            </select>
                        </div>
                    </div>

                    <!-- Seção 2: Origem e Destino -->
                    <h6 class="mb-3 text-primary"><i class="bx bx-store me-1"></i> Origem e Destino</h6>
                    <div class="row mb-4 p-3 bg-lighter rounded border mx-1">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="fornecedor_id">Fornecedor Preferencial</label>
                            <select class="form-select select2" id="fornecedor_id" name="fornecedor_id">
                                <option value="">Selecione...</option>
                                @foreach($fornecedores as $fornecedor)
                                <option value="{{ $fornecedor->id }}" {{ $compra->fornecedor_id == $fornecedor->id ? 'selected' : '' }}>{{ $fornecedor->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="local_compra">Local de Compra (Alternativo)</label>
                            <input type="text" class="form-control" id="local_compra" name="local_compra" placeholder="Ex: Amazon, Mercado Livre..." value="{{ old('local_compra', $compra->local_compra) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="cliente_id">Cliente (Encomenda)</label>
                            <select class="form-select select2" id="cliente_id" name="cliente_id">
                                <option value="">Não é encomenda</option>
                                @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}" {{ $compra->cliente_id == $cliente->id ? 'selected' : '' }}>{{ $cliente->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="data_prevista_entrega">Previsão de Entrega</label>
                            <input type="date" class="form-control" id="data_prevista_entrega" name="data_prevista_entrega" value="{{ $compra->data_prevista_entrega ? $compra->data_prevista_entrega->format('Y-m-d') : '' }}">
                        </div>
                    </div>

                    <!-- Seção 3: Itens (Read-only notice for now) -->
                    <h6 class="mb-3 text-primary"><i class="bx bx-list-ul me-1"></i> Itens da Compra</h6>
                    <div class="alert alert-warning d-flex align-items-center" role="alert">
                        <i class="bx bx-error me-2"></i>
                        <div>
                            Para modificar os itens, por favor exclua esta solicitação e crie uma nova. A edição de itens será implementada em breve.
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold" for="observacoes">Observações Gerais</label>
                        <textarea class="form-control" id="observacoes" name="observacoes" rows="3">{{ $compra->observacoes }}</textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('compras.index') }}" class="btn btn-label-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bx bx-save me-1"></i> Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection