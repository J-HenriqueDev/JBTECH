@extends('layouts.layoutMaster')

@section('title', 'Lista de Cobranças')

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
        <i class="fas fa-money-check-alt"></i> Lista de Cobranças
    </h1>
    <a href="{{ route('cobrancas.create') }}" class="btn btn-primary">
        <i class="fas fa-plus-circle me-1"></i> Nova Cobrança
    </a>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Cobranças Cadastradas ({{ count($cobrancas) }})</h5>
                    <div class="d-flex align-items-center">
                        <!-- Seletor de Ordenação -->
                        <select id="ordenacao" class="form-select" onchange="ordenarCobrancas()">
                            <option value="recentes" selected>Mais recentes primeiro</option>
                            <option value="antigos">Mais antigos primeiro</option>
                            <option value="maior_valor">Maior valor primeiro</option>
                            <option value="menor_valor">Menor valor primeiro</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <input type="text" id="search" class="form-control" placeholder="Pesquisar cobranças..." onkeyup="filterCobrancas()">
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-striped" id="cobrancasTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Venda</th>
                                <th>Método</th>
                                <th>Valor</th>
                                <th>Status</th>
                                <th>Data de Vencimento</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cobrancas as $cobranca)
                            <tr>
                                <td>{{ $cobranca->id }}</td>
                                <td class="cobranca-venda">
                                    <strong>{{ \Illuminate\Support\Str::limit($cobranca->venda->id ?? 'Venda não encontrada', 40, '...') }}</strong>
                                </td>
                                <td class="cobranca-metodo">{{ ucfirst($cobranca->metodo_pagamento) }}</td>
                                <td class="cobranca-valor"><strong>R$ {{ number_format($cobranca->valor, 2, ',', '.') }}</strong></td>
                                <td class="cobranca-status">
                                    <span class="badge bg-{{ $cobranca->status == 'pago' ? 'success' : ($cobranca->status == 'cancelado' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($cobranca->status) }}
                                    </span>
                                </td>
                                <td class="cobranca-vencimento">{{ \Carbon\Carbon::parse($cobranca->data_vencimento)->format('d/m/Y') }}</td>
                                <!-- Coluna oculta para updated_at -->
                                <td class="cobranca-updated-at" style="display: none;">{{ $cobranca->updated_at }}</td>
                                <td>
                                    <a href="{{ route('cobrancas.edit', $cobranca->id) }}" class="btn btn-info">
                                        <i class="fas fa-eye"></i> Ver / Editar
                                    </a>
                                    <form action="{{ route('cobrancas.destroy', $cobranca->id) }}" method="POST" style="display:inline;">
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
    // Ordena a tabela ao carregar a página
    document.addEventListener('DOMContentLoaded', function () {
        ordenarCobrancas();
    });

    // Filtra as cobranças conforme o texto digitado
    function filterCobrancas() {
        const input = document.getElementById('search');
        const filter = input.value.toLowerCase();
        const table = document.getElementById('cobrancasTable');
        const tr = table.getElementsByTagName('tr');

        for (let i = 1; i < tr.length; i++) { // Começa em 1 para ignorar o cabeçalho
            const tdId = tr[i].getElementsByTagName("td")[0]; // Coluna ID
            const tdVenda = tr[i].getElementsByClassName("cobranca-venda")[0];
            const tdMetodo = tr[i].getElementsByClassName("cobranca-metodo")[0];
            const tdValor = tr[i].getElementsByClassName("cobranca-valor")[0];
            const tdStatus = tr[i].getElementsByClassName("cobranca-status")[0];
            const tdVencimento = tr[i].getElementsByClassName("cobranca-vencimento")[0];

            if (tdId && tdVenda && tdMetodo && tdValor && tdStatus && tdVencimento) {
                const idValue = tdId.textContent || tdId.innerText;
                const vendaValue = tdVenda.textContent || tdVenda.innerText;
                const metodoValue = tdMetodo.textContent || tdMetodo.innerText;
                const valorValue = tdValor.textContent || tdValor.innerText;
                const statusValue = tdStatus.textContent || tdStatus.innerText;
                const vencimentoValue = tdVencimento.textContent || tdVencimento.innerText;

                if (idValue.toLowerCase().indexOf(filter) > -1 ||
                    vendaValue.toLowerCase().indexOf(filter) > -1 ||
                    metodoValue.toLowerCase().indexOf(filter) > -1 ||
                    valorValue.toLowerCase().indexOf(filter) > -1 ||
                    statusValue.toLowerCase().indexOf(filter) > -1 ||
                    vencimentoValue.toLowerCase().indexOf(filter) > -1) {
                    tr[i].style.display = ""; // Exibe a linha
                } else {
                    tr[i].style.display = "none"; // Oculta a linha
                }
            }
        }
    }

    // Ordena as cobranças conforme a opção selecionada
    function ordenarCobrancas() {
        const ordenacao = document.getElementById('ordenacao').value;
        const table = document.getElementById('cobrancasTable');
        const tbody = table.getElementsByTagName('tbody')[0];
        const rows = Array.from(tbody.getElementsByTagName('tr'));

        rows.sort((a, b) => {
            const idA = parseInt(a.getElementsByTagName('td')[0].textContent);
            const idB = parseInt(b.getElementsByTagName('td')[0].textContent);
            const valorA = parseFloat(a.getElementsByClassName('cobranca-valor')[0].textContent.replace('R$ ', '').replace('.', '').replace(',', '.'));
            const valorB = parseFloat(b.getElementsByClassName('cobranca-valor')[0].textContent.replace('R$ ', '').replace('.', '').replace(',', '.'));
            const updatedAtA = new Date(a.getElementsByClassName('cobranca-updated-at')[0].textContent);
            const updatedAtB = new Date(b.getElementsByClassName('cobranca-updated-at')[0].textContent);

            switch (ordenacao) {
                case 'recentes':
                    return updatedAtB - updatedAtA; // Mais recentes primeiro (considerando updated_at)
                case 'antigos':
                    return updatedAtA - updatedAtB; // Mais antigos primeiro (considerando updated_at)
                case 'maior_valor':
                    return valorB - valorA; // Maior valor primeiro
                case 'menor_valor':
                    return valorA - valorB; // Menor valor primeiro
                default:
                    return 0;
            }
        });

        // Remove as linhas atuais da tabela
        while (tbody.firstChild) {
            tbody.removeChild(tbody.firstChild);
        }

        // Adiciona as linhas ordenadas
        rows.forEach(row => tbody.appendChild(row));
    }
</script>

@endsection
