@extends('layouts.layoutMaster')

@section('title', 'Gestão de Usuários')

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible" role="alert">
    <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">
        <i class="bx bx-check-circle me-1"></i> Sucesso!
    </h6>
    <p class="mb-0">{{ session('success') }}</p>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible" role="alert">
    <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">
        <i class="bx bx-error-circle me-1"></i> Erro!
    </h6>
    <p class="mb-0">{{ session('error') }}</p>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="text-primary" style="font-size: 2.5rem; font-weight: bold;">
        <i class="bx bx-user"></i> Gestão de Usuários
    </h1>
    <a href="{{ route('users.create') }}" class="btn btn-primary">
        <i class="bx bx-plus me-1"></i> Novo Usuário
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Usuários Cadastrados</h5>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Função</th>
                    <th>Data de Cadastro</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @foreach($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td><strong>{{ $user->name }}</strong></td>
                    <td>{{ $user->email }}</td>
                    <td>
                        @if($user->role === 'admin')
                        <span class="badge bg-label-primary">Administrador</span>
                        @else
                        <span class="badge bg-label-secondary">Usuário</span>
                        @endif
                    </td>
                    <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-icon btn-outline-primary" title="Editar">
                                <i class="bx bx-edit-alt"></i>
                            </a>

                            @if($user->id !== 1) <!-- Prevent deleting master user via UI -->
                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este usuário?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Excluir">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection