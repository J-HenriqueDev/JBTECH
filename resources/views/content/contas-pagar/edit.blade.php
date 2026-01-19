@extends('layouts.layoutMaster')

@section('title', 'Editar Conta a Pagar')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card border-top border-primary border-3 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent py-3">
                <h4 class="mb-0 text-primary fw-bold">
                    <i class="bx bx-money-withdraw me-2"></i>Editar Conta a Pagar
                </h4>
                <a href="{{ route('contas-pagar.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Voltar
                </a>
            </div>

            <form action="{{ route('contas-pagar.update', $contaPagar->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body p-4">

                    <div class="row g-3 mb-4">
                        <div class="col-md-12">
                            <label class="form-label fw-bold" for="descricao">Descrição</label>
                            <input type="text" class="form-control" id="descricao" name="descricao" value="{{ old('descricao', $contaPagar->descricao) }}" required>
                            @error('descricao') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold" for="valor">Valor (R$)</label>
                            <input type="number" step="0.01" class="form-control" id="valor" name="valor" value="{{ old('valor', $contaPagar->valor) }}" required>
                            @error('valor') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold" for="data_vencimento">Data de Vencimento</label>
                            <input type="date" class="form-control" id="data_vencimento" name="data_vencimento" value="{{ old('data_vencimento', $contaPagar->data_vencimento->format('Y-m-d')) }}" required>
                            @error('data_vencimento') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold" for="fornecedor_id">Fornecedor (Opcional)</label>
                            <select class="select2 form-select" id="fornecedor_id" name="fornecedor_id">
                                <option value="">Selecione um fornecedor</option>
                                @foreach($fornecedores as $fornecedor)
                                    <option value="{{ $fornecedor->id }}" {{ $contaPagar->fornecedor_id == $fornecedor->id ? 'selected' : '' }}>#{{ $fornecedor->id }} - {{ $fornecedor->nome }} - {{ $fornecedor->cnpj }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" for="status">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="pendente" {{ $contaPagar->status == 'pendente' ? 'selected' : '' }}>Pendente</option>
                                <option value="pago" {{ $contaPagar->status == 'pago' ? 'selected' : '' }}>Pago</option>
                                <option value="cancelado" {{ $contaPagar->status == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                                <option value="atrasado" {{ $contaPagar->status == 'atrasado' ? 'selected' : '' }}>Atrasado</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-12">
                            <label class="form-label fw-bold" for="observacoes">Observações</label>
                            <textarea class="form-control" id="observacoes" name="observacoes" rows="3">{{ old('observacoes', $contaPagar->observacoes) }}</textarea>
                        </div>
                    </div>
                    
                    @if($contaPagar->status == 'pago')
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                             <label class="form-label fw-bold" for="data_pagamento">Data do Pagamento</label>
                             <input type="date" class="form-control" id="data_pagamento" name="data_pagamento" value="{{ $contaPagar->data_pagamento ? $contaPagar->data_pagamento->format('Y-m-d') : '' }}">
                        </div>
                    </div>
                    @endif

                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="fas fa-trash me-1"></i> Excluir
                        </button>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-1"></i> Atualizar Conta
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Exclusão -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir esta conta?</p>
                <p class="text-danger"><small>Esta ação não poderá ser desfeita.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form action="{{ route('contas-pagar.destroy', $contaPagar->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
