@extends('layouts.layoutMaster')

@section('title', 'Gestão de Usuários')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/select2/select2.scss'
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/select2/select2.js'
])
@endsection

@section('page-script')
@vite([
'resources/assets/js/forms-selects.js'
])
@endsection

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
<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0"><i class="bx bx-shield-quarter me-1"></i> Gestão de Cargos e Permissões</h5>
        <small class="text-muted">Defina acesso por módulo: ver, editar ou total</small>
    </div>
    <div class="card-body">
        @php
        $selectedRole = request('role', $roles[0] ?? 'admin');
        @endphp
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <form method="GET" action="{{ route('users.index') }}">
                    <label class="form-label fw-bold">Selecionar cargo</label>
                    <select name="role" class="form-select select2" onchange="this.form.submit()">
                        @foreach($roles as $role)
                        <option value="{{ $role }}" {{ $selectedRole === $role ? 'selected' : '' }} class="text-capitalize">{{ $role }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
            <div class="col-md-8 d-flex align-items-end justify-content-end gap-2">
                <form action="{{ route('users.roles.add') }}" method="POST" class="d-flex gap-2">
                    @csrf
                    <input type="text" name="name" class="form-control" placeholder="Novo cargo" required style="max-width: 220px;">
                    <button type="submit" class="btn btn-success"><i class="bx bx-plus me-1"></i> Criar</button>
                </form>
                <form action="{{ route('users.roles.rename') }}" method="POST" class="d-flex gap-2">
                    @csrf
                    <input type="hidden" name="from" value="{{ $selectedRole }}">
                    <input type="text" name="to" class="form-control" placeholder="Renomear cargo" required style="max-width: 220px;">
                    <button type="submit" class="btn btn-warning"><i class="bx bx-edit-alt me-1"></i> Renomear</button>
                </form>
                <form action="{{ route('users.roles.delete') }}" method="POST" class="d-flex gap-2" onsubmit="return confirm('Excluir este cargo?');">
                    @csrf
                    <input type="hidden" name="role" value="{{ $selectedRole }}">
                    <button type="submit" class="btn btn-danger" {{ $selectedRole === 'admin' ? 'disabled' : '' }}><i class="bx bx-trash me-1"></i> Excluir</button>
                </form>
            </div>
        </div>

        <form action="{{ route('users.permissions.updateRole') }}" method="POST">
            @csrf
            <input type="hidden" name="role" value="{{ $selectedRole }}">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Módulo</th>
                            <th class="text-capitalize">Permissão - {{ $selectedRole }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($modules as $module)
                        @php
                        $current = $permissions[$module][$selectedRole] ?? ($selectedRole === 'admin' ? 'full' : 'view');
                        @endphp
                        <tr>
                            <td class="text-capitalize">{{ str_replace('-', ' ', $module) }}</td>
                            <td>
                                <div class="d-flex gap-3">
                                    <label class="me-3"><input type="radio" name="perm[{{ $module }}]" value="none" {{ $current === 'none' ? 'checked' : '' }}> Sem acesso</label>
                                    <label class="me-3"><input type="radio" name="perm[{{ $module }}]" value="view" {{ $current === 'view' ? 'checked' : '' }}> Ver</label>
                                    <label class="me-3"><input type="radio" name="perm[{{ $module }}]" value="edit" {{ $current === 'edit' ? 'checked' : '' }}> Editar</label>
                                    <label><input type="radio" name="perm[{{ $module }}]" value="full" {{ $current === 'full' ? 'checked' : '' }}> Total</label>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="bx bx-save me-1"></i> Salvar Permissões do Cargo
                </button>
            </div>
        </form>
    </div>
</div>
@endsection