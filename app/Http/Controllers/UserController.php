<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Configuracao;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()->canAccess('users', 'view')) {
            return redirect()->route('dashboard')->with('error', 'Acesso não autorizado.');
        }

        $users = User::all();
        $modules = [
            'clientes',
            'fornecedores',
            'produtos',
            'compras',
            'os',
            'cobrancas',
            'vendas',
            'relatorios',
            'naturezas',
            'notas-entrada',
            'nfe',
            'nfse',
            'users'
        ];
        $roles = $this->rolesList();
        $permissions = [];
        $stored = Configuracao::get('roles_permissions', null);
        if ($stored) {
            try {
                $permissions = json_decode($stored, true) ?: [];
            } catch (\Exception $e) {
                $permissions = [];
            }
        }
        return view('content.users.index', compact('users', 'modules', 'roles', 'permissions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!Auth::user()->canAccess('users', 'edit')) {
            return redirect()->route('dashboard')->with('error', 'Acesso não autorizado.');
        }

        return view('content.users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!Auth::user()->canAccess('users', 'edit')) {
            return redirect()->route('dashboard')->with('error', 'Acesso não autorizado.');
        }

        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);

        return redirect()->route('users.index')->with('success', 'Usuário criado com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        if (!Auth::user()->canAccess('users', 'edit')) {
            return redirect()->route('dashboard')->with('error', 'Acesso não autorizado.');
        }

        $user = User::findOrFail($id);
        return view('content.users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if (!Auth::user()->canAccess('users', 'edit')) {
            return redirect()->route('dashboard')->with('error', 'Acesso não autorizado.');
        }

        $user = User::findOrFail($id);

        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', 'in:admin,user'],
        ]);

        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];

        // Prevent changing role of user ID 1 (Super Admin)
        if ($user->id !== 1) {
            $user->role = $validatedData['role'];
        }

        if (!empty($validatedData['password'])) {
            $user->password = Hash::make($validatedData['password']);
        }

        $user->save();

        return redirect()->route('users.index')->with('success', 'Usuário atualizado com sucesso!');
    }

    /**
     * Update roles permissions map
     */
    public function updatePermissions(Request $request)
    {
        if (!Auth::user()->canAccess('users', 'edit')) {
            return redirect()->route('dashboard')->with('error', 'Acesso não autorizado.');
        }
        $data = $request->input('perm', []);
        $allowedLevels = ['none', 'view', 'edit', 'full'];
        $sanitized = [];
        foreach ($data as $module => $byRole) {
            $sanitized[$module] = [];
            foreach ($byRole as $role => $level) {
                $sanitized[$module][$role] = in_array($level, $allowedLevels) ? $level : 'none';
            }
        }
        Configuracao::set('roles_permissions', json_encode($sanitized), 'sistema', 'json', 'Mapa de permissões por cargo');
        return redirect()->route('users.index')->with('success', 'Permissões atualizadas com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (!Auth::user()->canAccess('users', 'edit')) {
            return redirect()->route('dashboard')->with('error', 'Acesso não autorizado.');
        }

        if ($id == 1) {
            return redirect()->route('users.index')->with('error', 'Você não pode excluir a si mesmo.');
        }

        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuário excluído com sucesso!');
    }

    public function updateRolePermissions(Request $request)
    {
        if (!Auth::user()->canAccess('users', 'edit')) {
            return redirect()->route('dashboard')->with('error', 'Acesso não autorizado.');
        }
        $role = $request->input('role');
        $perms = $request->input('perm', []);
        $allowed = ['none', 'view', 'edit', 'full'];
        $stored = Configuracao::get('roles_permissions', null);
        $map = [];
        if ($stored) {
            try {
                $map = json_decode($stored, true) ?: [];
            } catch (\Exception $e) {
                $map = [];
            }
        }
        foreach ($perms as $module => $level) {
            $lvl = in_array($level, $allowed) ? $level : 'none';
            if (!isset($map[$module])) $map[$module] = [];
            $map[$module][$role] = $lvl;
        }
        Configuracao::set('roles_permissions', json_encode($map), 'sistema', 'json', 'Mapa de permissões por cargo');
        return redirect()->route('users.index', ['role' => $role])->with('success', 'Permissões do cargo atualizadas com sucesso!');
    }

    public function addRole(Request $request)
    {
        if (!Auth::user()->canAccess('users', 'edit')) {
            return redirect()->route('dashboard')->with('error', 'Acesso não autorizado.');
        }
        $name = trim($request->input('name'));
        if ($name === '' || strlen($name) > 50) {
            return back()->with('error', 'Nome de cargo inválido.');
        }
        $name = strtolower(preg_replace('/[^a-z0-9_\-]/i', '', $name));
        $roles = $this->rolesList();
        if (in_array($name, $roles)) {
            return back()->with('error', 'Cargo já existe.');
        }
        $roles[] = $name;
        Configuracao::set('roles_list', json_encode($roles), 'sistema', 'json', 'Lista de cargos');
        return redirect()->route('users.index', ['role' => $name])->with('success', 'Cargo criado com sucesso!');
    }

    public function renameRole(Request $request)
    {
        if (!Auth::user()->canAccess('users', 'edit')) {
            return redirect()->route('dashboard')->with('error', 'Acesso não autorizado.');
        }
        $from = trim($request->input('from'));
        $to = trim($request->input('to'));
        if ($from === 'admin') {
            return back()->with('error', 'Não é permitido renomear o cargo admin.');
        }
        $to = strtolower(preg_replace('/[^a-z0-9_\-]/i', '', $to));
        if ($to === '' || strlen($to) > 50) {
            return back()->with('error', 'Novo nome inválido.');
        }
        $roles = $this->rolesList();
        if (!in_array($from, $roles)) {
            return back()->with('error', 'Cargo de origem não existe.');
        }
        if (in_array($to, $roles)) {
            return back()->with('error', 'Já existe um cargo com este nome.');
        }
        $updatedRoles = array_map(function ($r) use ($from, $to) {
            return $r === $from ? $to : $r;
        }, $roles);
        Configuracao::set('roles_list', json_encode($updatedRoles), 'sistema', 'json', 'Lista de cargos');
        User::where('role', $from)->update(['role' => $to]);
        $stored = Configuracao::get('roles_permissions', null);
        $map = [];
        if ($stored) {
            try {
                $map = json_decode($stored, true) ?: [];
            } catch (\Exception $e) {
                $map = [];
            }
        }
        foreach ($map as $module => $byRole) {
            if (isset($map[$module][$from])) {
                $map[$module][$to] = $map[$module][$from];
                unset($map[$module][$from]);
            }
        }
        Configuracao::set('roles_permissions', json_encode($map), 'sistema', 'json', 'Mapa de permissões por cargo');
        return redirect()->route('users.index', ['role' => $to])->with('success', 'Cargo renomeado com sucesso!');
    }

    public function deleteRole(Request $request)
    {
        if (!Auth::user()->canAccess('users', 'edit')) {
            return redirect()->route('dashboard')->with('error', 'Acesso não autorizado.');
        }
        $role = trim($request->input('role'));
        if ($role === 'admin') {
            return back()->with('error', 'Não é permitido excluir o cargo admin.');
        }
        $count = User::where('role', $role)->count();
        if ($count > 0) {
            return back()->with('error', 'Há usuários vinculados a este cargo.');
        }
        $roles = $this->rolesList();
        $roles = array_values(array_filter($roles, fn($r) => $r !== $role));
        Configuracao::set('roles_list', json_encode($roles), 'sistema', 'json', 'Lista de cargos');
        $stored = Configuracao::get('roles_permissions', null);
        $map = [];
        if ($stored) {
            try {
                $map = json_decode($stored, true) ?: [];
            } catch (\Exception $e) {
                $map = [];
            }
        }
        foreach ($map as $module => $byRole) {
            if (isset($map[$module][$role])) {
                unset($map[$module][$role]);
            }
        }
        Configuracao::set('roles_permissions', json_encode($map), 'sistema', 'json', 'Mapa de permissões por cargo');
        return redirect()->route('users.index')->with('success', 'Cargo excluído com sucesso!');
    }

    protected function rolesList(): array
    {
        $roles = ['admin', 'user'];
        $stored = Configuracao::get('roles_list', null);
        if ($stored) {
            try {
                $list = json_decode($stored, true);
                if (is_array($list) && count($list) > 0) {
                    $roles = $list;
                }
            } catch (\Exception $e) {
            }
        } else {
            $distinct = User::select('role')->distinct()->pluck('role')->filter()->values()->toArray();
            foreach ($distinct as $r) {
                if (!in_array($r, $roles)) $roles[] = $r;
            }
        }
        return array_values(array_unique($roles));
    }
}
