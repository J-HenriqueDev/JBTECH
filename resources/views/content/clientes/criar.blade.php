@extends('layouts.layoutMaster')

@section('title', 'Novo Cliente')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-10">
        <div class="card border-top border-primary border-3 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent py-3">
                <h4 class="mb-0 text-primary fw-bold">
                    <i class="fas fa-user-plus me-2"></i>Novo Cliente
                </h4>
                <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Voltar
                </a>
            </div>

            <form action="{{ route('clientes.store') }}" method="POST">
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
                            <label class="form-label fw-bold" for="cpf">CPF / CNPJ</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="cpf" name="cpf"
                                    placeholder="000.000.000-00" oninput="formatCPFCNPJ(this)" required>
                                <button class="btn btn-primary" type="button" id="btn-buscar-cnpj"
                                    onclick="validarEBuscarCNPJ()" title="Buscar dados do CNPJ na Receita">
                                    <i class="fas fa-search me-1"></i> Buscar
                                </button>
                            </div>
                            @error('cpf') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-8">
                            <label class="form-label fw-bold" for="nome">Nome Completo / Razão Social</label>
                            <input type="text" class="form-control" id="nome" name="nome" placeholder="Ex: João Silva ou Empresa LTDA" required>
                            @error('nome') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3" id="data_nascimento_container" style="display:none;">
                            <label class="form-label fw-bold" for="data_nascimento">Data de Nascimento</label>
                            <input type="date" class="form-control" id="data_nascimento" name="data_nascimento">
                        </div>

                        <!-- Dados Fiscais (CNPJ) -->
                        <div class="col-md-3" id="inscricao_estadual_container">
                            <label class="form-label fw-bold" for="inscricao_estadual">Inscrição Estadual</label>
                            <input type="text" class="form-control" id="inscricao_estadual" name="inscricao_estadual" placeholder="Isento ou Número">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold" for="inscricao_municipal">Inscrição Municipal</label>
                            <input type="text" class="form-control" id="inscricao_municipal" name="inscricao_municipal" placeholder="Opcional">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold" for="indicador_ie">Indicador da IE</label>
                            <select class="form-select" id="indicador_ie" name="indicador_ie">
                                <option value="1">Contribuinte ICMS</option>
                                <option value="2">Contribuinte Isento</option>
                                <option value="9" selected>Não Contribuinte</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold" for="suframa">Suframa</label>
                            <input type="text" class="form-control" id="suframa" name="suframa" placeholder="Se houver">
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
                        <div class="col-md-3">
                            <label class="form-label fw-bold" for="telefone">Telefone Principal</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-phone"></i></span>
                                <input type="text" class="form-control" id="telefone" name="telefone"
                                    placeholder="(00) 00000-0000" oninput="formatPhone(this)" required>
                            </div>
                            @error('telefone') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold" for="telefone_secundario">Telefone Secundário</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-phone-alt"></i></span>
                                <input type="text" class="form-control" id="telefone_secundario" name="telefone_secundario"
                                    placeholder="(00) 00000-0000" oninput="formatPhone(this)">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold" for="email">E-mail Principal</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold" for="email_secundario">E-mail Secundário</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-envelope-open"></i></span>
                                <input type="email" class="form-control" id="email_secundario" name="email_secundario">
                            </div>
                        </div>
                    </div>

                    <hr class="my-4 text-muted opacity-25">

                    <!-- Seção: Endereço -->
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-warning bg-opacity-10 rounded p-2 me-3 text-warning">
                            <i class="fas fa-map-marker-alt fa-lg"></i>
                        </div>
                        <h5 class="mb-0 fw-bold text-dark">Endereço Completo</h5>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label fw-bold" for="cep">CEP</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="cep" name="cep"
                                    placeholder="00000-000" oninput="formatCEP(this)" required>
                                <button class="btn btn-outline-secondary" type="button"
                                    onclick="buscarCEP('cep', 'endereco', 'bairro', 'cidade', 'estado', 'numero')">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            @error('cep') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-5">
                            <label class="form-label fw-bold" for="endereco">Rua / Avenida</label>
                            <input type="text" class="form-control" id="endereco" name="endereco" required>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-bold" for="numero">Número</label>
                            <input type="text" class="form-control" id="numero" name="numero" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold" for="bairro">Bairro</label>
                            <input type="text" class="form-control" id="bairro" name="bairro" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold" for="cidade">Cidade</label>
                            <input type="text" class="form-control" id="cidade" name="cidade" required>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-bold" for="estado">UF</label>
                            <input type="text" class="form-control" id="estado" name="estado" maxlength="2" required>
                        </div>
                    </div>

                </div>

                <div class="card-footer bg-light px-4 py-3 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-label-secondary" onclick="window.history.back()">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">
                        <i class="fas fa-check me-2"></i> Salvar Cliente
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script>
    function validarEBuscarCNPJ() {
        const cpfInput = document.getElementById('cpf');
        const valor = cpfInput.value.replace(/\D/g, '');

        if (!valor) {
            alert('Por favor, digite um CNPJ antes de buscar.');
            cpfInput.focus();
            return;
        }

        if (valor.length !== 14) {
            alert('Para realizar a busca, digite um CNPJ válido (14 dígitos).');
            cpfInput.focus();
            return;
        }

        // Chama a função global definida em scripts.blade.php
        buscarCNPJ('cpf', 'nome', 'cep', 'endereco', 'numero', 'bairro', 'cidade', 'estado', 'telefone', 'email');
    }

    $(document).ready(function() {
        // Controle de exibição de campos baseado no tamanho do CPF/CNPJ
        $('#cpf').on('input', function() {
            var cpfCnpj = $(this).val().replace(/\D/g, '');

            if (cpfCnpj.length > 11) {
                // É CNPJ
                $('#data_nascimento_container').hide();
                $('#inscricao_estadual_container').show();
                $('#btn-buscar-cnpj').prop('disabled', false);

                // Busca automática se tiver 14 dígitos
                if (cpfCnpj.length === 14) {
                    buscarCNPJ('cpf', 'nome', 'cep', 'endereco', 'numero', 'bairro', 'cidade', 'estado', 'telefone', 'email');
                }
            } else {
                // É CPF
                $('#data_nascimento_container').show();
                $('#inscricao_estadual_container').show(); // Alterado: deixar visível sempre, pois PF pode ter IE
                $('#btn-buscar-cnpj').prop('disabled', true);
            }
        });

        // Trigger inicial para ajustar campos
        $('#cpf').trigger('input');

        // Auto-busca CEP
        autoBuscarCEP('cep', 'endereco', 'bairro', 'cidade', 'estado', 'numero');

        // Auto-busca CNPJ ao sair do campo (opcional, já que temos o botão agora, mas bom manter)
        // autoBuscarCNPJ('cpf', 'nome', 'cep', 'endereco', 'numero', 'bairro', 'cidade', 'estado', 'telefone', 'email');
    });
</script>
@endsection