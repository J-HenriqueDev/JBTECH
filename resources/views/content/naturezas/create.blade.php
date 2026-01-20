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
                            <label for="finNFe" class="form-label">Finalidade da Emissão *</label>
                            <select id="finNFe" name="finNFe" class="form-select" required>
                                <option value="1" {{ old('finNFe') == '1' ? 'selected' : '' }}>1 - NF-e Normal</option>
                                <option value="2" {{ old('finNFe') == '2' ? 'selected' : '' }}>2 - NF-e Complementar</option>
                                <option value="3" {{ old('finNFe') == '3' ? 'selected' : '' }}>3 - NF-e de Ajuste</option>
                                <option value="4" {{ old('finNFe') == '4' ? 'selected' : '' }}>4 - Devolução/Retorno</option>
                            </select>
                            @error('finNFe') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3 col-md-4">
                            <label for="indPres" class="form-label">Indicador de Presença *</label>
                            <select id="indPres" name="indPres" class="form-select" required>
                                <option value="0" {{ old('indPres') == '0' ? 'selected' : '' }}>0 - Não se aplica</option>
                                <option value="1" {{ old('indPres') == '1' ? 'selected' : '' }}>1 - Operação presencial</option>
                                <option value="2" {{ old('indPres') == '2' ? 'selected' : '' }}>2 - Operação não presencial, pela Internet</option>
                                <option value="3" {{ old('indPres') == '3' ? 'selected' : '' }}>3 - Operação não presencial, Teleatendimento</option>
                                <option value="4" {{ old('indPres') == '4' ? 'selected' : '' }}>4 - NFC-e em operação com entrega a domicílio</option>
                                <option value="5" {{ old('indPres') == '5' ? 'selected' : '' }}>5 - Operação presencial, fora do estabelecimento</option>
                                <option value="9" {{ old('indPres') == '9' ? 'selected' : '' }}>9 - Operação não presencial, outros</option>
                            </select>
                            @error('indPres') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3 col-md-4">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="consumidor_final" name="consumidor_final" {{ old('consumidor_final') ? 'checked' : '' }}>
                                <label class="form-check-label" for="consumidor_final">
                                    Operação com Consumidor Final
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <h6 class="mt-3 text-muted">Configurações Adicionais</h6>
                            <hr class="mt-0" />
                        </div>
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
                        <div class="col-12">
                            <h6 class="mt-3 text-muted">Configurações de Processamento</h6>
                            <hr class="mt-0" />
                        </div>
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