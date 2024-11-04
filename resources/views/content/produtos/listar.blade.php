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
                <!-- Barra de Pesquisa -->
                <div class="mb-4">
                    <input type="text" id="search" class="form-control" placeholder="Pesquisar produtos..." onkeyup="filterProducts()">
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-striped" id="productsTable">
                        <thead>
                            <tr>
                                <th>ID</th> <!-- Nova coluna para ID -->
                                <th>Nome do Produto</th>
                                <th>Quantidade em Estoque</th>
                                <th>Preço de Venda</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($produtos as $produto)
                                <tr>
                                    <td>{{ $produto->id }}</td> <!-- Exibe o ID do produto -->
                                    <td class="product-name">{{ $produto->nome }}</td>
                                    <td class="product-quantity">{{ $produto->estoque }}</td>
                                    <td class="product-price">{{ number_format($produto->preco_venda, 2, ',', '.') }}</td>
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

<script>
    function filterProducts() {
        const input = document.getElementById('search');
        const filter = input.value.toLowerCase();
        const table = document.getElementById('productsTable');
        const tr = table.getElementsByTagName('tr');

        for (let i = 1; i < tr.length; i++) { // Começa em 1 para ignorar o cabeçalho
            const tdName = tr[i].getElementsByClassName("product-name")[0];
            const tdQuantity = tr[i].getElementsByClassName("product-quantity")[0];
            const tdPrice = tr[i].getElementsByClassName("product-price")[0];
            const tdId = tr[i].getElementsByTagName("td")[0]; // Obtém a coluna ID

            if (tdName) {
                const txtValue = tdName.textContent || tdName.innerText;
                const quantityValue = tdQuantity.textContent || tdQuantity.innerText;
                const priceValue = tdPrice.textContent || tdPrice.innerText;
                const idValue = tdId.textContent || tdId.innerText; // Obtém o valor do ID

                if (txtValue.toLowerCase().indexOf(filter) > -1 ||
                    quantityValue.toLowerCase().indexOf(filter) > -1 ||
                    priceValue.toLowerCase().indexOf(filter) > -1 ||
                    idValue.indexOf(filter) > -1) { // Verifica se o ID contém o filtro
                    tr[i].style.display = ""; // Exibe a linha
                } else {
                    tr[i].style.display = "none"; // Oculta a linha
                }
            }
        }
    }
</script>

@endsection
