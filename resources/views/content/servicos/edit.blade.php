@extends('layouts/contentNavbarLayout')

@section('title', 'Editar Modelo de Serviço')

@section('content')
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Serviços /</span> Editar
    </h4>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <h5 class="card-header">Detalhes do Modelo de Serviço #{{ $servico->id }}</h5>
                <div class="card-body">
                    <form action="{{ route('servicos.update', $servico->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="nome" class="form-label">Nome do Modelo (Identificação Interna)</label>
                                <input type="text" class="form-control" id="nome" name="nome"
                                    value="{{ old('nome', $servico->nome) }}" required>
                            </div>

                            <div class="divider text-start">
                                <div class="divider-text">Dados Fiscais (NFS-e)</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="codigo_servico" class="form-label">Código de Tributação Nacional (LC 116)
                                    *</label>
                                <input type="text" class="form-control" id="codigo_servico" name="codigo_servico"
                                    value="{{ old('codigo_servico', $servico->codigo_servico) }}"
                                    placeholder="Ex: 01.07.01">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="codigo_nbs" class="form-label">Item da NBS correspondente ao serviço prestado
                                    *</label>
                                <input type="text" class="form-control" id="codigo_nbs" name="codigo_nbs"
                                    value="{{ old('codigo_nbs', $servico->codigo_nbs) }}" placeholder="Ex: 112013100">
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="discriminacao_padrao" class="form-label">Descrição do Serviço *</label>
                                <textarea class="form-control" id="discriminacao_padrao" name="discriminacao_padrao" rows="3">{{ old('discriminacao_padrao', $servico->discriminacao_padrao) }}</textarea>
                            </div>

                            <div class="col-md-12">
                                <a class="btn btn-link px-0" data-bs-toggle="collapse" href="#advancedFields" role="button"
                                    aria-expanded="false" aria-controls="advancedFields">
                                    Exibir Configurações Fiscais Avançadas
                                </a>
                            </div>

                            <div class="collapse mt-3" id="advancedFields">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label for="aliquota_iss" class="form-label">Alíquota ISS (%)</label>
                                        <input type="number" step="0.01" class="form-control" id="aliquota_iss"
                                            name="aliquota_iss" value="{{ old('aliquota_iss', $servico->aliquota_iss) }}">
                                    </div>

                                    <div class="col-md-3 mb-3 d-flex align-items-end">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="iss_retido"
                                                name="iss_retido" value="1"
                                                {{ old('iss_retido') ?? $servico->iss_retido ? 'checked' : '' }}>
                                            <label class="form-check-label" for="iss_retido">ISS Retido?</label>
                                        </div>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label for="observacoes" class="form-label">Observações Internas</label>
                                        <textarea class="form-control" id="observacoes" name="observacoes" rows="2">{{ old('observacoes', $servico->observacoes) }}</textarea>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" id="ativo" name="ativo"
                                                value="1" {{ old('ativo') ?? $servico->ativo ? 'checked' : '' }}>
                                            <label class="form-check-label" for="ativo">Modelo Ativo</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary me-2">Salvar Alterações</button>
                            <a href="{{ route('servicos.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
