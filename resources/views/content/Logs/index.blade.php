@extends('layouts.layoutMaster')

@section('title', 'Visualizador de Logs')

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
                    <i class="fas fa-trash"></i> Limpar Todos Logs
                </button>
            </form>
        </div>
    </div>

    <!-- Console Logs Section -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center" data-bs-toggle="collapse"
            data-bs-target="#consoleLogsCollapse" aria-expanded="true" style="cursor: pointer;">
            <h5 class="mb-0"><i class="fas fa-terminal me-2"></i>Logs do Console (Comandos)</h5>
            <i class="fas fa-chevron-down"></i>
        </div>
        <div id="consoleLogsCollapse" class="collapse show">
            <div class="card-body">
                <div class="bg-dark text-white p-3 rounded"
                    style="max-height: 400px; overflow-y: auto; font-family: monospace; font-size: 0.85rem;">
                    @if (isset($consoleLogs) && count($consoleLogs) > 0)
                        @foreach ($consoleLogs as $log)
                            <div class="mb-1 border-bottom border-secondary pb-1" style="white-space: pre-wrap;">
                                <span class="text-white">{{ $log }}</span>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted">Nenhum log de console encontrado.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- System Logs Section -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center" data-bs-toggle="collapse"
            data-bs-target="#systemLogsCollapse" aria-expanded="true" style="cursor: pointer;">
            <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Logs do Sistema (Laravel)</h5>
            <i class="fas fa-chevron-down"></i>
        </div>
        <div id="systemLogsCollapse" class="collapse show">
            <div class="card-body">
                <div class="bg-dark text-white p-3 rounded"
                    style="max-height: 600px; overflow-y: auto; font-family: monospace; font-size: 0.85rem;">
                    @if (count($logs) > 0)
                        @foreach ($logs as $log)
                            <div class="mb-1 border-bottom border-secondary pb-1" style="white-space: pre-wrap;">
                                @php
                                    $color = 'text-white';
                                    if (str_contains($log, '.ERROR') || str_contains($log, '.CRITICAL')) {
                                        $color = 'text-danger fw-bold';
                                    } elseif (str_contains($log, '.WARNING')) {
                                        $color = 'text-warning';
                                    } elseif (str_contains($log, '.INFO')) {
                                        $color = 'text-info';
                                    }
                                @endphp
                                <span class="{{ $color }}">{{ $log }}</span>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted">Nenhum log encontrado.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
