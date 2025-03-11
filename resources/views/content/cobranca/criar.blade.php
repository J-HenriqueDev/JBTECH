@extends('layouts.layoutMaster')

@section('title', 'Criar Cobrança')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/select2/select2.scss',
  'resources/assets/vendor/libs/typeahead-js/typeahead.scss',
  'resources/assets/vendor/libs/swiper/swiper.scss'
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/select2/select2.js',
  'resources/assets/vendor/libs/swiper/swiper.js'
])
@endsection

@section('page-script')
@vite([
  'resources/assets/js/forms-selects.js'
])
@endsection

@section('content')
<h1 class="mb-4 text-primary" style="font-size: 2.5rem; font-weight: bold; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);">
    <i class="bx bx-file"></i> Criar Cobrança
</h1>

<div class="card mb-4">
    <form action="{{ route('cobrancas.store') }}" method="POST">
        @csrf
        <div class="card-body">
            <div class="row mb-3">
                <!-- Campo Venda -->
                <div class="col-md-6">
                    <label for="venda_id" class="form-label">
                        <i class="bx bx-cart"></i> Venda
                    </label>
                    <select id="venda_id" class="select2 form-select" name="venda_id" required>
                        <option value="" disabled selected>Selecione uma venda</option>
                        @foreach ($vendas as $venda)
                        <option value="{{ $venda->id }}">Venda #{{ $venda->id }}</option>
                        @endforeach
                    </select>
                    @error('venda_id')
                    <small class="text-danger fw-bold">{{ $message }}</small>
                    @enderror
                </div>

                <!-- Campo Método de Pagamento -->
                <div class="col-md-6">
                    <label for="metodo_pagamento" class="form-label">
                        <i class="bx bx-credit-card"></i> Método de Pagamento
                    </label>
                    <select name="metodo_pagamento" id="metodo_pagamento" class="form-select" required>
                        <option value="pix">PIX</option>
                        <option value="boleto">Boleto</option>
                        <option value="cartao_credito">Cartão de Crédito</option>
                    </select>
                    @error('metodo_pagamento')
                    <small class="text-danger fw-bold">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div class="row mb-3">
                <!-- Campo Cobrança Recorrente -->
                <div class="col-md-6">
                    <label for="recorrente" class="form-label">
                        <i class="bx bx-refresh"></i> Cobrança Recorrente
                    </label>
                    <select name="recorrente" id="recorrente" class="form-select">
                        <option value="0">Não</option>
                        <option value="1">Sim</option>
                    </select>
                </div>

                <!-- Campo Frequência de Recorrência -->
                <div class="col-md-6">
                    <label for="frequencia_recorrencia" class="form-label">
                        <i class="bx bx-calendar"></i> Frequência de Recorrência
                    </label>
                    <select name="frequencia_recorrencia" id="frequencia_recorrencia" class="form-select">
                        <option value="1 month">Mensal</option>
                        <option value="3 months">Trimestral</option>
                        <option value="6 months">Semestral</option>
                        <option value="1 year">Anual</option>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <!-- Checkbox para Enviar por E-mail -->
                <div class="col-md-6">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="enviar_email" id="enviar_email" value="1">
                        <label class="form-check-label" for="enviar_email">
                            <i class="bx bx-envelope"></i> Enviar por E-mail
                        </label>
                    </div>
                </div>

                <!-- Checkbox para Enviar por WhatsApp -->
                <div class="col-md-6">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="enviar_whatsapp" id="enviar_whatsapp" value="1">
                        <label class="form-check-label" for="enviar_whatsapp">
                            <i class="bx bx-phone"></i> Enviar por WhatsApp
                        </label>
                    </div>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-end">
                <button type="submit" class="btn btn-md btn-primary fw-bold me-2">
                    <i class="bx bx-save"></i> Salvar
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="history.back();">
                    <i class="bx bx-x"></i> Cancelar
                </button>
            </div>
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />

<script>
    $(document).ready(function() {
        $('.select2').select2();
    });
</script>

@endsection
