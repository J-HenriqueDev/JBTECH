@extends('layouts.layoutMaster')

@section('title', 'Produtos')

@section('content')

<div class="alert alert-danger alert-dismissible fade show" role="alert">
  <strong>Atenção:</strong> Ao criar uma nova categoria, a página será reiniciada. Certifique-se de criar a categoria antes de prosseguir.
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>

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
  <h1><i class="fas fa-shopping-cart me-1"></i> Cadastro de Produtos</h1>
  <a href="{{ route('produtos.index') }}" class="btn btn-primary">
    <i class="fas fa-list me-1"></i> Listagem
  </a>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <form action="{{ route('produtos.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nome_produto"><i class="fas fa-shopping-bag"></i> Nome do Produto</label>
                                <input type="text" class="form-control" id="nome_produto" name="nome_produto" placeholder="Nome do Produto" required autocomplete="off">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="codigo_barras"><i class="fas fa-barcode"></i> Código de Barras</label>
                                <input type="number" class="form-control" id="codigo_barras" name="codigo_barras" placeholder="Código de Barras" autocomplete="off">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="preco_venda"><i class="fas fa-dollar-sign"></i> Preço de Venda</label>
                                <input type="number" step="0.01" class="form-control" id="preco_venda" name="preco_venda" placeholder="Preço de Venda" required autocomplete="off">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="categoria"><i class="fas fa-layer-group"></i> Categoria</label>
                                <div class="input-group">
                                  <select class="form-control" id="categoria" name="categoria">
                                    @isset($categorias)
                                        @foreach($categorias as $categoria)
                                            <option value="{{ $categoria->id }}">{{ $categoria->nome }}</option>
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
                                    <input type="text" class="form-control" id="local_impressao" name="local_impressao" placeholder="Local de Impressão" required autocomplete="off">
                                    <button type="button" class="btn btn-outline-secondary btn-open-modal" data-bs-toggle="modal" data-bs-target="#localImpressaoModal">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer d-flex justify-content-end">
                    <button type="submit" class="btn btn-md btn-primary fw-bold align-right mr-2">Adicionar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Categoria -->
<!-- Modal de Categoria -->
<div class="modal fade" id="categoriaModal" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('categorias.store') }}" method="POST" id="categoriaForm">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Adicionar Categoria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col mb-3">
                            <label for="categoriaNome" class="form-label">Nome</label>
                            <input type="text" id="categoriaNome" name="nome" class="form-control" placeholder="Informe a categoria" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal de Local de Impressão -->
<div class="modal fade" id="localImpressaoModal" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog">
        {{--  <form action="{{ route('local-impressao.store') }}" method="POST" id="localImpressaoForm">  --}}
          <form action=" "method="POST" id="localImpressaoForm"></form>
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Adicionar Local de Impressão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col mb-3">
                            <label for="localImpressaoNome" class="form-label">Nome</label>
                            <input type="text" id="localImpressaoNome" name="localImpressaoNome" class="form-control" placeholder="Informe o nome do local de impressão" required>
                        </div>
                        <div class="col mb-3">
                            <label for="localImpressaoIp" class="form-label">IP da Impressora</label>
                            <input type="text" id="localImpressaoIp" name="localImpressaoIp" class="form-control" placeholder="Informe o IP da impressora" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </div>
        </form>
    </div>
</div>

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
