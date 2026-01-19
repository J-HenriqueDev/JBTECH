@extends('layouts/contentNavbarLayout')

@section('title', 'Nova Natureza de Operação')

@section('content')
<h4 class="fw-bold py-3 mb-4">
    <span class="text-muted fw-light">Cadastros / Naturezas de Operação /</span> Nova
</h4>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <h5 class="card-header">Detalhes da Natureza</h5>
            <div class="card-body">
                <form action="{{ route('naturezas.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label for="descricao" class="form-label">Descrição *</label>
                            <input class="form-control" type="text" id="descricao" name="descricao" value="{{ old('descricao') }}" required placeholder="Ex: Venda de Mercadoria" />
                            @error('descricao') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3 col-md-3">
                            <label for="tipo" class="form-label">Tipo *</label>
                            <select id="tipo" name="tipo" class="form-select" required>
                                <option value="saida" {{ old('tipo') == 'saida' ? 'selected' : '' }}>Saída</option>
                                <option value="entrada" {{ old('tipo') == 'entrada' ? 'selected' : '' }}>Entrada</option>
                            </select>
                            @error('tipo') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3 col-md-3">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="padrao" name="padrao" {{ old('padrao') ? 'checked' : '' }}>
                                <label class="form-check-label" for="padrao">
                                    Definir como Padrão
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="mb-3 col-md-4">
                            <label for="cfop_estadual" class="form-label">CFOP Estadual (Dentro do Estado) *</label>
                            <input class="form-control" type="text" id="cfop_estadual" name="cfop_estadual" value="{{ old('cfop_estadual') }}" required maxlength="4" placeholder="Ex: 5102" />
                            @error('cfop_estadual') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3 col-md-4">
                            <label for="cfop_interestadual" class="form-label">CFOP Interestadual (Fora do Estado) *</label>
                            <input class="form-control" type="text" id="cfop_interestadual" name="cfop_interestadual" value="{{ old('cfop_interestadual') }}" required maxlength="4" placeholder="Ex: 6102" />
                            @error('cfop_interestadual') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3 col-md-4">
                            <label for="cfop_exterior" class="form-label">CFOP Exterior (Internacional)</label>
                            <input class="form-control" type="text" id="cfop_exterior" name="cfop_exterior" value="{{ old('cfop_exterior') }}" maxlength="4" placeholder="Ex: 7102" />
                            @error('cfop_exterior') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="mb-3 col-md-4">
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" id="calcula_custo" name="calcula_custo" {{ old('calcula_custo') ? 'checked' : '' }}>
                                <label class="form-check-label" for="calcula_custo">
                                    Calcula Custo
                                </label>
                            </div>
                        </div>
                        <div class="mb-3 col-md-4">
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" id="movimenta_estoque" name="movimenta_estoque" {{ old('movimenta_estoque') ? 'checked' : '' }}>
                                <label class="form-check-label" for="movimenta_estoque">
                                    Movimenta Estoque
                                </label>
                            </div>
                        </div>
                        <div class="mb-3 col-md-4">
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" id="gera_financeiro" name="gera_financeiro" {{ old('gera_financeiro') ? 'checked' : '' }}>
                                <label class="form-check-label" for="gera_financeiro">
                                    Gera Financeiro
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="mt-2">
                        <button type="submit" class="btn btn-primary me-2">Salvar</button>
                        <a href="{{ route('naturezas.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection