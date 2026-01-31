<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class AtribuirRoleGerente extends Command
{
    protected $signature = 'permissoes:atribuir-gerente';
    protected $description = 'Atribui o papel Gerente a todos os usuários não-admin que ainda não possuem papel';

    public function handle(): int
    {
        $gerente = Role::where('name', 'Gerente')->where('guard_name', 'web')->first();
        if (!$gerente) {
            $this->error('Papel "Gerente" não encontrado. Execute: php artisan db:seed --class=PermissionSeeder');
            return 1;
        }

        $users = User::where('is_admin', false)->get();
        $atribuidos = 0;

        foreach ($users as $user) {
            if ($user->roles->isEmpty()) {
                $user->assignRole($gerente);
                $this->line("  → {$user->email} atribuído ao papel Gerente");
                $atribuidos++;
            }
        }

        $this->info("{$atribuidos} usuário(s) atribuído(s) ao papel Gerente.");
        return 0;
    }
}
