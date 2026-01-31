<template>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Editar Pagamento #{{ pagamento.id }}</h1>
      <p class="text-gray-600 mt-1">Atualize as informações do pagamento</p>
    </div>

    <Card title="Informações do Pagamento" variant="elevated">
      <form @submit.prevent="submit">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Venda</label>
            <select v-model="form.venda_id" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
              <option v-for="venda in vendas" :key="venda.id" :value="venda.id">
                #{{ venda.id }} - {{ venda.cliente_nome_completo }} - R$ {{ Number(venda.valor_total).toLocaleString('pt-BR',{minimumFractionDigits:2}) }}
              </option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Data do Pagamento</label>
            <input v-model="form.data_pagamento" type="date" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Forma de Pagamento</label>
            <select v-model="form.forma_pagamento" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
              <option value="DINHEIRO">DINHEIRO</option>
              <option value="PIX">PIX</option>
              <option value="CREDITO">CRÉDITO</option>
              <option value="DEBITO">DÉBITO</option>
              <option value="BOLETO">BOLETO</option>
              <option value="TRANSFERENCIA">TRANSFERENCIA</option>
              <option value="OUTRO">OUTRO</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Valor</label>
            <input v-model="form.valor" type="number" step="0.01" min="0" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select v-model="form.status" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
              <option value="PAGO">PAGO</option>
              <option value="PENDENTE">PENDENTE</option>
              <option value="CANCELADO">CANCELADO</option>
            </select>
          </div>
        </div>
      </form>
      
      <template #footer>
        <div class="flex justify-end space-x-3">
          <a href="/pagamentos" class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Cancelar
          </a>
          <button @click="submit" :disabled="form.processing" class="px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
            {{ form.processing ? 'Salvando...' : 'Salvar Alterações' }}
          </button>
        </div>
      </template>
    </Card>
  </div>
</template>

<script setup>
import Card from '@/Components/Card.vue'
import { useNotificationsStore } from '@/stores/notifications'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({ 
  pagamento: Object,
  vendas: Array 
})

const notificationsStore = useNotificationsStore()

// Formulário usando Inertia.js para melhor integração
const form = useForm({
  venda_id: props.pagamento.venda_id,
  data_pagamento: props.pagamento.data,
  forma_pagamento: props.pagamento.forma_pagamento,
  valor: props.pagamento.valor,
  status: props.pagamento.status
})

const submit = () => {
  form.put(`/pagamentos/${props.pagamento.id}`, {
    onSuccess: () => {
      notificationsStore.success('Sucesso!', 'Pagamento atualizado com sucesso!')
    },
    onError: (errors) => {
      notificationsStore.error('Erro!', 'Verifique os dados e tente novamente')
      console.log('Erros de validação:', errors)
    }
  })
}
</script>
