<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class SetUserAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:admin {email} {--remove : Remove admin privileges}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set or remove admin privileges for a user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $remove = $this->option('remove');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("Usuário com email '{$email}' não encontrado.");
            return 1;
        }

        if ($remove) {
            $user->is_admin = false;
            $user->save();
            $this->info("Privilégios de admin removidos para '{$email}'.");
        } else {
            $user->is_admin = true;
            $user->save();
            $this->info("Usuário '{$email}' agora é administrador.");
        }

        return 0;
    }
}