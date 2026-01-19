@extends('layouts.layoutMaster')

@section('title', 'Detalhes do Fornecedor')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-10">
        <div class="card border-top border-primary border-3 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent py-3">
                <h4 class="mb-0 text-primary fw-bold">
                    <i class="bx bx-building-house me-2"></i>Detalhes do Fornecedor
                </h4>
                <a href="{{ route('fornecedores.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Voltar
                </a>
            </div>

            <div class="card-body p-4">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>CNPJ:</strong> {{ $fornecedor->cnpj }}
                    </div>
                    <div class="col-md-8">
                        <strong>Nome/Razão Social:</strong> {{ $fornecedor->nome }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Email:</strong> {{ $fornecedor->email }}
                    </div>
                    <div class="col-md-6">
                        <strong>Telefone:</strong> {{ $fornecedor->telefone }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-12">
                        <strong>Endereço:</strong> 
                        {{ $fornecedor->endereco }}, {{ $fornecedor->numero }} - {{ $fornecedor->bairro }}
                        <br>
                        {{ $fornecedor->cidade }} / {{ $fornecedor->uf }} - CEP: {{ $fornecedor->cep }}
                    </div>
                </div>

                <div class="mt-4">
                    <a href="{{ route('fornecedores.edit', $fornecedor->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-1"></i> Editar
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
