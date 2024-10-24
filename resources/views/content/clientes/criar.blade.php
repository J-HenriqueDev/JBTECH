@extends('layouts.layoutMaster')

@section('title', 'Novo Cliente')

@section('content')
<h1 class="mb-3">Cadastro de Cliente</h1>
<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
              <form action="{{ route('clientes.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <!-- Primeira linha: CPF/CNPJ, Nome/Razão Social, Inscrição Estadual/Data de Nascimento -->
                    <div class="row">
                        <div class="col-md-3"> <!-- Ajuste para manter na mesma linha -->
                            <div class="form-group">
                                <label for="cpf">
                                    <i class="fas fa-id-card"></i> CPF/CNPJ
                                </label>
                                <input type="text" class="form-control" id="cpf" name="cpf" placeholder="123.456.789-10" required>
                                @error('cpf')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-5"> <!-- Ajuste para manter na mesma linha -->
                            <div class="form-group">
                                <label for="nome">
                                    <i class="fas fa-user"></i> Nome/Razão Social
                                </label>
                                <input type="text" class="form-control" id="nome" name="nome" placeholder="José Henrique" required>
                                @error('nome')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4"> <!-- Inscrição Estadual ou Data de Nascimento -->
                            <div class="form-group" id="inscricao_estadual_container" style="display: none;">
                                <label for="inscricao_estadual" id="inscricao_estadual_label">
                                    <i class="fas fa-file-invoice"></i> Inscrição Estadual
                                </label>
                                <input type="text" class="form-control" id="inscricao_estadual" name="inscricao_estadual" placeholder="123456789" >
                                @error('inscricao_estadual')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group" id="data_nascimento_container" style="display: none;">
                                <label for="data_nascimento">
                                    <i class="fas fa-calendar-alt"></i> Data de Nascimento
                                </label>
                                <input type="date" class="form-control" id="data_nascimento" name="data_nascimento">
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
                                <input type="text" class="form-control" id="telefone" name="telefone" x-mask="(99) 99999-9999" placeholder="(24) 12345-6789" required>
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
                                <input type="email" class="form-control" id="email" name="email" placeholder="informatica.jbtech@gmail.com" required>
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
                                    <input type="text" name="cep" id="cep" class="form-control" data-mask="00000-000" placeholder="27520-000" required>
                                    @error('cep')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                    <div class="input-group-append">
                                        <button id="cep-search" class="btn btn-outline-secondary" type="button" style="height: 38px;">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="endereco">
                                    <i class="fas fa-road"></i> Endereço
                                </label>
                                <input type="text" class="form-control" id="endereco" name="endereco" placeholder="Rua Teste" required>
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
                                <input type="text" class="form-control" id="numero" name="numero" placeholder="123" required>
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
                                <input type="text" class="form-control" id="bairro" name="bairro" placeholder="Morada do Contorno" required>
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
                                <input type="text" class="form-control" id="cidade" name="cidade" placeholder="Resende" required>
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
                                <input type="text" class="form-control" id="estado" name="estado" placeholder="RJ" required>
                                @error('estado')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="divider my-6">
                      <div class="divider-text"><i class="fas fa-briefcase"></i> Tipo de Cliente</div>
                    </div>

                    <!-- Tipo de Cliente -->
                    <div class="form-group col-sm-5">
                        <label for="tipo_cliente" class="form-label d-block">
                        </label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="tipo_cliente" id="particular" value="0" checked>
                            <label class="form-check-label" for="particular">Particular</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="tipo_cliente" id="contrato" value="1">
                            <label class="form-check-label" for="contrato">Contrato</label>
                        </div>
                        @error('tipo_cliente')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="card-footer d-flex justify-content-end">
                    <button class="btn btn-md btn-primary fw-bold">Adicionar</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{--  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>  --}}
  <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.js" integrity="sha512-0XDfGxFliYJPFrideYOoxdgNIvrwGTLnmK20xZbCAvPfLGQMzHUsaqZK8ZoH+luXGRxTrS46+Aq400nCnAT0/w==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    $(document).ready(function() {

     // Máscara dinâmica para CPF/CNPJ
// Máscara dinâmica para CPF/CNPJ
        $('#cpf').on('input', function(e) {
          var value = e.target.value.replace(/\D/g, ''); // Remove caracteres não numéricos

          if (value.length <= 11) {
              // Aplica a máscara de CPF (até 11 dígitos)
              value = value.replace(/(\d{3})(\d)/, '$1.$2');
              value = value.replace(/(\d{3})(\d)/, '$1.$2');
              value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
          } else if (value.length <= 14) {
              // Aplica a máscara de CNPJ (até 14 dígitos)
              value = value.replace(/^(\d{2})(\d)/, '$1.$2');
              value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
              value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
              value = value.replace(/(\d{4})(\d{1,2})$/, '$1-$2');
          }

          e.target.value = value;
        });



        $('#cpf').blur(function() {
            var cpfCnpj = $(this).val().replace(/\D/g, '');
            if (cpfCnpj.length === 11) {
              $('#data_nascimento_container').show();
              // CPF
                $.ajax({
                    url: 'https://www.receitaws.com.br/v1/cpf/' + cpfCnpj,
                    type: 'GET',
                    dataType: 'jsonp',
                    success: function(data) {
                        if (data.status === 'OK') {
                            $('#nome').val(data.nome);
                            $('#data_nascimento').val(data.data_nascimento);
                            $('#inscricao_estadual_container').hide();
                        } else {
                            alert('CPF não encontrado.');
                        }
                    },
                    error: function() {
                        console.log('Erro ao consultar CPF.');
                    }
                });
            } else if (cpfCnpj.length === 14) { // CNPJ
              $.ajax({
                url: 'https://www.receitaws.com.br/v1/cnpj/' + cpfCnpj,
                type: 'GET',
                dataType: 'jsonp',
                success: function(data) {
                    if (data.status === 'OK') {
                        // Verificar se há nome fantasia
                        var nomeExibido = data.fantasia
                            ? '(' + data.fantasia + ') ' + data.nome
                            : data.nome;

                        // Atribuir o valor ao campo de nome
                        $('#nome').val(nomeExibido);
                        $('#inscricao_estadual').val(data.inscricao_estadual);
                        $('#inscricao_estadual_container').show();
                        $('#data_nascimento_container').hide();
                        $('#cep').val(data.cep);
                        $('#endereco').val(data.logradouro);
                        $('#numero').val(data.numero);
                        $('#bairro').val(data.bairro);
                        $('#cidade').val(data.municipio);
                        $('#estado').val(data.uf);
                        $('#telefone').val(data.telefone);
                        $('#email').val(data.email);
                    } else {
                        alert('CNPJ não encontrado.');
                    }
                },
                error: function() {
                    alert('Erro ao consultar CNPJ.');
                }
            });

            }
        });

    });
</script>
@endsection
