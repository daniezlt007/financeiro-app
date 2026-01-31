<?php

namespace App\Observers;

use App\Models\Pagamento;
use App\Models\Transacao;

class PagamentoObserver
{
    /**
     * Handle the Pagamento "created" event.
     * Quando um pagamento é criado, cria uma transação com o mesmo status
     */
    public function created(Pagamento $pagamento): void
    {
        $venda = $pagamento->venda;
        
        if ($venda) {
            Transacao::create([
                'empresa_id' => $venda->empresa_id,
                'tipo' => 'ENTRADA',
                'categoria' => 'VENDA',
                'data' => $pagamento->data,
                'valor' => $pagamento->valor,
                'descricao' => 'Venda #' . $venda->id . ' - ' . ($venda->cliente_nome_completo ?? 'Cliente não informado'),
                'observacoes' => 'Transação gerada automaticamente pela venda',
                'forma_pagamento' => $pagamento->forma_pagamento,
                'venda_id' => $venda->id,
                'user_id' => auth()->id() ?? 1,
                'status' => $pagamento->status, // PENDENTE ou PAGO
            ]);
        }
    }

    /**
     * Handle the Pagamento "updated" event.
     * Quando status do pagamento muda, atualiza status da transação
     */
    public function updated(Pagamento $pagamento): void
    {
        // Verifica se o status mudou
        if ($pagamento->isDirty('status')) {
            // Atualiza o status da transação vinculada
            Transacao::where('venda_id', $pagamento->venda_id)
                ->where('categoria', 'VENDA')
                ->update(['status' => $pagamento->status]);
        }
    }

    /**
     * Handle the Pagamento "deleted" event.
     * Quando um pagamento é excluído, remove a transação associada
     */
    public function deleted(Pagamento $pagamento): void
    {
        // Remove a transação vinculada, independente do status
        Transacao::where('venda_id', $pagamento->venda_id)
            ->where('categoria', 'VENDA')
            ->delete();
    }
}
