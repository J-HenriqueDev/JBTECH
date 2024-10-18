@extends('layouts.layoutMaster')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/select2/select2.scss',
  'resources/assets/vendor/libs/typeahead-js/typeahead.scss'
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/select2/select2.js'
])
@endsection

@section('page-script')
@vite([
  'resources/assets/js/forms-selects.js'
])
@endsection

@section('content')
<h1>Nova Ordem de Serviço</h1>
<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <form action="" method="post">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <!-- Campo Cliente -->
                        <div class="col-4">
                            <div class="mb-3">
                                <label for="cliente_id">
                                  <i class="fas fa-id-card"></i> Cliente
                              </label>
                                <select id="select2Basic" class="form-select" data-live-search="true">
                                    <option value="" disabled selected>Selecione um cliente</option>
                                    @foreach ($clientes as $cliente)
                                    <option value="{{$cliente->id}}">
                                        {{$cliente->nome}}
                                    </option>
                                    @endforeach
                                </select>
                                @error('cliente_id')
                                <small class="text-danger fw-bold">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <!-- Campo Data de Entrada -->
                        <div class="col-2">
                            <div class="mb-3">
                                <label for="data" class="form-label">Data de entrada</label>
                                <input type="date" class="form-control" name="data_do_gasto" id="data" value="{{ date('Y-m-d') }}">
                                @error('data_do_gasto')
                                <small class="text-danger fw-bold">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <!-- Campo Tipo de Equipamento -->
                        <div class="col-3">
                            <div class="mb-3">
                                <label for="tipo_id">
                                  <i class="fas fa-id-card"></i> Tipo de equipamento
                              </label>
                                <select class="form-control" id="tipo_id" name="tipo_id">
                                    <option value="" disabled selected>Selecione um tipo</option>
                                    <!-- Adicione opções aqui -->
                                </select>
                                @error('tipo_id')
                                <small class="text-danger fw-bold">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <!-- Campo Prazo de Entrega -->
                        <div class="col-2">
                            <div class="mb-3">
                                <label for="prazo_entrega" class="form-label">Prazo de entrega</label>
                                <input type="date" class="form-control" name="prazo_entrega" id="prazo_entrega" value="{{ date('Y-m-d') }}">
                                @error('prazo_entrega')
                                <small class="text-danger fw-bold">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Campo Problema Apresentado -->
                        <div class="col-sm-6">
                            <label for="descricao" class="form-label">Problema apresentado</label>
                            <textarea class="form-control" name="problema_item" id="descricao" rows="4"
                                placeholder="Computador com muita lentidão, liga e às vezes fica em tela preta." required></textarea>
                            @error('problema_item')
                            <small class="text-danger fw-bold">{{$message}}</small>
                            @enderror
                            <small>* É importante preencher a descrição de forma correta para que os técnicos possam ser mais rápidos no diagnóstico.</small>
                        </div>

                        <!-- Campo Acessórios -->
                        <div class="col-sm-5">
                            <label for="acessorio" class="form-label d-block">Acessórios</label>
                            <div class="acessorios-container">
                                <div class="form-check form-check-inline">
                                    <label class="form-check-label">
                                        <input class="form-check-input" type="checkbox" id="inlineCheckbox1" value="option1" checked> Sem Acessório
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <label class="form-check-label">
                                        <input class="form-check-input" type="checkbox" id="inlineCheckbox2" value="option2"> Carregador
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <label class="form-check-label">
                                        <input class="form-check-input" type="checkbox" id="inlineCheckbox3" value="option3"> SSD
                                    </label>
                                </div>
                            </div>
                            <div class="mt-3">
                                <label for="senha_pc">Senha do dispositivo:</label>
                                <input type="text" class="form-control" id="senha_pc" placeholder="Ex: Henrique123" aria-label="Username" aria-describedby="basic-addon1">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Tabela de Itens -->
                        <div class="col-12 mb-4">
                            <div class="card h-100">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th class="text-center">ID</th>
                                            <th class="text-center">Nome</th>
                                            <th class="text-center">Valor</th>
                                            <th class="text-center">Quantidade</th>
                                            <th class="text-right">Valor total</th>
                                            <th class="text-right">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-center">1</td>
                                            <td class="text-center">Adaptador Wifi</td>
                                            <td class="text-center">&real; 399,99</td>
                                            <td class="text-center">1</td>
                                            <td class="text-right">&real; 399,99</td>
                                            <td class="text-right">
                                                <div class="text-right">
                                                    <button type="button" rel="tooltip" class="btn btn-success btn-sm btn-icon">
                                                        <i class="tim-icons icon-settings"></i>
                                                    </button>
                                                    <button type="button" rel="tooltip" class="btn btn-danger btn-sm btn-icon">
                                                        <i class="tim-icons icon-simple-remove"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <!-- Adicione mais itens aqui conforme necessário -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end">
                    <button class="btn btn-md btn-primary fw-bold align-right mr-2">Adicionar</button>
                    <button class="btn btn-outline-secondary">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
    $(document).ready(function() {

        $('#select2Basic').select2({
            placeholder: "Selecione um cliente",
            allowClear: true
        });

        // Ajusta a altura do select2 para coincidir com os inputs
        var inputHeight = $('.form-control').outerHeight();
        $('.select2-selection').css('height', inputHeight + 'px');
    });
</script>
@endsection
