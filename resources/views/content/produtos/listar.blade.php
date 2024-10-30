@extends('layouts.layoutMaster')

@section('title', 'Lista de Produtos')

@section('content')

@if(session('success'))
<div class="alert alert-primary alert-dismissible" role="alert">
    <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">
        <i class="fas fa-check-circle me-1"></i> Sucesso!
    </h6>
    <p class="mb-0">{!! session('success') !!}</p>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0 text-primary" style="font-size: 2.5rem; font-weight: bold; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);">
        <i class="fas fa-box"></i> Lista de Produtos
    </h1>
    <a href="{{ route('produtos.create') }}" class="btn btn-primary">
        <i class="fas fa-plus-circle me-1"></i> Novo Produto
    </a>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">Produtos Cadastrados</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Preço de Custo</th>
                                <th>Preço de Venda</th>
                                <th>Estoque</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($produtos as $produto)
                                <tr>
                                    <td>{{ $produto->nome }}</td>
                                    <td>{{ number_format($produto->preco_custo, 2, ',', '.') }}</td>
                                    <td>{{ number_format($produto->preco_venda, 2, ',', '.') }}</td>
                                    <td>{{ $produto->estoque }}</td>
                                    <td>
                                        <a href="{{ route('produtos.edit', $produto->id) }}" class="btn btn-warning">Editar</a>
                                        <form action="{{ route('produtos.destroy', $produto->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">Excluir</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
