<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

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

        // Criar usuário funcionário com role Gerente
        $funcionario = User::updateOrCreate(
            ['email' => 'funcionario@example.com'],
            [
                'name' => 'Funcionário Teste',
                'password' => Hash::make('password'),
                'is_admin' => false,
                'empresa_id' => null,
            ]
        );
        $gerente = Role::where('name', 'Gerente')->where('guard_name', 'web')->first();
        if ($gerente) {
            $funcionario->syncRoles([$gerente]);
        }

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
        $this->command->line('- funcionario@example.com (Funcionário/Gerente) - senha: password');
    }
}