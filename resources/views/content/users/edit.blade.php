@extends('layouts.layoutMaster')

@section('title', 'Editar Usuário')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold py-3 mb-0">
        <span class="text-muted fw-light">Usuários /</span> Editar Usuário
    </h4>
    <a href="{{ route('users.index') }}" class="btn btn-secondary">
        <i class="bx bx-arrow-back me-1"></i> Voltar
    </a>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <h5 class="card-header">Detalhes do Usuário</h5>
            <div class="card-body">
                <form action="{{ route('users.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label for="name" class="form-label">Nome</label>
                            <input class="form-control @error('name') is-invalid @enderror" type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required />
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="email" class="form-label">E-mail</label>
                            <input class="form-control @error('email') is-invalid @enderror" type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required />
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="role" class="form-label">Função</label>
                            <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required {{ $user->id === 1 ? 'disabled' : '' }}>
                                <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>Usuário</option>
                                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Administrador</option>
                            </select>
                            @if($user->id === 1)
                                <input type="hidden" name="role" value="admin">
                                <div class="form-text">O usuário principal não pode ter sua função alterada.</div>
                            @endif
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="password" class="form-label">Nova Senha (deixe em branco para manter)</label>
                            <input class="form-control @error('password') is-invalid @enderror" type="password" id="password" name="password" />
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="password_confirmation" class="form-label">Confirmar Nova Senha</label>
                            <input class="form-control" type="password" id="password_confirmation" name="password_confirmation" />
                        </div>
                    </div>
                    <div class="mt-2">
                        <button type="submit" class="btn btn-primary me-2">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
