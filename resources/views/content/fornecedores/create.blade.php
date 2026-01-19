@extends('layouts.layoutMaster')

@section('title', 'Novo Fornecedor')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-10">
        <div class="card border-top border-primary border-3 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent py-3">
                <h4 class="mb-0 text-primary fw-bold">
                    <i class="bx bx-building-house me-2"></i>Novo Fornecedor
                </h4>
                <a href="{{ route('fornecedores.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Voltar
                </a>
            </div>

            <form action="{{ route('fornecedores.store') }}" method="POST">
                @csrf
                <div class="card-body p-4">

                    <!-- Seção: Dados Cadastrais -->
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-primary bg-opacity-10 rounded p-2 me-3 text-primary">
                            <i class="fas fa-id-card fa-lg"></i>
                        </div>
                        <h5 class="mb-0 fw-bold text-dark">Dados Cadastrais</h5>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold" for="cnpj">CNPJ</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="cnpj" name="cnpj" placeholder="00.000.000/0000-00" oninput="formatCPFCNPJ(this)">
                                <button class="btn btn-primary" type="button" id="btn-buscar-cnpj"
                                    onclick="validarEBuscarCNPJ()" title="Buscar dados do CNPJ na Receita">
                                    <i class="fas fa-search me-1"></i> Buscar
                                </button>
                            </div>
                            @error('cnpj') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-8">
                            <label class="form-label fw-bold" for="nome">Razão Social / Nome</label>
                            <input type="text" class="form-control" id="nome" name="nome" placeholder="Nome do Fornecedor" required>
                            @error('nome') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <hr class="my-4 text-muted opacity-25">

                    <!-- Seção: Contato -->
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-success bg-opacity-10 rounded p-2 me-3 text-success">
                            <i class="fas fa-address-book fa-lg"></i>
                        </div>
                        <h5 class="mb-0 fw-bold text-dark">Informações de Contato</h5>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold" for="email">Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" placeholder="email@exemplo.com">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold" for="telefone">Telefone</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-phone"></i></span>
                                <input type="text" class="form-control" id="telefone" name="telefone" placeholder="(00) 0000-0000">
                            </div>
                        </div>
                    </div>

                    <hr class="my-4 text-muted opacity-25">

                    <!-- Seção: Endereço -->
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-warning bg-opacity-10 rounded p-2 me-3 text-warning">
                            <i class="fas fa-map-marker-alt fa-lg"></i>
                        </div>
                        <h5 class="mb-0 fw-bold text-dark">Endereço</h5>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label fw-bold" for="cep">CEP</label>
                            <input type="text" class="form-control" id="cep" name="cep" placeholder="00000-000">
                        </div>
                        <div class="col-md-7">
                            <label class="form-label fw-bold" for="endereco">Logradouro</label>
                            <input type="text" class="form-control" id="endereco" name="endereco">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold" for="numero">Número</label>
                            <input type="text" class="form-control" id="numero" name="numero">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold" for="bairro">Bairro</label>
                            <input type="text" class="form-control" id="bairro" name="bairro">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" for="cidade">Cidade</label>
                            <input type="text" class="form-control" id="cidade" name="cidade">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold" for="uf">UF</label>
                            <input type="text" class="form-control" id="uf" name="uf" maxlength="2">
                        </div>
                    </div>

                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-1"></i> Salvar Fornecedor
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script>
    function validarEBuscarCNPJ() {
        const cnpjInput = document.getElementById('cnpj');
        const valor = cnpjInput.value.replace(/\D/g, '');

        if (!valor) {
            alert('Por favor, digite um CNPJ antes de buscar.');
            cnpjInput.focus();
            return;
        }

        if (valor.length !== 14) {
            alert('Para realizar a busca, digite um CNPJ válido (14 dígitos).');
            cnpjInput.focus();
            return;
        }

        // Chama a função global definida em scripts.blade.php
        // Parâmetros: cnpjInputId, nomeId, cepId, enderecoId, numeroId, bairroId, cidadeId, estadoId, telefoneId, emailId
        buscarCNPJ('cnpj', 'nome', 'cep', 'endereco', 'numero', 'bairro', 'cidade', 'uf', 'telefone', 'email');
    }

    $(document).ready(function() {
        // Auto-busca CEP
        autoBuscarCEP('cep', 'endereco', 'bairro', 'cidade', 'uf', 'numero');
    });
</script>
@endsection