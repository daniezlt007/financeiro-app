<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Dashboard
            'dashboard.faturamento',
            'dashboard.clientes',
            'dashboard.produtos',
            'dashboard.servicos',
            'dashboard.pendentes',
            'dashboard.vendas_recentes',
            // Vendas
            'vendas.ver',
            'vendas.criar',
            'vendas.editar',
            'vendas.excluir',
            // Pagamentos
            'pagamentos.ver',
            'pagamentos.criar',
            'pagamentos.editar',
            'pagamentos.excluir',
            'pagamentos.baixa_lote',
            // Financeiro
            'financeiro.ver',
            'financeiro.criar',
            'financeiro.editar',
            'financeiro.excluir',
            // Relatórios
            'relatorios.vendas',
            'relatorios.pagamentos',
            // Configurações
            'configuracoes.empresas',
            'configuracoes.funcionarios',
            'configuracoes.users',
            'configuracoes.auditoria',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->givePermissionTo(Permission::all());

        $gerente = Role::firstOrCreate(['name' => 'gerente', 'guard_name' => 'web']);
        $gerente->givePermissionTo([
            'dashboard.faturamento', 'dashboard.clientes', 'dashboard.produtos', 'dashboard.servicos', 'dashboard.pendentes', 'dashboard.vendas_recentes',
            'vendas.ver', 'vendas.criar', 'vendas.editar', 'vendas.excluir',
            'pagamentos.ver', 'pagamentos.criar', 'pagamentos.editar', 'pagamentos.excluir', 'pagamentos.baixa_lote',
            'financeiro.ver', 'relatorios.vendas', 'relatorios.pagamentos',
        ]);

        $funcionario = Role::firstOrCreate(['name' => 'funcionario', 'guard_name' => 'web']);
        $funcionario->givePermissionTo([
            'dashboard.faturamento', 'dashboard.clientes', 'dashboard.produtos', 'dashboard.servicos', 'dashboard.pendentes', 'dashboard.vendas_recentes',
            'vendas.ver', 'vendas.criar', 'vendas.editar',
            'pagamentos.ver', 'pagamentos.baixa_lote',
            'relatorios.vendas', 'relatorios.pagamentos',
        ]);

        $this->command->info('Permissões e papéis criados com sucesso.');
    }
}
