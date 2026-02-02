<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;

class UserController extends Controller
{
    /** Permissões agrupadas com labels amigáveis */
    private function getPermissionGroups(): array
    {
        return [
            'Dashboard' => [
                ['name' => 'dashboard.faturamento', 'label' => 'Faturamento e Vendas (hoje/mês)'],
                ['name' => 'dashboard.clientes', 'label' => 'Total de Clientes'],
                ['name' => 'dashboard.produtos', 'label' => 'Produtos'],
                ['name' => 'dashboard.servicos', 'label' => 'Serviços'],
                ['name' => 'dashboard.pendentes', 'label' => 'Pagamentos Pendentes'],
                ['name' => 'dashboard.vendas_recentes', 'label' => 'Vendas Recentes'],
            ],
            'Vendas' => [
                ['name' => 'vendas.ver', 'label' => 'Visualizar vendas'],
                ['name' => 'vendas.criar', 'label' => 'Criar vendas'],
                ['name' => 'vendas.editar', 'label' => 'Editar vendas'],
                ['name' => 'vendas.excluir', 'label' => 'Excluir vendas'],
            ],
            'Pagamentos' => [
                ['name' => 'pagamentos.ver', 'label' => 'Visualizar pagamentos'],
                ['name' => 'pagamentos.criar', 'label' => 'Criar pagamentos'],
                ['name' => 'pagamentos.editar', 'label' => 'Editar pagamentos'],
                ['name' => 'pagamentos.excluir', 'label' => 'Excluir pagamentos'],
                ['name' => 'pagamentos.baixa_lote', 'label' => 'Baixa em lote'],
            ],
            'Financeiro' => [
                ['name' => 'financeiro.ver', 'label' => 'Visualizar financeiro'],
                ['name' => 'financeiro.criar', 'label' => 'Criar transações'],
                ['name' => 'financeiro.editar', 'label' => 'Editar transações'],
                ['name' => 'financeiro.excluir', 'label' => 'Excluir transações'],
            ],
            'Relatórios' => [
                ['name' => 'relatorios.vendas', 'label' => 'Relatório de vendas'],
                ['name' => 'relatorios.pagamentos', 'label' => 'Relatório de pagamentos'],
            ],
            'Configurações' => [
                ['name' => 'configuracoes.empresas', 'label' => 'Gerenciar empresas'],
                ['name' => 'configuracoes.funcionarios', 'label' => 'Gerenciar funcionários'],
                ['name' => 'configuracoes.users', 'label' => 'Gerenciar usuários'],
                ['name' => 'configuracoes.auditoria', 'label' => 'Visualizar auditoria'],
            ],
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with(['empresa', 'permissions'])->latest()->paginate(15);
        return Inertia::render('Users/Index', ['data' => $users]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $empresas = \App\Models\Empresa::select('id', 'nome_fantasia')->orderBy('nome_fantasia')->get();
        $permissionGroups = $this->getPermissionGroups();
        return Inertia::render('Users/Create', ['empresas' => $empresas, 'permissionGroups' => $permissionGroups]);
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
            'permissions' => 'array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $permissions = $validated['permissions'] ?? [];
        $isAdmin = $validated['is_admin'] ?? false;
        unset($validated['permissions']);

        $validated['is_admin'] = $isAdmin;
        $user = User::create($validated);

        $user->syncRoles([]);
        if ($isAdmin) {
            $user->syncPermissions(Permission::pluck('name')->toArray());
        } elseif (!empty($permissions)) {
            $user->syncPermissions($permissions);
        }
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

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
        $user->load('permissions');
        $empresas = \App\Models\Empresa::select('id', 'nome_fantasia')->orderBy('nome_fantasia')->get();
        $permissionGroups = $this->getPermissionGroups();
        $userPermissions = $user->getAllPermissions()->pluck('name')->toArray();
        return Inertia::render('Users/Edit', [
            'item' => $user,
            'empresas' => $empresas,
            'permissionGroups' => $permissionGroups,
            'userPermissions' => $userPermissions,
        ]);
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
            'permissions' => 'array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $permissions = $validated['permissions'] ?? [];
        $isAdmin = $validated['is_admin'] ?? false;
        unset($validated['permissions']);

        $validated['is_admin'] = $isAdmin;
        $user->update($validated);

        $user->syncRoles([]);
        if ($isAdmin) {
            $user->syncPermissions(Permission::pluck('name')->toArray());
        } elseif (!empty($permissions)) {
            $user->syncPermissions($permissions);
        } else {
            $user->syncPermissions([]);
        }
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

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