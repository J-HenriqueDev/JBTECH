@extends('layouts.layoutMaster')

@section('title', 'Produtos')

@section('content')

@if(session('noti'))
<div class="alert alert-primary alert-dismissible" role="alert">
  <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">
    <i class="fas fa-check-circle me-1"></i> Sucesso!
  </h6>
  <p class="mb-0">{{ session('noti') }}</p>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="d-flex justify-content-between align-items-center">
    <h1>Cadastro de Produtos</h1>
    <a href="{{ route('produtos.create') }}" class="btn btn-primary">
        <i class="fas fa-plus-circle me-1"></i> Novo Produto
    </a>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome do Produto</th>
                                <th>Preço de Venda</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($produtos as $produto)
                                <tr>
                                    <td>{{ $produto->id }}</td>
                                    <td>{{ $produto->nome_produto }}</td>
                                    <td>{{ $produto->preco_venda }}</td>
                                    <td>
                                        <a href="{{ route('produtos.edit', $produto->id) }}" class="btn btn-primary">
                                            <i class="fas fa-pencil-alt"></i> Editar
                                        </a>
                                        <a href="#" class="btn btn-danger btn-delete" data-toggle="modal" data-target="#confirmDelete" data-id="{{ $produto->id }}">
                                            <i class="fas fa-trash-alt"></i> Excluir
                                        </a>
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

<div class="modal fade" id="confirmDelete" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Excluir Produto</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Tem certeza de que deseja excluir este produto?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Excluir</a>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        let produtoIdToDelete;

        $('.btn-delete').on('click', function() {
            produtoIdToDelete = $(this).data('id');
        });

        $('#confirmDeleteBtn').on('click', function() {
            if (produtoIdToDelete) {
                let deleteUrl = "{{ route('produtos.destroy', '') }}" + "/" + produtoIdToDelete;
                window.location.href = deleteUrl;
            }
        });
    });
</script>
@endsection
