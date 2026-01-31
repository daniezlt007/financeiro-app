<template>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="text-center mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Novo Pagamento</h1>
      <p class="mt-2 text-gray-600">Registre um novo pagamento no sistema</p>
    </div>

    <Card title="Informações do Pagamento" variant="elevated">
      <form id="pagamento-form" method="post" action="/pagamentos" class="space-y-6">
        <input type="hidden" name="_token" :value="csrf">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Venda *</label>
            <select name="venda_id" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
              <option value="" disabled>Selecione uma venda…</option>
              <option v-for="venda in vendas" :key="venda.id" :value="venda.id">
                {{ venda.cliente_nome_completo }} - R$ {{ Number(venda.valor_total).toLocaleString('pt-BR', {minimumFractionDigits: 2}) }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Data *</label>
            <input name="data" type="date" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required />
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Forma de Pagamento *</label>
            <select name="forma_pagamento" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
              <option value="" disabled>Selecione a forma de pagamento</option>
              <option value="DINHEIRO">Dinheiro</option>
              <option value="PIX">PIX</option>
              <option value="CREDITO">Crédito</option>
              <option value="DEBITO">Débito</option>
              <option value="BOLETO">Boleto</option>
              <option value="TRANSFERENCIA">Transferência</option>
              <option value="OUTRO">Outro</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Valor *</label>
            <input name="valor" type="number" step="0.01" min="0" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="0,00" required />
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
            <select name="status" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
              <option value="" disabled>Selecione o status</option>
              <option value="PAGO">Pago</option>
              <option value="PENDENTE">Pendente</option>
              <option value="CANCELADO">Cancelado</option>
            </select>
          </div>
          <div></div>
        </div>
      </form>
      
      <template #footer>
        <div class="flex justify-end gap-4">
          <button type="submit" form="pagamento-form" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
            Salvar Pagamento
          </button>
        </div>
      </template>
    </Card>
  </div>
</template>
<script setup>
import Card from '@/Components/Card.vue'

defineProps({
  vendas: {
    type: Array,
    default: () => []
  }
})

const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
</script>

