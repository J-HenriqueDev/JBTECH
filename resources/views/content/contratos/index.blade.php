@extends('layouts/contentNavbarLayout')

@section('title', 'Contratos')

@section('content')
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Contratos /</span> Listagem
    </h4>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Contratos</h5>
            <div>
                <span class="badge bg-label-info me-2">
                    <i class="bx bx-time-five me-1"></i>
                    Geração Automática: {{ $diasAntecedencia }} dias antes
                </span>
                <a href="{{ route('contratos.create') }}" class="btn btn-primary">
                    <i class="bx bx-plus me-1"></i> Novo Contrato
                </a>
            </div>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Serviço</th>
                        <th>Valor</th>
                        <th>Frequência</th>
                        <th>Vencimento</th>
                        <th>Próx. Faturamento</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($contratos as $contrato)
                        <tr>
                            <td>#{{ $contrato->id }}</td>
                            <td>
                                <strong>{{ $contrato->cliente->nome }}</strong><br>
                                <small class="text-muted">{{ $contrato->cliente->cpf_cnpj }}</small>
                            </td>
                            <td>
                                @if ($contrato->servico)
                                    <span class="badge bg-label-secondary">{{ $contrato->servico->nome }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $dias = $contrato->dias_personalizados
                                        ? array_map('trim', explode(',', $contrato->dias_personalizados))
                                        : [];
                                    $qtdDias = count(array_filter($dias));
                                @endphp
                                @if ($qtdDias > 1)
                                    R$ {{ number_format($contrato->valor, 2, ',', '.') }} <br>
                                    <small class="text-muted">({{ $qtdDias }}x R$
                                        {{ number_format($contrato->valor / $qtdDias, 2, ',', '.') }})</small>
                                @else
                                    R$ {{ number_format($contrato->valor, 2, ',', '.') }}
                                @endif
                            </td>
                            <td>{{ ucfirst($contrato->frequencia) }}</td>
                            <td>
                                @if ($qtdDias > 0)
                                    Dias {{ implode(', ', $dias) }}
                                @else
                                    Dia {{ $contrato->dia_vencimento }}
                                @endif
                            </td>
                            <td>{{ $contrato->proximo_faturamento ? $contrato->proximo_faturamento->format('d/m/Y') : '-' }}
                            </td>
                            <td>
                                @if ($contrato->ativo)
                                    <span class="badge bg-label-success">Ativo</span>
                                @else
                                    <span class="badge bg-label-danger">Inativo</span>
                                @endif
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                        data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="{{ route('contratos.edit', $contrato->id) }}"><i
                                                class="bx bx-edit-alt me-1"></i> Editar</a>
                                        <form action="{{ route('contratos.destroy', $contrato->id) }}" method="POST"
                                            onsubmit="return confirm('Tem certeza que deseja excluir este contrato?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item"><i class="bx bx-trash me-1"></i>
                                                Excluir</button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">Nenhum contrato encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $contratos->links() }}
        </div>
    </div>
@endsection
