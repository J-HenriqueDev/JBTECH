@extends('layouts.layoutMaster')

@section('vendor-style')
@vite([/* CSS do Bootstrap ou qualquer outro que você estiver usando */])
@endsection

@section('vendor-script')
@vite([/* JS do Bootstrap ou qualquer outro que você estiver usando */])
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-4 text-primary">Lista de Categorias</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNovaCategoria">
        Adicionar Categoria
    </button>
</div>

<!-- Modal para adicionar nova categoria -->
<div class="modal fade" id="modalNovaCategoria" tabindex="-1" aria-labelledby="modalNovaCategoriaLabel" aria-hidden="true">
    <div class="modal-dialog">
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
                    <input type="text" class="form-control" name="nome" id="nome" required>
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

<div class="accordion" id="accordionCategorias">
    @foreach ($categorias as $categoria)
        <div class="accordion-item">
            <h2 class="accordion-header" id="heading{{ $categoria->id }}">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $categoria->id }}" aria-expanded="true" aria-controls="collapse{{ $categoria->id }}">
                    {{ $categoria->nome }}
                </button>
            </h2>
            <div id="collapse{{ $categoria->id }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $categoria->id }}" data-bs-parent="#accordionCategorias">
                <div class="accordion-body">
                    <input type="text" class="form-control mb-2" placeholder="Buscar produtos..." onkeyup="filterProducts(this, '{{ $categoria->id }}')">
                    <ul id="produtos{{ $categoria->id }}">
                        @foreach ($categoria->produtos as $produto)
                            <li>{{ $produto->nome }}</li>
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
</script>

@endsection
