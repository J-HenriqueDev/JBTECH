<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Restriction: Only Admins can access
        if (!Auth::user()->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'Acesso não autorizado.');
        }

        $users = User::all();
        return view('content.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Restriction: Only Admins can access
        if (!Auth::user()->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'Acesso não autorizado.');
        }

        return view('content.users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Restriction: Only Admins can access
        if (!Auth::user()->isAdmin()) {
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
        // Restriction: Only Admins can access
        if (!Auth::user()->isAdmin()) {
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
        // Restriction: Only Admins can access
        if (!Auth::user()->isAdmin()) {
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
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Restriction: Only Admins can access
        if (!Auth::user()->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'Acesso não autorizado.');
        }

        if ($id == 1) {
            return redirect()->route('users.index')->with('error', 'Você não pode excluir a si mesmo.');
        }

        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuário excluído com sucesso!');
    }
}
