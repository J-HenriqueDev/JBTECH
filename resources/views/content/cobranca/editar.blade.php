@extends('layouts.layoutMaster')

@section('title', 'Editar Cobrança')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0 text-primary" style="font-size: 2.5rem; font-weight: bold; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);">
        <i class="fas fa-edit"></i> Editar Cobrança #{{ $cobranca->id }}
    </h1>
    <a href="{{ route('cobrancas.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Voltar
    </a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('cobrancas.update', $cobranca->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="venda_id" class="form-label">
                        <i class="bx bx-cart"></i> Venda
                    </label>
                    <select name="venda_id" id="venda_id" class="form-select" required>
                        <option value="" disabled>Selecione uma venda</option>
                        @foreach ($vendas as $venda)
                        <option value="{{ $venda->id }}" {{ $cobranca->venda_id == $venda->id ? 'selected' : '' }}>
                            Venda #{{ $venda->id }} - {{ $venda->cliente->nome ?? 'N/A' }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="metodo_pagamento" class="form-label">
                        <i class="bx bx-credit-card"></i> Método de Pagamento
                    </label>
                    <select name="metodo_pagamento" id="metodo_pagamento" class="form-select" required>
                        <option value="pix" {{ $cobranca->metodo_pagamento == 'pix' ? 'selected' : '' }}>PIX</option>
                        <option value="boleto" {{ $cobranca->metodo_pagamento == 'boleto' ? 'selected' : '' }}>Boleto</option>
                        <option value="cartao_credito" {{ $cobranca->metodo_pagamento == 'cartao_credito' ? 'selected' : '' }}>Cartão de Crédito</option>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="valor" class="form-label">
                        <i class="bx bx-money"></i> Valor
                    </label>
                    <input type="text" class="form-control" name="valor" id="valor" value="{{ number_format($cobranca->valor, 2, ',', '.') }}" oninput="formatCurrency(this)">
                </div>
                <div class="col-md-6">
                    <label for="status" class="form-label">
                        <i class="bx bx-info-circle"></i> Status
                    </label>
                    <select name="status" id="status" class="form-select">
                        <option value="pendente" {{ $cobranca->status == 'pendente' ? 'selected' : '' }}>Pendente</option>
                        <option value="pago" {{ $cobranca->status == 'pago' ? 'selected' : '' }}>Pago</option>
                        <option value="cancelado" {{ $cobranca->status == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="data_vencimento" class="form-label">
                        <i class="bx bx-calendar"></i> Data de Vencimento
                    </label>
                    <input type="date" class="form-control" name="data_vencimento" id="data_vencimento" value="{{ $cobranca->data_vencimento ? \Carbon\Carbon::parse($cobranca->data_vencimento)->format('Y-m-d') : '' }}">
                </div>
                <div class="col-md-6">
                    <label for="recorrente" class="form-label">
                        <i class="bx bx-refresh"></i> Cobrança Recorrente
                    </label>
                    <select name="recorrente" id="recorrente" class="form-select">
                        <option value="0" {{ !$cobranca->recorrente ? 'selected' : '' }}>Não</option>
                        <option value="1" {{ $cobranca->recorrente ? 'selected' : '' }}>Sim</option>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="frequencia_recorrencia" class="form-label">
                        <i class="bx bx-calendar"></i> Frequência de Recorrência
                    </label>
                    <select name="frequencia_recorrencia" id="frequencia_recorrencia" class="form-select">
                        <option value="">Selecione...</option>
                        <option value="1 month" {{ $cobranca->frequencia_recorrencia == '1 month' ? 'selected' : '' }}>Mensal</option>
                        <option value="3 months" {{ $cobranca->frequencia_recorrencia == '3 months' ? 'selected' : '' }}>Trimestral</option>
                        <option value="6 months" {{ $cobranca->frequencia_recorrencia == '6 months' ? 'selected' : '' }}>Semestral</option>
                        <option value="1 year" {{ $cobranca->frequencia_recorrencia == '1 year' ? 'selected' : '' }}>Anual</option>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="enviar_email" id="enviar_email" value="1" {{ $cobranca->enviar_email ? 'checked' : '' }}>
                        <label class="form-check-label" for="enviar_email">
                            <i class="bx bx-envelope"></i> Enviar por E-mail
                        </label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="enviar_whatsapp" id="enviar_whatsapp" value="1" {{ $cobranca->enviar_whatsapp ? 'checked' : '' }}>
                        <label class="form-check-label" for="enviar_whatsapp">
                            <i class="bx bx-phone"></i> Enviar por WhatsApp
                        </label>
                    </div>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-end">
                <button type="button" class="btn btn-outline-secondary me-2" onclick="history.back();">
                    <i class="bx bx-x"></i> Cancelar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="bx bx-save"></i> Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function formatCurrency(input) {
        let value = input.value.replace(/\D/g, '');
        value = (value / 100).toFixed(2) + '';
        value = value.replace('.', ',');
        value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        input.value = value;
    }
</script>

@endsection
