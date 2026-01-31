<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pagamento;
use App\Models\Transacao;

class SincronizarTodasTransacoes extends Command
{
    protected $signature = 'financeiro:sincronizar-todas {--force : Recriar todas as transações}';
    protected $description = 'Sincroniza TODAS as transações de TODOS os pagamentos (PENDENTES e PAGOS)';

    public function handle()
    {
        $this->info('═══════════════════════════════════════════════════');
        $this->info('SINCRONIZAÇÃO COMPLETA DE TRANSAÇÕES');
        $this->info('═══════════════════════════════════════════════════');
        $this->newLine();

        $force = $this->option('force');
        
        if ($force) {
            $this->warn('⚠ MODO FORCE ATIVADO: Recriará TODAS as transações automáticas!');
            if (!$this->confirm('Deseja continuar?')) {
                return Command::FAILURE;
            }
            $this->newLine();
        }

        // Buscar TODOS os pagamentos
        $pagamentos = Pagamento::with('venda')->orderBy('id')->get();

        $criadas = 0;
        $ignoradas = 0;
        $erros = 0;
        $atualizadas = 0;

        $this->info("Total de pagamentos encontrados: " . $pagamentos->count());
        $this->newLine();

        foreach ($pagamentos as $pagamento) {
            $venda = $pagamento->venda;
            
            if (!$venda) {
                $this->warn("⚠ Pagamento #{$pagamento->id} sem venda associada. Ignorando...");
                $erros++;
                continue;
            }

            // Buscar transação existente
            $transacao = Transacao::where('venda_id', $pagamento->venda_id)
                ->where('categoria', 'VENDA')
                ->first();

            if ($force && $transacao) {
                // Modo force: atualiza a transação existente
                try {
                    $transacao->update([
                        'status' => $pagamento->status,
                        'valor' => $pagamento->valor,
                        'data' => $pagamento->data,
                        'forma_pagamento' => $pagamento->forma_pagamento,
                    ]);
                    $atualizadas++;
                    $this->info("↻ Transação #{$transacao->id} atualizada para Venda #{$venda->id} - Status: {$pagamento->status}");
                } catch (\Exception $e) {
                    $this->error("✗ Erro ao atualizar transação #{$transacao->id}: " . $e->getMessage());
                    $erros++;
                }
                continue;
            }

            if ($transacao) {
                // Já existe e não é force
                $this->line("- Pagamento #{$pagamento->id} (Venda #{$venda->id}) já tem transação #{$transacao->id}");
                $ignoradas++;
                continue;
            }

            // Criar nova transação
            try {
                $novaTransacao = Transacao::create([
                    'empresa_id' => $venda->empresa_id,
                    'tipo' => 'ENTRADA',
                    'categoria' => 'VENDA',
                    'data' => $pagamento->data,
                    'valor' => $pagamento->valor,
                    'descricao' => 'Venda #' . $venda->id . ' - ' . ($venda->cliente_nome_completo ?? 'Cliente não informado'),
                    'observacoes' => 'Transação gerada automaticamente pela sincronização',
                    'forma_pagamento' => $pagamento->forma_pagamento,
                    'venda_id' => $venda->id,
                    'user_id' => 1,
                    'status' => $pagamento->status, // PENDENTE ou PAGO
                ]);

                $criadas++;
                $statusIcon = $pagamento->status === 'PAGO' ? '✓' : '⏳';
                $this->info("{$statusIcon} Transação criada: Pagamento #{$pagamento->id} | Venda #{$venda->id} | Status: {$pagamento->status} | R$ {$pagamento->valor}");
            } catch (\Exception $e) {
                $this->error("✗ Erro ao criar transação para Pagamento #{$pagamento->id}: " . $e->getMessage());
                $this->error("  Detalhes: " . $e->getTraceAsString());
                $erros++;
            }
        }

        $this->newLine();
        $this->info("═══════════════════════════════════════════════════");
        $this->info("RESULTADO DA SINCRONIZAÇÃO:");
        $this->info("═══════════════════════════════════════════════════");
        $this->info("✓ Transações criadas: {$criadas}");
        if ($force && $atualizadas > 0) {
            $this->info("↻ Transações atualizadas: {$atualizadas}");
        }
        $this->info("- Transações já existentes: {$ignoradas}");
        if ($erros > 0) {
            $this->error("✗ Erros encontrados: {$erros}");
        }
        $this->newLine();

        return Command::SUCCESS;
    }
}
