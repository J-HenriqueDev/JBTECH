@extends('layouts.layoutMaster')

@section('content')
<div class="container">
    <h1 class="mb-4">Importar Produtos via XML</h1>
    <form action="{{ route('produtos.import') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label for="xml_file">Carregar Arquivo XML</label>
            <input type="file" name="xml_file" id="xml_file" class="form-control" accept=".xml" required>
        </div>
        <button type="submit" class="btn btn-primary">Importar</button>
    </form>

    @if (!empty($productsData))
    <h2 class="mt-5">Produtos Importados</h2>
    <form action="{{ route('produtos.store') }}" method="POST">
        @csrf
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Preço Custo</th>
                        <th>Preço Venda</th>
                        <th>Código de Barras</th>
                        <th>NCM</th>
                        <th>Estoque</th>
                        <th>Categoria</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($productsData as $index => $product)
                    <tr>
                        <td><input type="text" name="produtos[{{ $index }}][nome]" value="{{ $product['nome'] }}" class="form-control" required></td>
                        <td><input type="number" name="produtos[{{ $index }}][preco_custo]" value="{{ $product['preco_custo'] }}" class="form-control" required></td>
                        <td><input type="number" name="produtos[{{ $index }}][preco_venda]" value="{{ $product['preco_venda'] }}" class="form-control" required></td>
                        <td><input type="text" name="produtos[{{ $index }}][codigo_barras]" value="{{ $product['codigo_barras'] }}" class="form-control"></td>
                        <td><input type="text" name="produtos[{{ $index }}][ncm]" value="{{ $product['ncm'] }}" class="form-control" required></td>
                        <td><input type="number" name="produtos[{{ $index }}][estoque]" value="{{ $product['estoque'] }}" class="form-control" required></td>
                        <td>
                            <select name="produtos[{{ $index }}][categoria_id]" class="form-control">
                                @foreach ($categorias as $categoria)
                                <option value="{{ $categoria->id }}">{{ $categoria->nome }}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <button type="submit" class="btn btn-success">Salvar Produtos</button>
    </form>
    @endif
</div>
@endsection
