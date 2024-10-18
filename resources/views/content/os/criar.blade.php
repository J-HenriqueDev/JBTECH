@extends('layouts.layoutMaster')

<!-- Vendor Styles -->
@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/select2/select2.scss',
  'resources/assets/vendor/libs/typeahead-js/typeahead.scss'
])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/select2/select2.js'

])
@endsection

<!-- Page Scripts -->
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
                      <div class="col-4">
                        <div class="mb-3">
                            <label for="cliente_id">Cliente</label>
                            <select id="select2Basic" class="select2 form-select" data-allow-clear="true">
                                <option value="" disabled selected>Selecione um cliente</option>
                                @foreach ($clientes as $cliente)
                                <option value="{{$cliente->id}}">
                                    {{$cliente->nome}}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>


                        {{--  <div class="col-0">
                            <div class="mb-0">
                                <button class="btn btn-primary btn-sm" type="button" onclick="window.location.href='{{ route('novo_cliente.index') }}'">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>  --}}


                        <div class="col-2">
                            <div class="mb-3">
                                <div class="form-group input-group-merge text-center">
                                    <label for="data" class="form-label ">Data de entrada</label>
                                    <div class="input-group input-group-merge">
                                        <input type="date" class="form-control" name="data_do_gasto" id="data" value="{{ date('Y-m-d') }}">
                                        @error('data_do_gasto')
                                        <small class="text-danger fw-bold">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="mb-2">
                                <div class="form-group input-group-merge text-center">
                                    <label for="tipo_id">Tipo de equipamento</label>
                                    <select class="form-control" id="tipo_id">
                                        <option value="" disabled selected>Selecione um tipo</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="mb-3">
                                <div class="form-group input-group-merge">
                                    <label for="prazo_entrega" class="form-label">Prazo de entrega</label>
                                    <div class="input-group input-group-merge">
                                        <input type="date" class="form-control" name="prazo_entrega" id="prazo_entrega" value="{{ date('Y-m-d') }}">
                                        @error('prazo_entrega')
                                        <small class="text-danger fw-bold">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <label for="descricao" class="form-label">Problema apresentado</label>
                            <textarea class="form-control" name="problema_item" id="descricao" rows="4"
                                placeholder="Computador com muita lentidão, liga e as vezes fica em tela preta." required></textarea>
                            @error('problema_item')
                            <small class="text-danger fw-bold">{{$message}}</small>
                            @enderror
                            <small>* É importante preencher a descrição de forma correta para que os técnicos possam ser mais rápidos no diagnostico.</small>
                        </div>


                        <div class="col-sm-5">
                            <label for="acessorio" class="form-label d-block">Acessórios</label>
                            <div class="acessorios-container">
                                <div class="form-check form-check-inline">
                                    <label class="form-check-label">
                                        <input class="form-check-input" type="checkbox" id="inlineCheckbox1" value="option1" checked> Sem Acessório
                                        <span class="form-check-sign"></span>
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <label class="form-check-label">
                                        <input class="form-check-input" type="checkbox" id="inlineCheckbox2" value="option2"> Carregador
                                        <span class="form-check-sign"></span>
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <label class="form-check-label">
                                        <input class="form-check-input" type="checkbox" id="inlineCheckbox3" value="option3"> SSD
                                        <span class="form-check-sign"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="mt-3">
                                <label for="senha_pc">Senha do dispositivo:</label>
                                <input type="text" class="form-control" id="senha_pc" placeholder="Ex: Henrique123" aria-label="Username" aria-describedby="basic-addon1">
                            </div>
                        </div>




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
                                            <td class="text-right">&real; 399,29</td>
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
                                    </tbody>
                                </table>
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


  <style>
    .card-body > .row {
        display: flex;
        flex-wrap: wrap; /* Permite que os itens quebrem a linha, se necessário */
        align-items: flex-start; /* Alinha os itens no topo */
    }

    .col-4, .col-2, .col-3, .col-sm-6, .col-sm-5 {
        display: flex; /* Flex para garantir que os itens dentro da coluna também se comportem bem */
        flex-direction: column; /* Permite o alinhamento vertical */
        margin-bottom: 1rem; /* Espaçamento inferior entre as colunas */
    }

    /* Ajusta o espaço entre a primeira e a segunda linha */
    .card-body .row > div {
        margin-bottom: 0.5rem; /* Diminui a distância entre as linhas */
    }

    .col-sm-6 {
        display: flex;
        flex-direction: column; /* Para o textarea */
        justify-content: flex-end; /* Alinha ao final */
    }

    .col-sm-5 {
        display: flex;
        flex-direction: column; /* Para os acessórios */
    }

    /* Alinhamento do campo "descreva o problema" */
    #descricao {
        margin-top: auto; /* Garante que o textarea se alinhe na parte inferior */
    }

    /* Ajustes gerais para todos os campos de entrada */
    .form-control {
        min-height: 38px; /* Define uma altura mínima */
        padding: 0.375rem 0.75rem; /* Alinhamento do padding */
        border-radius: 0.375rem; /* Ajuste do border-radius, se necessário */
        border: 1px solid #ced4da; /* Bordas para manter consistência */
        box-sizing: border-box; /* Garante que padding e border não aumentem a largura total */
    }
  </style>

<script>
    $(document).ready(function() {
        $('#select2Basic').select2({
            placeholder: "Selecione um cliente", // Define o placeholder
            allowClear: true // Permite limpar a seleção
        });

        // Alinhamento da altura
        var inputHeight = $('.form-control').outerHeight();
        $('.select2-selection').css('height', inputHeight + 'px');
    });
</script>


@endsection
