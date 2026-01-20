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

                    <!-- Seção de Recorrência -->
                    <div class="card bg-lighter border mb-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="form-check form-switch custom-option-basic">
                                    <input class="form-check-input" type="checkbox" id="recorrente" name="recorrente" value="1">
                                    <label class="form-check-label fw-bold ms-2" for="recorrente">Este é um pagamento recorrente?</label>
                                </div>
                            </div>

                            <div id="recorrencia_options" class="row g-3 d-none animate__animated animate__fadeIn">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold" for="frequencia">Frequência</label>
                                    <select class="form-select" id="frequencia" name="frequencia">
                                        <option value="mensal" selected>Mensal</option>
                                        <option value="semanal">Semanal</option>
                                        <option value="anual">Anual</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold" for="dia_vencimento">Dia de Vencimento (Fixo)</label>
                                    <input type="number" class="form-control" id="dia_vencimento" name="dia_vencimento" min="1" max="31" placeholder="Ex: 10">
                                    <small class="text-muted">Se definido, o vencimento será sempre neste dia.</small>
                                </div>
                            </div>
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

@section('page-script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkboxRecorrente = document.getElementById('recorrente');
        const optionsContainer = document.getElementById('recorrencia_options');

        // Função para alternar visibilidade
        function toggleRecorrencia() {
            if (checkboxRecorrente.checked) {
                optionsContainer.classList.remove('d-none');
            } else {
                optionsContainer.classList.add('d-none');
            }
        }

        // Event listener
        checkboxRecorrente.addEventListener('change', toggleRecorrencia);

        // Verifica estado inicial (útil para old inputs em caso de erro de validação)
        toggleRecorrencia();
    });
</script>
@endsection