@extends('layouts.layoutMaster')

@section('title', 'Nova Conta a Pagar')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card border-top border-primary border-3 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent py-3">
                <h4 class="mb-0 text-primary fw-bold">
                    <i class="bx bx-money-withdraw me-2"></i>Nova Conta a Pagar
                </h4>
                <a href="{{ route('contas-pagar.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Voltar
                </a>
            </div>

            <form action="{{ route('contas-pagar.store') }}" method="POST">
                @csrf
                <div class="card-body p-4">

                    <div class="row g-3 mb-4">
                        <div class="col-md-12">
                            <label class="form-label fw-bold" for="descricao">Descrição</label>
                            <input type="text" class="form-control" id="descricao" name="descricao" placeholder="Ex: Conta de Luz, Aluguel" required>
                            @error('descricao') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold" for="valor">Valor (R$)</label>
                            <input type="number" step="0.01" class="form-control" id="valor" name="valor" placeholder="0.00" required>
                            @error('valor') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold" for="data_vencimento">Data de Vencimento</label>
                            <input type="date" class="form-control" id="data_vencimento" name="data_vencimento" required>
                            @error('data_vencimento') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-12">
                            <label class="form-label fw-bold" for="fornecedor_id">Fornecedor (Opcional)</label>
                            <select class="select2 form-select" id="fornecedor_id" name="fornecedor_id">
                                <option value="">Selecione um fornecedor</option>
                                @foreach($fornecedores as $fornecedor)
                                <option value="{{ $fornecedor->id }}">#{{ $fornecedor->id }} - {{ $fornecedor->nome }} - {{ $fornecedor->cnpj }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-12">
                            <label class="form-label fw-bold" for="observacoes">Observações</label>
                            <textarea class="form-control" id="observacoes" name="observacoes" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-1"></i> Salvar Conta
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>
@endsection