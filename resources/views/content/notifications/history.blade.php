@extends('layouts.layoutMaster')

@section('title', 'Histórico de Notificações')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/select2/select2.scss'
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

<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="mb-0 text-primary">
    <i class="bx bx-history"></i> Histórico de Notificações
  </h1>
  <a href="{{ route('notifications.admin') }}" class="btn btn-outline-primary">
    <i class="bx bx-bell"></i> Enviar Notificações
  </a>
  <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
    <i class="bx bx-arrow-back"></i> Voltar
  </a>
</div>

<div class="card mb-3">
  <div class="card-header">
    <h5 class="card-title mb-0">Filtros</h5>
  </div>
  <div class="card-body">
    <form method="GET" class="row g-3">
      <div class="col-md-4">
        <label class="form-label fw-bold">Usuário (quem enviou)</label>
        <select name="usuario" class="form-select select2" data-placeholder="Selecione um usuário">
          <option value=""></option>
          @foreach($users as $u)
          <option value="{{ $u->id }}" {{ request('usuario') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label fw-bold">Ação</label>
        <select name="acao" class="form-select select2" data-placeholder="Selecione uma ação">
          <option value=""></option>
          <option value="Enviar" {{ request('acao') == 'Enviar' ? 'selected' : '' }}>Enviar</option>
          <option value="Despachar" {{ request('acao') == 'Despachar' ? 'selected' : '' }}>Despachar</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label fw-bold">Período</label>
        <select name="periodo" class="form-select select2" data-placeholder="Selecione um período">
          <option value=""></option>
          <option value="hoje" {{ request('periodo') == 'hoje' ? 'selected' : '' }}>Hoje</option>
          <option value="7d" {{ request('periodo') == '7d' ? 'selected' : '' }}>Últimos 7 dias</option>
          <option value="30d" {{ request('periodo') == '30d' ? 'selected' : '' }}>Últimos 30 dias</option>
        </select>
      </div>
      <div class="col-12">
        <button type="submit" class="btn btn-primary">
          <i class="bx bx-filter"></i> Filtrar
        </button>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h5 class="card-title mb-0">Registros</h5>
  </div>
  <div class="card-body">
    @if($registros->count() === 0)
    <p class="text-muted">Nenhum registro encontrado para os filtros informados.</p>
    @else
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Data/Hora</th>
            <th>Usuário</th>
            <th>Ação</th>
            <th>Detalhes</th>
          </tr>
        </thead>
        <tbody>
          @foreach($registros as $r)
          <tr>
            <td>{{ $r->created_at->format('d/m/Y H:i') }}</td>
            <td>{{ $r->user?->name ?? 'N/A' }}</td>
            <td><span class="badge bg-label-primary">{{ $r->acao }}</span></td>
            <td class="text-break">{{ $r->detalhes }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="mt-3">
      {{ $registros->links() }}
    </div>
    @endif
  </div>
</div>

@endsection