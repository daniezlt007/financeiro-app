<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Empresa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $empresaId = config('seed.empresa_id') ?? Empresa::first()?->id;

        // Criar usuário admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'empresa_id' => null,
            ]
        );
        $admin->syncRoles(['admin']);

        // Criar usuário funcionário (não admin)
        $funcionario = User::updateOrCreate(
            ['email' => 'funcionario@example.com'],
            [
                'name' => 'Funcionário Teste',
                'password' => Hash::make('password'),
                'is_admin' => false,
                'empresa_id' => $empresaId,
            ]
        );
        $funcionario->syncRoles(['funcionario']);

        // Criar usuário test@example.com como admin
        $test = User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Teste Admin',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'empresa_id' => null,
            ]
        );
        $test->syncRoles(['admin']);

        $this->command->info('Usuários criados/atualizados:');
        $this->command->line('- admin@example.com (Administrador) - senha: password');
        $this->command->line('- test@example.com (Administrador) - senha: password');
        $this->command->line('- funcionario@example.com (Funcionário) - senha: password');
    }
}