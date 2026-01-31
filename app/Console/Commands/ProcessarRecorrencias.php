<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\{Recorrencia,Venda};

class ProcessarRecorrencias extends Command
{
    protected $signature = 'recorrencias:processar';
    protected $description = 'Gera lançamentos a partir das recorrências ativas';

    public function handle(): int
    {
        $hoje = now()->toDateString();
        $recs = Recorrencia::where('ativo',true)->whereDate('proxima_execucao','<=',$hoje)->get();
        foreach ($recs as $r) {
            // Exemplo simplificado: apenas registra uma venda RECEITA na data atual
            if ($r->tipo === 'RECEITA') {
                Venda::create([
                    'empresa_id' => $r->empresa_id,
                    'data' => $hoje,
                    'cliente_nome_completo' => 'Cliente Recorrência',
                    'consumidor_tipo' => 'CONSUMIDOR FINAL',
                    'valor_total' => $r->valor,
                    'forma_pagamento' => 'OUTRO'
                ]);
            }
            // Próxima execução
            $prox = match($r->periodicidade) {
                'DIARIO' => now()->addDay(),
                'SEMANAL' => now()->addWeek(),
                default => now()->addMonth(),
            };
            $r->update(['proxima_execucao' => $prox->toDateString()]);
        }
        $this->info("Recorrências processadas: {$recs->count()}");
        return self::SUCCESS;
    }
}
