<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pagamento;
use App\Models\Transacao;

class DiagnosticarTransacoes extends Command
{
    protected $signature = 'financeiro:diagnosticar';
    protected $description = 'Mostra todos os pagamentos e suas transações correspondentes';

    public function handle()
    {
        $this->info('DIAGNÓSTICO DE PAGAMENTOS E TRANSAÇÕES');
        $this->info('═══════════════════════════════════════════════════');
        $this->newLine();

        // Buscar TODOS os pagamentos
        $pagamentos = Pagamento::with('venda')->orderBy('id')->get();

        $this->info("Total de pagamentos no sistema: " . $pagamentos->count());
        $this->newLine();

        $comTransacao = 0;
        $semTransacao = 0;

        foreach ($pagamentos as $pagamento) {
            $venda = $pagamento->venda;
            
            if (!$venda) {
                $this->warn("⚠ Pagamento #{$pagamento->id} sem venda");
                continue;
            }

            // Buscar transação correspondente
            $transacao = Transacao::where('venda_id', $pagamento->venda_id)
                ->where('categoria', 'VENDA')
                ->first();

            if ($transacao) {
                $comTransacao++;
                $this->line("✓ Pagamento #{$pagamento->id} | Venda #{$venda->id} | Status: {$pagamento->status} | Transação: #{$transacao->id} ({$transacao->status}) | R$ {$pagamento->valor}");
            } else {
                $semTransacao++;
                $this->error("✗ Pagamento #{$pagamento->id} | Venda #{$venda->id} | Status: {$pagamento->status} | SEM TRANSAÇÃO | R$ {$pagamento->valor}");
            }
        }

        $this->newLine();
        $this->info("═══════════════════════════════════════════════════");
        $this->info("RESUMO:");
        $this->info("Pagamentos COM transação: {$comTransacao}");
        $this->error("Pagamentos SEM transação: {$semTransacao}");
        $this->newLine();

        if ($semTransacao > 0) {
            $this->warn("Execute: php artisan financeiro:sincronizar-pendentes");
        }

        return Command::SUCCESS;
    }
}
