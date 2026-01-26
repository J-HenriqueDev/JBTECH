@extends('layouts.layoutMaster')

@section('title', 'Logs do Sistema')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Logs do Sistema</h4>
        <div>
            <a href="{{ route('logs.index') }}" class="btn btn-primary me-2">
                <i class="fas fa-sync"></i> Atualizar
            </a>
            <form action="{{ route('logs.clear') }}" method="POST" class="d-inline"
                onsubmit="return confirm('Tem certeza que deseja limpar todos os logs?');">
                @csrf
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Limpar Logs
                </button>
            </form>
        </div>
    </div>

    <!-- Console Logs Section (Retrátil) -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center" data-bs-toggle="collapse"
            data-bs-target="#consoleLogsCollapse" aria-expanded="false" style="cursor: pointer;">
            <h5 class="mb-0"><i class="fas fa-terminal me-2"></i>Logs do Console (Comandos)</h5>
            <i class="fas fa-chevron-down"></i>
        </div>
        <div id="consoleLogsCollapse" class="collapse">
            <div class="card-body p-0">
                <div
                    style="background-color: #000000; border: 1px solid #333333; border-radius: 4px; padding: 10px; max-height: 400px; overflow-y: auto; box-shadow: none;">
                    <pre class="m-0"
                        style="font-family: 'Consolas', 'Lucida Console', 'Courier New', monospace; font-size: 13px; line-height: 1.2; color: #cccccc; background-color: transparent; border: none; white-space: pre-wrap; text-shadow: none;">
@if (isset($consoleLogs) && count($consoleLogs) > 0)
{{ implode("\n", $consoleLogs) }}
@else
Nenhum log de console encontrado.
@endif
</pre>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Filtros</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('logs.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="usuario" class="form-label">Usuário</label>
                    <select name="usuario" id="usuario" class="form-select">
                        <option value="">Todos</option>
                        @foreach ($usuarios as $usuario)
                            <option value="{{ $usuario->id }}" {{ request('usuario') == $usuario->id ? 'selected' : '' }}>
                                {{ $usuario->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="categoria" class="form-label">Categoria</label>
                    <select name="categoria" id="categoria" class="form-select">
                        <option value="">Todas</option>
                        @foreach ($categorias as $cat)
                            <option value="{{ $cat }}" {{ request('categoria') == $cat ? 'selected' : '' }}>
                                {{ $cat }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="acao" class="form-label">Ação</label>
                    <select name="acao" id="acao" class="form-select">
                        <option value="">Todas</option>
                        @foreach ($acoes as $ac)
                            <option value="{{ $ac }}" {{ request('acao') == $ac ? 'selected' : '' }}>
                                {{ $ac }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filtrar</button>
                    <a href="{{ route('logs.index') }}" class="btn btn-secondary">Limpar</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela de Logs do Sistema -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Histórico de Ações</h5>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Usuário</th>
                        <th>Categoria</th>
                        <th>Ação</th>
                        <th>Detalhes</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                            <td>
                                @if ($log->user)
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-xs me-2">
                                            <span class="avatar-initial rounded-circle bg-label-primary">
                                                {{ substr($log->user->name, 0, 2) }}
                                            </span>
                                        </div>
                                        <span>{{ $log->user->name }}</span>
                                    </div>
                                @else
                                    <span class="text-muted">Sistema/Desconhecido</span>
                                @endif
                            </td>
                            <td><span class="badge bg-label-info">{{ $log->categoria }}</span></td>
                            <td>{{ $log->acao }}</td>
                            <td style="white-space: normal; max-width: 400px;">
                                {{ \Illuminate\Support\Str::limit($log->detalhes, 100) }}
                                @if (strlen($log->detalhes) > 100)
                                    <button type="button" class="btn btn-sm btn-link p-0" data-bs-toggle="popover"
                                        data-bs-content="{{ $log->detalhes }}" title="Detalhes Completos">
                                        Ver mais
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Nenhum registro encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $logs->links() }}
        </div>
    </div>
@endsection
