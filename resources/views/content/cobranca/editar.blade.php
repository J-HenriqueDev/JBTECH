@extends('layouts.layoutMaster')

@section('content')
<div class="container">
    <h1 class="mb-4 text-primary">Editar Cobrança</h1>
    <div class="card">
        <div class="card-body">
            <form action="{{ route('cobranca.atualizar', $cobranca->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="venda_id" class="form-label">Venda</label>
                    <select name="venda_id" id="venda_id" class="form-select" required>
                        <option value="" disabled>Selecione uma venda</option>
                        @foreach ($vendas as $venda)
                        <option value="{{ $venda->id }}" {{ $cobranca->venda_id == $venda->id ? 'selected' : '' }}>
                            Venda #{{ $venda->id }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="metodo_pagamento" class="form-label">Método de Pagamento</label>
                    <select name="metodo_pagamento" id="metodo_pagamento" class="form-select" required>
                        <option value="pix" {{ $cobranca->metodo_pagamento == 'pix' ? 'selected' : '' }}>PIX</option>
                        <option value="boleto" {{ $cobranca->metodo_pagamento == 'boleto' ? 'selected' : '' }}>Boleto</option>
                        <option value="cartao_credito" {{ $cobranca->metodo_pagamento == 'cartao_credito' ? 'selected' : '' }}>Cartão de Crédito</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="recorrente" class="form-label">Cobrança Recorrente</label>
                    <select name="recorrente" id="recorrente" class="form-select">
                        <option value="0" {{ !$cobranca->recorrente ? 'selected' : '' }}>Não</option>
                        <option value="1" {{ $cobranca->recorrente ? 'selected' : '' }}>Sim</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="frequencia_recorrencia" class="form-label">Frequência de Recorrência</label>
                    <select name="frequencia_recorrencia" id="frequencia_recorrencia" class="form-select">
                        <option value="1 month" {{ $cobranca->frequencia_recorrencia == '1 month' ? 'selected' : '' }}>Mensal</option>
                        <option value="3 months" {{ $cobranca->frequencia_recorrencia == '3 months' ? 'selected' : '' }}>Trimestral</option>
                        <option value="6 months" {{ $cobranca->frequencia_recorrencia == '6 months' ? 'selected' : '' }}>Semestral</option>
                        <option value="1 year" {{ $cobranca->frequencia_recorrencia == '1 year' ? 'selected' : '' }}>Anual</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="enviar_email" class="form-label">Enviar por E-mail</label>
                    <input type="checkbox" name="enviar_email" id="enviar_email" value="1" {{ $cobranca->enviar_email ? 'checked' : '' }}>
                </div>
                <div class="mb-3">
                    <label for="enviar_whatsapp" class="form-label">Enviar por WhatsApp</label>
                    <input type="checkbox" name="enviar_whatsapp" id="enviar_whatsapp" value="1" {{ $cobranca->enviar_whatsapp ? 'checked' : '' }}>
                </div>
                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            </form>
        </div>
    </div>
</div>
@endsection
