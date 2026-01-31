<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pagamento;
use App\Models\Transacao;

class SincronizarTransacoesPendentes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'financeiro:sincronizar-pendentes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza transações financeiras a partir de pagamentos PENDENTES que ainda não têm transação';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando sincronização de transações pendentes...');

        // Buscar todos os pagamentos com status PENDENTE
        $pagamentos = Pagamento::where('status', 'PENDENTE')
            ->with('venda')
            ->get();

        $criadas = 0;
        $ignoradas = 0;
        $erros = 0;

        $this->info("Total de pagamentos PENDENTES encontrados: " . $pagamentos->count());
        $this->newLine();

        foreach ($pagamentos as $pagamento) {
            $venda = $pagamento->venda;
            
            if (!$venda) {
                $this->warn("⚠ Pagamento #{$pagamento->id} sem venda associada. Ignorando...");
                $erros++;
                continue;
            }

            // Verifica se já existe uma transação PENDENTE para esta venda
            // Usa venda_id E status para permitir múltiplas transações da mesma venda (caso tenha sido paga e depois recriada)
            $transacaoExistente = Transacao::where('venda_id', $pagamento->venda_id)
                ->where('categoria', 'VENDA')
                ->where('status', 'PENDENTE')
                ->first();

            if ($transacaoExistente) {
                $this->line("- Transação PENDENTE já existe para Venda #{$venda->id}. Ignorando.");
                $ignoradas++;
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
                    'observacoes' => 'Transação gerada automaticamente pela sincronização de pendentes',
                    'forma_pagamento' => $pagamento->forma_pagamento,
                    'venda_id' => $venda->id,
                    'user_id' => 1, // Admin padrão
                    'status' => 'PENDENTE', // Status pendente
                ]);

                $criadas++;
                $this->info("✓ Transação PENDENTE criada para Venda #{$venda->id} - Cliente: {$venda->cliente_nome_completo} - Valor: R$ {$pagamento->valor}");
            } catch (\Exception $e) {
                $this->error("✗ Erro ao criar transação para Venda #{$venda->id}: " . $e->getMessage());
                $erros++;
            }
        }

        $this->newLine();
        $this->info("═══════════════════════════════════════");
        $this->info("Sincronização de pendentes concluída!");
        $this->info("═══════════════════════════════════════");
        $this->info("✓ Transações PENDENTES criadas: {$criadas}");
        $this->info("- Transações já existentes: {$ignoradas}");
        if ($erros > 0) {
            $this->warn("⚠ Erros encontrados: {$erros}");
        }

        return Command::SUCCESS;
    }
}
