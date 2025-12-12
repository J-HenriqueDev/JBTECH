@extends('layouts.layoutMaster')

@section('title', 'Emitir NF-e')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0 text-primary">
        <i class="bx bx-receipt"></i> Emitir Nota Fiscal Eletrônica
    </h1>
</div>

@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Selecionar Venda</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('nfe.store') }}" method="POST">
                    @csrf
                    
                    @if($venda)
                        <div class="alert alert-info">
                            <h6>Venda Selecionada: #{{ $venda->id }}</h6>
                            <p class="mb-0">
                                <strong>Cliente:</strong> {{ $venda->cliente->nome }}<br>
                                <strong>Valor Total:</strong> R$ {{ number_format($venda->valor_total, 2, ',', '.') }}<br>
                                <strong>Produtos:</strong> {{ $venda->produtos->count() }} item(ns)
                            </p>
                        </div>
                        <input type="hidden" name="venda_id" value="{{ $venda->id }}">
                    @else
                        <div class="mb-3">
                            <label for="venda_id" class="form-label">Selecione uma Venda</label>
                            <select name="venda_id" id="venda_id" class="form-select" required>
                                <option value="">Selecione uma venda...</option>
                                @foreach($vendas as $v)
                                    <option value="{{ $v->id }}">
                                        Venda #{{ $v->id }} - {{ $v->cliente->nome }} - 
                                        R$ {{ number_format($v->valor_total, 2, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">
                                Apenas vendas sem NF-e autorizada são exibidas.
                            </small>
                        </div>
                    @endif

                    <div class="card-footer d-flex justify-content-end">
                        <a href="{{ route('nfe.index') }}" class="btn btn-secondary me-2">
                            <i class="bx bx-x"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-check"></i> Emitir NF-e
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if($venda)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Detalhes da Venda</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Cliente:</strong> {{ $venda->cliente->nome }}<br>
                        <strong>CPF/CNPJ:</strong> {{ $venda->cliente->cpf_cnpj }}<br>
                        <strong>Email:</strong> {{ $venda->cliente->email ?? 'N/A' }}<br>
                        <strong>Telefone:</strong> {{ $venda->cliente->telefone ?? 'N/A' }}
                    </div>
                    <div class="col-md-6">
                        @if($venda->cliente->endereco)
                            <strong>Endereço:</strong><br>
                            {{ $venda->cliente->endereco->endereco }}, {{ $venda->cliente->endereco->numero }}<br>
                            {{ $venda->cliente->endereco->bairro }}<br>
                            {{ $venda->cliente->endereco->cidade }}/{{ $venda->cliente->endereco->estado }}<br>
                            CEP: {{ $venda->cliente->endereco->cep }}
                        @else
                            <div class="alert alert-warning">
                                <i class="bx bx-error"></i> Cliente não possui endereço cadastrado!
                            </div>
                        @endif
                    </div>
                </div>

                <h6>Produtos:</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>NCM</th>
                                <th>Quantidade</th>
                                <th>Valor Unitário</th>
                                <th>Valor Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($venda->produtos as $produto)
                            <tr>
                                <td>{{ $produto->id }}</td>
                                <td>{{ $produto->nome }}</td>
                                <td>{{ $produto->ncm ?? 'N/A' }}</td>
                                <td>{{ $produto->pivot->quantidade }}</td>
                                <td>R$ {{ number_format($produto->pivot->valor_unitario, 2, ',', '.') }}</td>
                                <td>R$ {{ number_format($produto->pivot->valor_total, 2, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-end"><strong>Total:</strong></td>
                                <td><strong>R$ {{ number_format($venda->valor_total, 2, ',', '.') }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@endsection



