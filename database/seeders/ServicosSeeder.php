<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Servico;

class ServicosSeeder extends Seeder
{
    public function run(): void
    {
        $empresaId = config('seed.empresa_id'); // não chutar 1

        $servicos = [
            'VISTORIA PRÉVIA','ECV','ECV - AUTO DE INFRAÇÃO','DEKRA CAUTELAR',
            'DEKRA CAUTELAR + PINTURA','DEKRA GARANTIDO','VISTORIA AVULSA',
            'DEKRA CAUTELAR + ECV','DEKRA CAUTELAR + PINTURA + ECV',
            'DEKRA GARANTIDO + ECV','LAUDO DE MOTO',
        ];

        foreach ($servicos as $nome) {
            Servico::firstOrCreate(
                ['empresa_id' => $empresaId, 'tipo_servico' => $nome],
                ['preco_base' => 0, 'comissao_percentual' => 0]
            );
        }
    }
}
