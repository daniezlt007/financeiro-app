<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Criar usuário admin
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'empresa_id' => null,
            ]
        );

        // Criar usuário funcionário (não admin)
        User::updateOrCreate(
            ['email' => 'funcionario@example.com'],
            [
                'name' => 'Funcionário Teste',
                'password' => Hash::make('password'),
                'is_admin' => false,
                'empresa_id' => null,
            ]
        );

        // Criar usuário test@example.com como admin
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Teste Admin',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'empresa_id' => null,
            ]
        );

        $this->command->info('Usuários criados/atualizados:');
        $this->command->line('- admin@example.com (Administrador) - senha: password');
        $this->command->line('- test@example.com (Administrador) - senha: password');
        $this->command->line('- funcionario@example.com (Funcionário) - senha: password');
    }
}