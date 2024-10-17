@extends('layouts.layoutMaster')

@section('title', 'Edição de Produto')

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
    <h1><i class="fas fa-shopping-cart me-1"></i> Edição de Produto</h1>
    <a href="{{ route('produtos.index') }}" class="btn btn-primary">
        <i class="fas fa-list me-1"></i> Listagem
    </a>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <form action="{{ route('produtos.update', $produto->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <!-- Seu formulário existente para editar produtos -->

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nome_produto"><i class="fas fa-shopping-bag"></i> Nome do Produto</label>
                                <input type="text" class="form-control" id="nome_produto" name="nome_produto" placeholder="Nome do Produto" required autocomplete="off" value="{{ $produto->nome_produto }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="codigo_barras"><i class="fas fa-barcode"></i> Código de Barras</label>
                                <input type="number" class="form-control" id="codigo_barras" name="codigo_barras" placeholder="Código de Barras" autocomplete="off" value="{{ $produto->codigo_barras }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="preco_venda"><i class="fas fa-dollar-sign"></i> Preço de Venda</label>
                                <input type="number" step="0.01" class="form-control" id="preco_venda" name="preco_venda" placeholder="Preço de Venda" required autocomplete="off" value="{{ $produto->preco_venda }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="categoria"><i class="fas fa-layer-group"></i> Categoria</label>
                                <div class="input-group">
                                    <select class="form-control" id="categoria" name="categoria_id">
                                        @isset($categorias)
                                            @foreach($categorias as $categoria)
                                                <option value="{{ $categoria->id }}" {{ $produto->categoria_id == $categoria->id ? 'selected' : '' }}>
                                                    {{ $categoria->nome }}
                                                </option>
                                            @endforeach
                                        @endisset
                                    </select>
                                    <button type="button" class="btn btn-outline-secondary btn-open-modal" data-bs-toggle="modal" data-bs-target="#categoriaModal">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="local_impressao"><i class="fas fa-print"></i> Local de Impressão</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="local_impressao" name="local_impressao" placeholder="Local de Impressão" required autocomplete="off" value="{{ $produto->local_impressao }}">
                                    <button type="button" class="btn btn-outline-secondary btn-open-modal" data-bs-toggle="modal" data-bs-target="#localImpressaoModal">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer d-flex justify-content-end">
                    <button type="submit" class="btn btn-md btn-primary fw-bold align-right mr-2">Atualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Categoria -->
<!-- Restante do código permanece o mesmo -->

<!-- Modal de Local de Impressão -->
<!-- Restante do código permanece o mesmo -->

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function () {
        $('.btn-open-modal').click(function () {
            var modalId = $(this).data('modal-id');
            $('#' + modalId).modal('show');
        });
    });
</script>

<style>
    /* Adicione estilos personalizados aqui, se necessário */
</style>

@endsection
