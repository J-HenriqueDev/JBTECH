@extends('layouts/contentNavbarLayout')

@section('title', 'Naturezas de Operação')

@section('content')
<h4 class="fw-bold py-3 mb-4">
    <span class="text-muted fw-light">Cadastros /</span> Naturezas de Operação
</h4>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Lista de Naturezas</h5>
        <a href="{{ route('naturezas.create') }}" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i> Nova Natureza
        </a>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Descrição</th>
                    <th>Tipo</th>
                    <th>CFOP Est.</th>
                    <th>CFOP Inter.</th>
                    <th>CFOP Ext.</th>
                    <th>Custo</th>
                    <th>Estoque</th>
                    <th>Fin.</th>
                    <th>Padrão</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse($naturezas as $natureza)
                <tr>
                    <td><strong>{{ $natureza->descricao }}</strong></td>
                    <td>
                        <span class="badge bg-label-{{ $natureza->tipo == 'entrada' ? 'success' : 'info' }}">
                            {{ ucfirst($natureza->tipo) }}
                        </span>
                    </td>
                    <td>{{ $natureza->cfop_estadual }}</td>
                    <td>{{ $natureza->cfop_interestadual }}</td>
                    <td>{{ $natureza->cfop_exterior ?? '-' }}</td>
                    <td><i class="bx bx-{{ $natureza->calcula_custo ? 'check text-success' : 'x text-secondary' }}"></i></td>
                    <td><i class="bx bx-{{ $natureza->movimenta_estoque ? 'check text-success' : 'x text-secondary' }}"></i></td>
                    <td><i class="bx bx-{{ $natureza->gera_financeiro ? 'check text-success' : 'x text-secondary' }}"></i></td>
                    <td>
                        @if($natureza->padrao)
                        <span class="badge bg-primary">Sim</span>
                        @else
                        <span class="badge bg-label-secondary">Não</span>
                        @endif
                    </td>
                    <td>
                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="{{ route('naturezas.edit', $natureza->id) }}">
                                    <i class="bx bx-edit-alt me-1"></i> Editar
                                </a>
                                <form action="{{ route('naturezas.destroy', $natureza->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bx bx-trash me-1"></i> Excluir
                                    </button>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">Nenhuma natureza cadastrada.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection