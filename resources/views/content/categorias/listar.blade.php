@extends('layouts.layoutMaster')

@section('vendor-style')
@vite([/* CSS do Bootstrap ou qualquer outro que você estiver usando */])
<style>
    .modal-header {
        background-color: #007bff;
        color: white;
    }
    .accordion-button {
        font-weight: bold;
        color: #333;
        background-color: #f8f9fa;
    }
    .accordion-button:not(.collapsed) {
        color: #007bff;
        background-color: #e7f1ff;
    }
    .list-group-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .list-group-item:hover {
        background-color: #f1f1f1;
    }
    .btn-edit {
        margin-left: 10px; /* Espaço entre o título e o botão de editar */
    }
</style>
@endsection

@section('vendor-script')
@vite([/* JS do Bootstrap ou qualquer outro que você estiver usando */])
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-4 text-primary" style="font-size: 2rem; font-weight: bold;">
        <i class="fas fa-tags"></i> Lista de Categorias
    </h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNovaCategoria">
        <i class="fas fa-plus-circle"></i> Adicionar Categoria
    </button>
</div>

<!-- Modal para adicionar nova categoria -->
<div class="modal fade" id="modalNovaCategoria" tabindex="-1" aria-labelledby="modalNovaCategoriaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNovaCategoriaLabel">Adicionar Nova Categoria</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('categorias.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome da Categoria</label>
                        <input type="text" class="form-control" name="nome" id="nome" placeholder="Digite o nome da categoria" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Adicionar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar categoria -->
<div class="modal fade" id="modalEditarCategoria" tabindex="-1" aria-labelledby="modalEditarCategoriaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarCategoriaLabel">Editar Categoria</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarCategoria" action="" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="editar_nome" class="form-label">Nome da Categoria</label>
                        <input type="text" class="form-control" name="nome" id="editar_nome" placeholder="Digite o novo nome da categoria" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="accordion" id="accordionCategorias">
    @foreach ($categorias as $categoria)
        <div class="accordion-item">
            <h2 class="accordion-header" id="heading{{ $categoria->id }}">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $categoria->id }}" aria-expanded="true" aria-controls="collapse{{ $categoria->id }}">
                        <i class="fas fa-folder"></i> {{ $categoria->nome }}
                    </button>
                    <button type="button" class="btn btn-outline-success btn-edit" data-bs-toggle="modal" data-bs-target="#modalEditarCategoria" onclick="setEditCategory({{ $categoria->id }}, '{{ $categoria->nome }}')">
                        <i class="fas fa-edit"></i> Editar
                    </button>
                </div>
            </h2>
            <div id="collapse{{ $categoria->id }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $categoria->id }}" data-bs-parent="#accordionCategorias">
                <div class="accordion-body">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" placeholder="Buscar produtos..." onkeyup="filterProducts(this, '{{ $categoria->id }}')">
                        <button class="btn btn-outline-secondary" type="button" id="button-addon2">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <ul id="produtos{{ $categoria->id }}" class="list-group">
                        @foreach ($categoria->produtos as $produto)
                            <li class="list-group-item">
                                <i class="fas fa-box"></i> {{ $produto->nome }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endforeach
</div>

<script>
    function filterProducts(input, categoriaId) {
        const filter = input.value.toLowerCase();
        const ul = document.getElementById('produtos' + categoriaId);
        const li = ul.getElementsByTagName('li');

        for (let i = 0; i < li.length; i++) {
            const txtValue = li[i].textContent || li[i].innerText;
            li[i].style.display = txtValue.toLowerCase().includes(filter) ? "" : "none";
        }
    }

    function setEditCategory(id, nome) {
        // Preenche o campo de nome do modal de edição
        document.getElementById('editar_nome').value = nome;
        // Define a ação do formulário para a atualização da categoria
        document.getElementById('formEditarCategoria').action = `/categorias/${id}`;
    }
</script>

@endsection
