@extends('layouts.layoutMaster')

@section('title', 'Detalhes da NFS-e')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0 text-primary">
        <i class="bx bx-file"></i> Detalhes da NFS-e #{{ $nfse->numero_nfse ?? $nfse->id }}
    </h1>
    <div>
        <a href="{{ route('nfse.index') }}" class="btn btn-outline-secondary me-2">
            <i class="bx bx-arrow-back"></i> Voltar
        </a>
        @if($nfse->status == 'pendente' || $nfse->status == 'rejeitada')
        <form action="{{ route('nfse.emitir', $nfse->id) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-primary">
                <i class="bx bx-send me-1"></i> Emitir Agora
            </button>
        </form>
        @endif
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Informações Gerais</h5>
                @php
                $statusColor = match($nfse->status) {
                    'autorizada' => 'success',
                    'rejeitada' => 'danger',
                    'cancelada' => 'warning',
                    'processando' => 'info',
                    default => 'secondary'
                };
                @endphp
                <span class="badge bg-{{ $statusColor }} fs-6">{{ ucfirst($nfse->status) }}</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="fw-bold">Tomador (Cliente):</label>
                        <p>{{ $nfse->cliente->nome ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">CPF/CNPJ:</label>
                        <p>{{ $nfse->cliente->cpf_cnpj ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Número NFS-e:</label>
                        <p>{{ $nfse->numero_nfse ?? 'Não gerado' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Código de Verificação:</label>
                        <p>{{ $nfse->chave_acesso ?? 'Não gerado' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Data de Emissão:</label>
                        <p>{{ $nfse->data_emissao ? $nfse->data_emissao->format('d/m/Y H:i:s') : $nfse->created_at->format('d/m/Y H:i:s') }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">RPS:</label>
                        <p>{{ $nfse->numero_rps ?? $nfse->id }}</p>
                    </div>
                </div>

                <hr class="my-4">

                <h6 class="mb-3">Serviço</h6>
                <div class="row">
                    <div class="col-12">
                        <label class="fw-bold">Discriminação:</label>
                        <p class="text-justify" style="white-space: pre-wrap;">{{ $nfse->discriminacao }}</p>
                    </div>
                    <div class="col-md-6 mt-3">
                        <label class="fw-bold">Código Serviço (LC 116):</label>
                        <p>{{ $nfse->codigo_servico }}</p>
                    </div>
                </div>
            </div>
        </div>

        @if($nfse->motivo_rejeicao)
        <div class="alert alert-danger">
            <h6 class="alert-heading fw-bold">Motivo da Rejeição:</h6>
            <p class="mb-0">{{ $nfse->motivo_rejeicao }}</p>
        </div>
        @endif
    </div>

    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Valores</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        Valor Serviço
                        <span>R$ {{ number_format($nfse->valor_servico, 2, ',', '.') }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        Alíquota ISS
                        <span>{{ number_format($nfse->aliquota_iss, 2, ',', '.') }}%</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        Valor ISS
                        <span>R$ {{ number_format($nfse->valor_iss, 2, ',', '.') }}</span>
                    </li>
                    @if($nfse->iss_retido)
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 text-danger">
                        ISS Retido
                        <span>Sim</span>
                    </li>
                    @endif
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 fw-bold fs-5 mt-2">
                        Valor Total
                        <span>R$ {{ number_format($nfse->valor_total, 2, ',', '.') }}</span>
                    </li>
                </ul>
            </div>
        </div>

        @if($nfse->link_nfse)
        <div class="card">
            <div class="card-body">
                <a href="{{ $nfse->link_nfse }}" target="_blank" class="btn btn-label-primary w-100">
                    <i class="bx bx-download me-1"></i> Baixar/Imprimir NFS-e
                </a>
            </div>
        </div>
        @endif
    </div>
</div>

@endsection
