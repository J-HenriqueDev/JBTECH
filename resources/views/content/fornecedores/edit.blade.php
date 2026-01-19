@extends('layouts.layoutMaster')

@section('title', 'Editar Fornecedor')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-10">
        <div class="card border-top border-primary border-3 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent py-3">
                <h4 class="mb-0 text-primary fw-bold">
                    <i class="bx bx-building-house me-2"></i>Editar Fornecedor
                </h4>
                <a href="{{ route('fornecedores.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Voltar
                </a>
            </div>

            <form action="{{ route('fornecedores.update', $fornecedor->id) }}" method="POST">
                @csrf
                @method('PUT')
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
                                <input type="text" class="form-control" id="cnpj" name="cnpj" placeholder="00.000.000/0000-00" value="{{ old('cnpj', $fornecedor->cnpj) }}" oninput="formatCPFCNPJ(this)">
                                <button class="btn btn-primary" type="button" id="btn-buscar-cnpj"
                                    onclick="validarEBuscarCNPJ()" title="Buscar dados do CNPJ na Receita">
                                    <i class="fas fa-search me-1"></i> Buscar
                                </button>
                            </div>
                            @error('cnpj') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-8">
                            <label class="form-label fw-bold" for="nome">Razão Social / Nome</label>
                            <input type="text" class="form-control" id="nome" name="nome" placeholder="Nome do Fornecedor" value="{{ old('nome', $fornecedor->nome) }}" required>
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
                                <input type="email" class="form-control" id="email" name="email" placeholder="email@exemplo.com" value="{{ old('email', $fornecedor->email) }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold" for="telefone">Telefone</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-phone"></i></span>
                                <input type="text" class="form-control" id="telefone" name="telefone" placeholder="(00) 0000-0000" value="{{ old('telefone', $fornecedor->telefone) }}">
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
                            <input type="text" class="form-control" id="cep" name="cep" placeholder="00000-000" value="{{ old('cep', $fornecedor->cep) }}">
                        </div>
                        <div class="col-md-7">
                            <label class="form-label fw-bold" for="endereco">Logradouro</label>
                            <input type="text" class="form-control" id="endereco" name="endereco" value="{{ old('endereco', $fornecedor->endereco) }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold" for="numero">Número</label>
                            <input type="text" class="form-control" id="numero" name="numero" value="{{ old('numero', $fornecedor->numero) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold" for="bairro">Bairro</label>
                            <input type="text" class="form-control" id="bairro" name="bairro" value="{{ old('bairro', $fornecedor->bairro) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" for="cidade">Cidade</label>
                            <input type="text" class="form-control" id="cidade" name="cidade" value="{{ old('cidade', $fornecedor->cidade) }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold" for="uf">UF</label>
                            <input type="text" class="form-control" id="uf" name="uf" maxlength="2" value="{{ old('uf', $fornecedor->uf) }}">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="fas fa-trash me-1"></i> Excluir
                        </button>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-1"></i> Atualizar Fornecedor
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
                <p>Tem certeza que deseja excluir o fornecedor <strong>{{ $fornecedor->nome }}</strong>?</p>
                <p class="text-danger"><small>Esta ação não poderá ser desfeita se não houver registros vinculados.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form action="{{ route('fornecedores.destroy', $fornecedor->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
            </div>
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
        buscarCNPJ('cnpj', 'nome', 'cep', 'endereco', 'numero', 'bairro', 'cidade', 'uf', 'telefone', 'email');
    }

    $(document).ready(function() {
        // Auto-busca CEP
        autoBuscarCEP('cep', 'endereco', 'bairro', 'cidade', 'uf', 'numero');
    });
</script>
@endsection