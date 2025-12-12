@extends('layouts.layoutMaster')

@section('title', 'Editar Cliente')

@section('content')
<h1 class="mb-3">Editar Cliente</h1>
{{-- Notificação --}}
@if(session('noti'))
    <!-- Toast Notification -->
    <div class="position-relative">
        <div class="bs-toast toast fade show bg-primary animate__animated animate__tada position-absolute end-0" role="alert" aria-live="assertive" aria-atomic="true" style="z-index: 1050; white-space: nowrap;">
            <div class="toast-header">
                <i class='bx bx-bell me-2'></i>
                <div class="me-auto fw-medium">Notificação</div>
                <small>Agora</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                <strong>{{ session('cliente_nome') }}</strong> {{ session('noti') }}
            </div>
        </div>
    </div>

    <script>
        // Remove the toast after 3 seconds
        setTimeout(() => {
            const toastEl = document.querySelector('.bs-toast');
            if (toastEl) {
                const bsToast = new bootstrap.Toast(toastEl);
                bsToast.hide();
            }
        }, 3000);
    </script>
@endif

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <form action="{{ route('clientes.update', $cliente->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <!-- Primeira linha: CPF/CNPJ, Nome/Razão Social, Inscrição Estadual/Data de Nascimento -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="cpf">
                                    <i class="fas fa-id-card"></i> CPF/CNPJ
                                </label>
                                <input type="text" class="form-control" id="cpf" name="cpf" value="{{ old('cpf', formatarCpfCnpj($cliente->cpf_cnpj)) }}" readonly>
                                @error('cpf')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="nome">
                                    <i class="fas fa-user"></i> Nome/Razão Social
                                </label>
                                <input type="text" class="form-control" id="nome" name="nome" value="{{ old('nome', $cliente->nome) }}" required>
                                @error('nome')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group" id="inscricao_estadual_container" style="{{ $cliente->cpf && strlen($cliente->cpf) === 11 ? 'display: none;' : '' }}">
                                <label for="inscricao_estadual" id="inscricao_estadual_label">
                                    <i class="fas fa-file-invoice"></i> Inscrição Estadual
                                </label>
                                <input type="text" class="form-control" id="inscricao_estadual" name="inscricao_estadual" value="{{ old('inscricao_estadual', $cliente->inscricao_estadual) }}">
                                @error('inscricao_estadual')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group" id="data_nascimento_container" style="{{ $cliente->cpf && strlen($cliente->cpf) === 11 ? '' : 'display: none;' }}">
                                <label for="data_nascimento">
                                    <i class="fas fa-calendar-alt"></i> Data de Nascimento
                                </label>
                                <input type="date" class="form-control" id="data_nascimento" name="data_nascimento" value="{{ old('data_nascimento', $cliente->data_nascimento) }}">
                                @error('data_nascimento')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Outras linhas -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="telefone">
                                    <i class="fas fa-phone"></i> Telefone
                                </label>
                                <input type="text" class="form-control" id="telefone" name="telefone" value="{{ old('telefone', $cliente->telefone) }}" placeholder="(00) 00000-0000" oninput="formatPhone(this)" required>
                                @error('telefone')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="email">
                                    <i class="fas fa-envelope"></i> E-mail
                                </label>
                                <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $cliente->email) }}" required>
                                @error('email')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Endereço -->
                    <div class="row mt-4">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="cep">
                                    <i class="fas fa-map-marker-alt"></i> CEP
                                </label>
                                <div class="input-group">
                                    <input type="text" name="cep" id="cep" class="form-control" value="{{ old('cep', $cliente->endereco->cep ? \Illuminate\Support\Str::substr($cliente->endereco->cep, 0, 5) . '-' . \Illuminate\Support\Str::substr($cliente->endereco->cep, 5) : '') }}" placeholder="00000-000" oninput="formatCEP(this)" required>
                                    <button type="button" class="btn btn-outline-secondary" onclick="buscarCEP('cep', 'endereco', 'bairro', 'cidade', 'estado', 'numero')">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                                @error('cep')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="endereco">
                                    <i class="fas fa-road"></i> Endereço
                                </label>
                                <input type="text" class="form-control" id="endereco" name="endereco" value="{{ old('endereco', $cliente->endereco->endereco) }}" required>
                                @error('endereco')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="numero">
                                    <i class="fas fa-home"></i> Número
                                </label>
                                <input type="text" class="form-control" id="numero" name="numero" value="{{ old('numero', $cliente->endereco->numero) }}" required>
                                @error('numero')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Bairro, Cidade, Estado -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="bairro">
                                    <i class="fas fa-map-pin"></i> Bairro
                                </label>
                                <input type="text" class="form-control" id="bairro" name="bairro" value="{{ old('bairro', $cliente->endereco->bairro) }}" required>
                                @error('bairro')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="cidade">
                                    <i class="fas fa-city"></i> Cidade
                                </label>
                                <input type="text" class="form-control" id="cidade" name="cidade" value="{{ old('cidade', $cliente->endereco->cidade) }}" required>
                                @error('cidade')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="estado">
                                    <i class="fas fa-globe-americas"></i> Estado
                                </label>
                                <input type="text" class="form-control" id="estado" name="estado" value="{{ old('estado', $cliente->endereco->estado) }}" required>
                                @error('estado')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Tipo de Cliente -->
                    <div class="divider my-6">
                        <div class="divider-text"><i class="fas fa-briefcase"></i> Tipo de Cliente</div>
                    </div>

                    <div class="form-group col-sm-5">
                        <label for="tipo_cliente" class="form-label d-block">
                        </label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="tipo_cliente" id="particular" value="0" {{ old('tipo_cliente', $cliente->tipo_cliente) == 0 ? 'checked' : '' }}>
                            <label class="form-check-label" for="particular">Particular</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="tipo_cliente" id="empresarial" value="1" {{ old('tipo_cliente', $cliente->tipo_cliente) == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="empresarial">Empresarial</label>
                        </div>
                    </div>

                </div>

                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary">Atualizar</button>
                      <button type="button" class="btn btn-secondary me-2" onclick="window.history.back();">
                        <i class="bx bx-x"></i> Cancelar
                      </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script>
    $(document).ready(function() {
        // Auto-busca CEP ao sair do campo
        autoBuscarCEP('cep', 'endereco', 'bairro', 'cidade', 'estado', 'numero');
    });
</script>
@endsection
