<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with('empresa')->latest()->paginate(15);
        return Inertia::render('Users/Index', ['data' => $users]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $empresas = \App\Models\Empresa::select('id', 'nome_fantasia')->orderBy('nome_fantasia')->get();
        return Inertia::render('Users/Create', ['empresas' => $empresas]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nome_completo' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'empresa_id' => 'nullable|integer|exists:empresas,id',
            'is_admin' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('users.index')->with('success', 'Usuário criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load('empresa');
        return Inertia::render('Users/Show', ['item' => $user]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $empresas = \App\Models\Empresa::select('id', 'nome_fantasia')->orderBy('nome_fantasia')->get();
        return Inertia::render('Users/Edit', ['item' => $user, 'empresas' => $empresas]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nome_completo' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'empresa_id' => 'nullable|integer|exists:empresas,id',
            'is_admin' => 'boolean',
        ]);

        if ($validated['password']) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('users.index')->with('success', 'Usuário atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Não permitir deletar o próprio usuário
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Você não pode deletar seu próprio usuário!');
        }

        $user->delete();
        return redirect()->back()->with('success', 'Usuário excluído com sucesso!');
    }
}