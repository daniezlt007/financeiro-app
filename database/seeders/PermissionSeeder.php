<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Permissões do sistema (formato: modulo.acao)
     */
    public const PERMISSIONS = [
        // Vendas
        'vendas.visualizar',
        'vendas.criar',
        'vendas.editar',
        'vendas.excluir',
        // Pagamentos
        'pagamentos.visualizar',
        'pagamentos.criar',
        'pagamentos.editar',
        'pagamentos.excluir',
        // Clientes
        'clientes.visualizar',
        'clientes.criar',
        'clientes.editar',
        'clientes.excluir',
        // Serviços
        'servicos.visualizar',
        'servicos.criar',
        'servicos.editar',
        'servicos.excluir',
        // Produtos
        'produtos.visualizar',
        'produtos.criar',
        'produtos.editar',
        'produtos.excluir',
        // Relatórios
        'relatorios.vendas',
        'relatorios.pagamentos',
        // Financeiro
        'financeiro.visualizar',
        'financeiro.criar',
        'financeiro.editar',
        'financeiro.excluir',
        'financeiro.dashboard',
        'financeiro.baixa-lote',
        // Empresas (admin)
        'empresas.visualizar',
        'empresas.criar',
        'empresas.editar',
        'empresas.excluir',
        // Funcionários
        'funcionarios.visualizar',
        'funcionarios.criar',
        'funcionarios.editar',
        'funcionarios.excluir',
        // Usuários
        'usuarios.visualizar',
        'usuarios.criar',
        'usuarios.editar',
        'usuarios.excluir',
        // Metas
        'metas.visualizar',
        'metas.criar',
        'metas.editar',
        'metas.excluir',
        // Recorrências
        'recorrencias.visualizar',
        'recorrencias.criar',
        'recorrencias.editar',
        'recorrencias.excluir',
        // Auditoria
        'auditoria.visualizar',
        // Permissões (gestão de permissões)
        'permissoes.gerenciar',
    ];

    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Role: Gerente - acesso amplo operacional
        $gerente = Role::firstOrCreate(['name' => 'Gerente', 'guard_name' => 'web']);
        $gerente->syncPermissions([
            'vendas.visualizar', 'vendas.criar', 'vendas.editar', 'vendas.excluir',
            'pagamentos.visualizar', 'pagamentos.criar', 'pagamentos.editar', 'pagamentos.excluir',
            'clientes.visualizar', 'clientes.criar', 'clientes.editar', 'clientes.excluir',
            'servicos.visualizar', 'servicos.criar', 'servicos.editar', 'servicos.excluir',
            'produtos.visualizar', 'produtos.criar', 'produtos.editar', 'produtos.excluir',
            'relatorios.vendas', 'relatorios.pagamentos',
            'financeiro.visualizar', 'financeiro.criar', 'financeiro.editar', 'financeiro.excluir',
            'financeiro.dashboard', 'financeiro.baixa-lote',
            'funcionarios.visualizar', 'funcionarios.criar', 'funcionarios.editar', 'funcionarios.excluir',
            'metas.visualizar', 'metas.criar', 'metas.editar', 'metas.excluir',
            'recorrencias.visualizar', 'recorrencias.criar', 'recorrencias.editar', 'recorrencias.excluir',
        ]);

        // Role: Vendedor - operações básicas
        $vendedor = Role::firstOrCreate(['name' => 'Vendedor', 'guard_name' => 'web']);
        $vendedor->syncPermissions([
            'vendas.visualizar', 'vendas.criar', 'vendas.editar',
            'pagamentos.visualizar', 'pagamentos.criar', 'pagamentos.editar',
            'clientes.visualizar', 'clientes.criar', 'clientes.editar',
            'servicos.visualizar', 'servicos.criar', 'servicos.editar',
            'produtos.visualizar', 'produtos.criar', 'produtos.editar',
            'relatorios.vendas',
        ]);

        // Role: Financeiro - foco em financeiro e relatórios
        $financeiro = Role::firstOrCreate(['name' => 'Financeiro', 'guard_name' => 'web']);
        $financeiro->syncPermissions([
            'vendas.visualizar', 'pagamentos.visualizar', 'pagamentos.criar', 'pagamentos.editar', 'pagamentos.excluir',
            'clientes.visualizar', 'servicos.visualizar', 'produtos.visualizar',
            'relatorios.vendas', 'relatorios.pagamentos',
            'financeiro.visualizar', 'financeiro.criar', 'financeiro.editar', 'financeiro.excluir',
            'financeiro.dashboard', 'financeiro.baixa-lote',
        ]);

        // Role: Visualizador - apenas visualização
        $visualizador = Role::firstOrCreate(['name' => 'Visualizador', 'guard_name' => 'web']);
        $visualizador->syncPermissions([
            'vendas.visualizar', 'pagamentos.visualizar', 'clientes.visualizar',
            'servicos.visualizar', 'produtos.visualizar', 'relatorios.vendas', 'relatorios.pagamentos',
        ]);
    }
}
