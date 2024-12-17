@extends('layouts.layoutMaster')

@section('title', 'Gerenciar Categorias')

@section('vendor-style')
@vite(['resources/css/app.css'])
<style>
    /* Estilo do tema */
    .page-title {
        font-size: 2.5rem;
        font-weight: bold;
        color: #007bff; /* Cor principal do tema */
    }
    .btn-primary {
        background-color: #007bff;
        border-color: #007bff;
        font-weight: bold;
    }
    .btn-primary:hover {
        background-color: #0056b3;
        border-color: #004085;
    }
    .accordion-button {
        font-weight: bold;
        color: #333;
        background-color: #f8f9fa;
        border-radius: 8px !important;
    }
    .accordion-button:not(.collapsed) {
        color: #007bff;
        background-color: #e3f2fd;
    }
    .accordion-item {
        border: none;
        margin-bottom: 10px;
    }
    .accordion-header i {
        margin-right: 10px; /* Espaço entre o ícone e o texto */
    }
    .list-group-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background-color: #f8f9fa;
        border-radius: 6px;
        border: none;
        padding: 10px 15px;
        margin-bottom: 5px;
        transition: background-color 0.3s;
    }
    .list-group-item:hover {
        background-color: #e3f2fd;
    }
    .btn-edit {
        margin-left: 10px;
    }
    .modal-header {
        background-color: #007bff;
        color: white;
        border-radius: 6px 6px 0 0;
    }
    .modal-footer {
        border-top: none;
    }
    .form-control {
        border-radius: 6px;
    }
    .input-group {
        margin-bottom: 15px;
    }
    .input-group .form-control {
        border-right: none;
    }
    .input-group .btn {
        border-left: none;
    }
    @media (max-width: 768px) {
        .accordion-header {
            flex-direction: column;
            align-items: flex-start;
        }
        .btn-edit {
            margin-top: 10px;
        }
    }
</style>
@endsection

@section('vendor-script')
@vite(['resources/js/app.js'])
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="page-title">
        <i class="fas fa-tags"></i> Gerenciar Categorias
    </h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNovaCategoria">
        <i class="fas fa-plus-circle"></i> Nova Categoria
    </button>
</div>

<!-- Modal para adicionar nova categoria -->
<div class="modal fade" id="modalNovaCategoria" tabindex="-1" aria-labelledby="modalNovaCategoriaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNovaCategoriaLabel">Adicionar Categoria</h5>
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

<!-- Categorias -->
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
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Buscar produtos..." onkeyup="filterProducts(this, '{{ $categoria->id }}')">
                        <button class="btn btn-outline-secondary" type="button">
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
        document.getElementById('editar_nome').value = nome;
        document.getElementById('formEditarCategoria').action = `/categorias/${id}`;
    }
</script>
@endsection
