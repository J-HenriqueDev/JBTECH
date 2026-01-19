@extends('layouts.layoutMaster')

@section('title', 'Editar Cliente')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-10">
        <div class="card border-top border-primary border-3 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent py-3">
                <h4 class="mb-0 text-primary fw-bold">
                    <i class="fas fa-user-edit me-2"></i>Editar Cliente
                </h4>
                <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Voltar
                </a>
            </div>

            <form action="{{ route('clientes.update', $cliente->id) }}" method="POST">
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
                        <div class="col-md-2">
                            <label class="form-label fw-bold" for="id">ID</label>
                            <input type="text" class="form-control" id="id" value="{{ $cliente->id }}" readonly disabled style="background-color: #e9ecef;">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold" for="cpf">CPF / CNPJ</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="cpf" name="cpf"
                                    value="{{ old('cpf', formatarCpfCnpj($cliente->cpf_cnpj)) }}"
                                    oninput="formatCPFCNPJ(this)" required>
                                <button class="btn btn-primary" type="button" id="btn-buscar-cnpj"
                                    onclick="validarEBuscarCNPJ()" title="Atualizar dados via Receita">
                                    <i class="fas fa-search me-1"></i>
                                </button>
                            </div>
                            @error('cpf') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-8">
                            <label class="form-label fw-bold" for="nome">Nome Completo / Razão Social</label>
                            <input type="text" class="form-control" id="nome" name="nome"
                                value="{{ old('nome', $cliente->nome) }}" required>
                            @error('nome') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3" id="data_nascimento_container" style="{{ strlen(preg_replace('/\D/', '', $cliente->cpf_cnpj)) > 11 ? 'display:none;' : '' }}">
                            <label class="form-label fw-bold" for="data_nascimento">Data de Nascimento</label>
                            <input type="date" class="form-control" id="data_nascimento" name="data_nascimento"
                                value="{{ old('data_nascimento', $cliente->data_nascimento ? \Carbon\Carbon::parse($cliente->data_nascimento)->format('Y-m-d') : '') }}">
                        </div>

                        <!-- Dados Fiscais (CNPJ) -->
                        <div class="col-md-3" id="inscricao_estadual_container">
                            <label class="form-label fw-bold" for="inscricao_estadual">Inscrição Estadual</label>
                            <input type="text" class="form-control" id="inscricao_estadual" name="inscricao_estadual"
                                value="{{ old('inscricao_estadual', $cliente->inscricao_estadual) }}" placeholder="Isento ou Número">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold" for="inscricao_municipal">Inscrição Municipal</label>
                            <input type="text" class="form-control" id="inscricao_municipal" name="inscricao_municipal"
                                value="{{ old('inscricao_municipal', $cliente->inscricao_municipal) }}" placeholder="Opcional">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold" for="indicador_ie">Indicador da IE</label>
                            <select class="form-select" id="indicador_ie" name="indicador_ie">
                                <option value="1" {{ old('indicador_ie', $cliente->indicador_ie) == 1 ? 'selected' : '' }}>Contribuinte ICMS</option>
                                <option value="2" {{ old('indicador_ie', $cliente->indicador_ie) == 2 ? 'selected' : '' }}>Contribuinte Isento</option>
                                <option value="9" {{ old('indicador_ie', $cliente->indicador_ie) == 9 ? 'selected' : '' }}>Não Contribuinte</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold" for="suframa">Suframa</label>
                            <input type="text" class="form-control" id="suframa" name="suframa"
                                value="{{ old('suframa', $cliente->suframa) }}" placeholder="Se houver">
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
                                    value="{{ old('telefone', $cliente->telefone) }}"
                                    placeholder="(00) 00000-0000" oninput="formatPhone(this)" required>
                            </div>
                            @error('telefone') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold" for="telefone_secundario">Telefone Secundário</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-phone-alt"></i></span>
                                <input type="text" class="form-control" id="telefone_secundario" name="telefone_secundario"
                                    value="{{ old('telefone_secundario', $cliente->telefone_secundario) }}"
                                    placeholder="(00) 00000-0000" oninput="formatPhone(this)">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold" for="email">E-mail Principal</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="{{ old('email', $cliente->email) }}" required>
                            </div>
                            @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold" for="email_secundario">E-mail Secundário</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-envelope-open"></i></span>
                                <input type="email" class="form-control" id="email_secundario" name="email_secundario"
                                    value="{{ old('email_secundario', $cliente->email_secundario) }}">
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
                                    value="{{ old('cep', $cliente->endereco->cep) }}"
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
                            <input type="text" class="form-control" id="endereco" name="endereco"
                                value="{{ old('endereco', $cliente->endereco->endereco) }}" required>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-bold" for="numero">Número</label>
                            <input type="text" class="form-control" id="numero" name="numero"
                                value="{{ old('numero', $cliente->endereco->numero) }}" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold" for="bairro">Bairro</label>
                            <input type="text" class="form-control" id="bairro" name="bairro"
                                value="{{ old('bairro', $cliente->endereco->bairro) }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold" for="cidade">Cidade</label>
                            <input type="text" class="form-control" id="cidade" name="cidade"
                                value="{{ old('cidade', $cliente->endereco->cidade) }}" required>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-bold" for="estado">UF</label>
                            <input type="text" class="form-control" id="estado" name="estado" maxlength="2"
                                value="{{ old('estado', $cliente->endereco->estado) }}" required>
                        </div>
                    </div>

                </div>

                <div class="card-footer bg-light px-4 py-3 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-label-secondary" onclick="window.history.back()">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">
                        <i class="fas fa-save me-2"></i> Atualizar
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

        buscarCNPJ('cpf', 'nome', 'cep', 'endereco', 'numero', 'bairro', 'cidade', 'estado', 'telefone', 'email');
    }

    $(document).ready(function() {
        $('#cpf').on('input', function() {
            var cpfCnpj = $(this).val().replace(/\D/g, '');

            if (cpfCnpj.length > 11) {
                $('#data_nascimento_container').hide();
                $('#btn-buscar-cnpj').prop('disabled', false);

                // Busca automática se tiver 14 dígitos
                if (cpfCnpj.length === 14) {
                    buscarCNPJ('cpf', 'nome', 'cep', 'endereco', 'numero', 'bairro', 'cidade', 'estado', 'telefone', 'email');
                }
            } else {
                $('#data_nascimento_container').show();
                $('#btn-buscar-cnpj').prop('disabled', true);
            }
        });

        // Trigger inicial
        $('#cpf').trigger('input');

        // Auto-busca CEP
        autoBuscarCEP('cep', 'endereco', 'bairro', 'cidade', 'estado', 'numero');
    });
</script>
@endsection
