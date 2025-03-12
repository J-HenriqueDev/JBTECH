@extends('layouts.layoutMaster')

@section('title', 'Logs do Sistema')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0 text-primary" style="font-size: 2.5rem; font-weight: bold; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);">
        <i class="fas fa-clipboard-list"></i> Logs do Sistema
    </h1>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Logs Registrados</h5>
                    <!-- Filtros -->
                    <form method="GET" action="{{ route('logs.index') }}" class="d-flex align-items-center">
                        <input type="text" name="usuario" class="form-control me-2" placeholder="Filtrar por usuário" value="{{ request('usuario') }}">
                        <input type="text" name="categoria" class="form-control me-2" placeholder="Filtrar por categoria" value="{{ request('categoria') }}">
                        <input type="date" name="data" class="form-control me-2" value="{{ request('data') }}">
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                    </form>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Usuário</th>
                                <th>Categoria</th>
                                <th>Ação</th>
                                <th>Detalhes</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($logs as $log)
                                <tr>
                                    <td>{{ $log->id }}</td>
                                    <td>{{ $log->user->name ?? 'N/A' }}</td>
                                    <td>{{ $log->categoria }}</td>
                                    <td>{{ $log->acao }}</td>
                                    <td>{{ $log->detalhes }}</td>
                                    <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Links de Paginação -->
{{ $logs->appends(request()->query())->links() }}

@endsection
