<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pagamento;
use App\Models\Transacao;

class SincronizarTransacoesVendas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'financeiro:sincronizar-vendas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza transações financeiras a partir de pagamentos já marcados como PAGO';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando sincronização de transações...');

        // Buscar todos os pagamentos com status PAGO que ainda não têm transação
        $pagamentos = Pagamento::where('status', 'PAGO')
            ->with('venda')
            ->get();

        $criadas = 0;
        $ignoradas = 0;

        foreach ($pagamentos as $pagamento) {
            // Verifica se já existe uma transação para esta venda
            $transacaoExistente = Transacao::where('venda_id', $pagamento->venda_id)
                ->where('categoria', 'VENDA')
                ->first();

            if ($transacaoExistente) {
                $ignoradas++;
                continue;
            }

            $venda = $pagamento->venda;
            
            if (!$venda) {
                $this->warn("Pagamento #{$pagamento->id} sem venda associada. Ignorando...");
                continue;
            }

            try {
                Transacao::create([
                    'empresa_id' => $venda->empresa_id,
                    'tipo' => 'ENTRADA',
                    'categoria' => 'VENDA',
                    'data' => $pagamento->data,
                    'valor' => $pagamento->valor,
                    'descricao' => 'Venda #' . $venda->id . ' - ' . ($venda->cliente_nome_completo ?? 'Cliente não informado'),
                    'observacoes' => 'Transação gerada automaticamente pela sincronização',
                    'forma_pagamento' => $pagamento->forma_pagamento,
                    'venda_id' => $venda->id,
                    'user_id' => 1, // Admin padrão
                    'status' => 'PAGO', // Status pago
                ]);

                $criadas++;
                $this->info("✓ Transação criada para Venda #{$venda->id}");
            } catch (\Exception $e) {
                $this->error("✗ Erro ao criar transação para Venda #{$venda->id}: " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info("Sincronização concluída!");
        $this->info("Transações criadas: {$criadas}");
        $this->info("Transações já existentes: {$ignoradas}");

        return Command::SUCCESS;
    }
}
