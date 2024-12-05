@extends('layouts.layoutMaster')

@section('title', 'Lista de Orçamentos')

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
        <i class="fas fa-file-alt"></i> Lista de Orçamentos
    </h1>
    <a href="{{ route('orcamentos.create') }}" class="btn btn-primary">
        <i class="fas fa-plus-circle me-1"></i> Novo Orçamento
    </a>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">Orçamentos Cadastrados</h5>
                <!-- Barra de Pesquisa -->
                <div class="mb-4">
                    <input type="text" id="search" class="form-control" placeholder="Pesquisar orçamentos..." onkeyup="filterOrcamentos()">
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-striped" id="orcamentosTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Data</th>
                                <th>Validade</th>
                                <th>Valor Total</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orcamentos as $orcamento)
                                <tr>
                                    <td>{{ $orcamento->id }}</td>
                                    <td class="orcamento-cliente">{{ $orcamento->cliente->nome ?? 'Cliente não encontrado' }}</td>
                                    <td class="orcamento-data">{{ \Carbon\Carbon::parse($orcamento->data)->format('d/m/Y') }}</td>
                                    <td class="orcamento-validade">{{ \Carbon\Carbon::parse($orcamento->validade)->format('d/m/Y') }}</td>
                                    <td class="orcamento-valor">{{ number_format($orcamento->valor_total, 2, ',', '.') }}</td>
                                    <td>
                                        {{--  <a href="{{ route('orcamentos.edit', $orcamento->id) }}" class="btn btn-warning">Editar</a>  --}}
                                        <a href="{{ route('orcamentos.edit', $orcamento->id) }}" class="btn btn-info">
                                          <i class="fas fa-eye"></i> Ver / Editar
                                      </a>

                                        <form action="{{ route('orcamentos.destroy', $orcamento->id) }}" method="POST" style="display:inline;">
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
    function filterOrcamentos() {
        const input = document.getElementById('search');
        const filter = input.value.toLowerCase();
        const table = document.getElementById('orcamentosTable');
        const tr = table.getElementsByTagName('tr');

        for (let i = 1; i < tr.length; i++) { // Começa em 1 para ignorar o cabeçalho
            const tdId = tr[i].getElementsByTagName("td")[0]; // Coluna ID
            const tdCliente = tr[i].getElementsByClassName("orcamento-cliente")[0];
            const tdData = tr[i].getElementsByClassName("orcamento-data")[0];
            const tdValidade = tr[i].getElementsByClassName("orcamento-validade")[0];
            const tdValor = tr[i].getElementsByClassName("orcamento-valor")[0];

            if (tdId && tdCliente && tdData && tdValidade && tdValor) {
                const idValue = tdId.textContent || tdId.innerText;
                const clienteValue = tdCliente.textContent || tdCliente.innerText;
                const dataValue = tdData.textContent || tdData.innerText;
                const validadeValue = tdValidade.textContent || tdValidade.innerText;
                const valorValue = tdValor.textContent || tdValor.innerText;

                if (idValue.toLowerCase().indexOf(filter) > -1 ||
                    clienteValue.toLowerCase().indexOf(filter) > -1 ||
                    dataValue.toLowerCase().indexOf(filter) > -1 ||
                    validadeValue.toLowerCase().indexOf(filter) > -1 ||
                    valorValue.toLowerCase().indexOf(filter) > -1) {
                    tr[i].style.display = ""; // Exibe a linha
                } else {
                    tr[i].style.display = "none"; // Oculta a linha
                }
            }
        }
    }
</script>

@endsection
