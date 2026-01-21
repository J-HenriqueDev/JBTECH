@extends('layouts.layoutMaster')

@section('title', 'Notas Fiscais de Serviço (NFS-e)')

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible" role="alert">
    <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">
        <i class="bx bx-check-circle me-1"></i> Sucesso!
    </h6>
    <p class="mb-0">{!! session('success') !!}</p>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible" role="alert">
    <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">
        <i class="bx bx-error-circle me-1"></i> Erro!
    </h6>
    <p class="mb-0">{!! session('error') !!}</p>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0 text-primary">
        <i class="bx bx-receipt"></i> Notas Fiscais de Serviço (NFS-e)
    </h1>
    <a href="{{ route('nfse.create') }}" class="btn btn-primary">
        <i class="bx bx-plus-circle me-1"></i> Nova NFS-e
    </a>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">NFS-e Emitidas ({{ $notas->total() }})</h5>
            </div>
            <div class="card-body">
                @if($notas->isEmpty())
                <div class="alert alert-info text-center">
                    <i class="bx bx-info-circle fs-1 d-block mb-2"></i>
                    <h5>Nenhuma NFS-e encontrada</h5>
                    <p class="mb-0">Clique em "Nova NFS-e" para começar.</p>
                </div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Número RPS/NFS-e</th>
                                <th>Cliente</th>
                                <th>Valor Serviço</th>
                                <th>ISS</th>
                                <th>Data Emissão</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($notas as $nfse)
                            <tr>
                                <td>
                                    @if($nfse->numero_nfse)
                                        <span class="badge bg-label-primary">NFS-e: {{ $nfse->numero_nfse }}</span>
                                    @else
                                        <span class="badge bg-label-secondary">RPS: {{ $nfse->id }}</span>
                                    @endif
                                </td>
                                <td>{{ $nfse->cliente->nome ?? 'N/A' }}</td>
                                <td>R$ {{ number_format($nfse->valor_servico, 2, ',', '.') }}</td>
                                <td>R$ {{ number_format($nfse->valor_iss, 2, ',', '.') }}</td>
                                <td>{{ $nfse->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    @php
                                    $statusColor = match($nfse->status) {
                                        'autorizada' => 'success',
                                        'rejeitada' => 'danger',
                                        'cancelada' => 'warning',
                                        'processando' => 'info',
                                        default => 'secondary'
                                    };
                                    @endphp
                                    <span class="badge bg-{{ $statusColor }}">
                                        {{ ucfirst($nfse->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="{{ route('nfse.show', $nfse->id) }}">
                                                <i class="bx bx-show-alt me-1"></i> Detalhes
                                            </a>
                                            @if($nfse->status == 'pendente' || $nfse->status == 'rejeitada')
                                            <form action="{{ route('nfse.emitir', $nfse->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="dropdown-item">
                                                    <i class="bx bx-send me-1"></i> Emitir Agora
                                                </button>
                                            </form>
                                            @endif
                                            @if($nfse->link_nfse)
                                            <a class="dropdown-item" href="{{ $nfse->link_nfse }}" target="_blank">
                                                <i class="bx bx-download me-1"></i> Imprimir NFS-e
                                            </a>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $notas->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
