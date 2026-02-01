<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Empresa;  
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
// cria/garante uma empresa
        $empresa = Empresa::first() ?: Empresa::create([
            'nome_fantasia' => 'Empresa Padrão',
            'razao_social'  => 'Empresa Padrão LTDA',
            'email'         => 'contato@empresa.com',
            'status'        => true,
        ]);

        // disponibiliza o ID para outros seeders
        config(['seed.empresa_id' => $empresa->id]);
        $this->call(ServicosSeeder::class);
        $this->call(UserSeeder::class);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
