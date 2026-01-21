@extends('layouts.layoutMaster')

@section('title', 'Nova NFS-e')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0 text-primary">
        <i class="bx bx-plus-circle"></i> Nova Nota Fiscal de Serviço
    </h1>
    <a href="{{ route('nfse.index') }}" class="btn btn-outline-secondary">
        <i class="bx bx-arrow-back"></i> Voltar
    </a>
</div>

@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form action="{{ route('nfse.store') }}" method="POST">
    @csrf

    <div class="row">
        <div class="col-md-8">
            <!-- Dados do Tomador -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Dados do Tomador (Cliente)</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Cliente</label>
                        <select name="cliente_id" class="form-select select2" required>
                            <option value="">Selecione um cliente...</option>
                            @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}" {{ old('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                {{ $cliente->nome }} ({{ $cliente->cpf_cnpj }})
                            </option>
                            @endforeach
                        </select>
                        <div class="form-text">Certifique-se que o cliente possui CPF/CNPJ e Endereço cadastrados.</div>
                    </div>
                </div>
            </div>

            <!-- Detalhes do Serviço -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Detalhes do Serviço</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Discriminação do Serviço</label>
                            <textarea name="discriminacao" class="form-control" rows="4" required placeholder="Descreva o serviço prestado...">{{ old('discriminacao') }}</textarea>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Código do Serviço (LC 116/03)</label>
                            <input type="text" name="codigo_servico" class="form-control" value="{{ old('codigo_servico', '14.01') }}" placeholder="Ex: 14.01">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Município de Prestação (Código IBGE)</label>
                            <input type="text" name="municipio_prestacao" class="form-control" value="{{ old('municipio_prestacao') }}" placeholder="Deixe em branco se for no mesmo município">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Valores -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Valores e Impostos</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Valor do Serviço (R$)</label>
                        <input type="number" step="0.01" name="valor_servico" class="form-control form-control-lg" value="{{ old('valor_servico') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Alíquota ISS (%)</label>
                        <input type="number" step="0.01" name="aliquota_iss" class="form-control" value="{{ old('aliquota_iss', '2.00') }}">
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="iss_retido" name="iss_retido" {{ old('iss_retido') ? 'checked' : '' }}>
                            <label class="form-check-label" for="iss_retido">ISS Retido pelo Tomador</label>
                        </div>
                    </div>

                    <hr>

                    <div class="d-grid gap-2">
                        <button type="submit" name="action" value="save" class="btn btn-secondary">
                            <i class="bx bx-save me-1"></i> Salvar Rascunho
                        </button>
                        <button type="submit" name="emitir_agora" value="true" class="btn btn-primary">
                            <i class="bx bx-send me-1"></i> Emitir Agora
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection

@section('page-script')
<script>
    $(document).ready(function() {
        $('.select2').select2();
    });
</script>
@endsection
