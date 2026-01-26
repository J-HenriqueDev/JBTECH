@extends('layouts.layoutMaster')

@section('title', 'Visualizador de Logs')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Logs do Sistema (Últimas 1000 linhas)</h5>
            <div>
                <a href="{{ route('logs.index') }}" class="btn btn-sm btn-primary me-2">
                    <i class="fas fa-sync"></i> Atualizar
                </a>
                <form action="{{ route('logs.clear') }}" method="POST" class="d-inline"
                    onsubmit="return confirm('Tem certeza que deseja limpar os logs?');">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-danger">
                        <i class="fas fa-trash"></i> Limpar Logs
                    </button>
                </form>
            </div>
        </div>
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
@endsection
