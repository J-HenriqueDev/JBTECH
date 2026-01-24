@extends('layouts/contentNavbarLayout')

@section('title', 'Modelos de Serviços')

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">Cadastros /</span> Modelos de Serviços
</h4>

@if(session('success'))
<div class="alert alert-success alert-dismissible" role="alert">
  {{ session('success') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible" role="alert">
  {{ session('error') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Modelos de Serviços Fiscais</h5>
    <a href="{{ route('servicos.create') }}" class="btn btn-primary">
      <span class="tf-icons bx bx-plus"></span> Novo Modelo
    </a>
  </div>
  <div class="table-responsive text-nowrap">
    <table class="table table-hover">
      <thead>
        <tr>
          <th>Nome</th>
          <th>Cód. Serviço</th>
          <th>Alíquota</th>
          <th>ISS Retido</th>
          <th>Status</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">
        @forelse($servicos as $servico)
        <tr>
          <td><strong>{{ $servico->nome }}</strong></td>
          <td>{{ $servico->codigo_servico ?? '-' }}</td>
          <td>{{ $servico->aliquota_iss }}%</td>
          <td>{{ $servico->iss_retido ? 'Sim' : 'Não' }}</td>
          <td>
            @if($servico->ativo)
              <span class="badge bg-label-success">Ativo</span>
            @else
              <span class="badge bg-label-danger">Inativo</span>
            @endif
          </td>
          <td>
            <div class="dropdown">
              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="{{ route('servicos.edit', $servico->id) }}"><i class="bx bx-edit-alt me-1"></i> Editar</a>
                <form action="{{ route('servicos.destroy', $servico->id) }}" method="POST" onsubmit="return confirm('Tem certeza?');">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="dropdown-item"><i class="bx bx-trash me-1"></i> Excluir</button>
                </form>
              </div>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="6" class="text-center">Nenhum modelo cadastrado.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="card-footer">
    {{ $servicos->links() }}
  </div>
</div>
@endsection
