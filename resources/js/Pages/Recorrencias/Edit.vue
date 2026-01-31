<template>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="text-center mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Editar Recorrência</h1>
      <p class="mt-2 text-gray-600">Atualize as informações da transação recorrente</p>
    </div>

    <Card title="Informações da Recorrência" variant="elevated">
      <form id="recorrencia-form" :action="`/recorrencias/${item.id}`" method="post" class="space-y-6">
        <input type="hidden" name="_token" :value="csrf">
        <input type="hidden" name="_method" value="PUT">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo *</label>
            <select class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" name="tipo" v-model="item.tipo" required>
              <option value="">Selecione o tipo</option>
              <option value="ENTRADA">Entrada</option>
              <option value="SAIDA">Saída</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Periodicidade *</label>
            <select class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" name="periodicidade" v-model="item.periodicidade" required>
              <option value="">Selecione a periodicidade</option>
              <option value="DIARIA">Diária</option>
              <option value="SEMANAL">Semanal</option>
              <option value="MENSAL">Mensal</option>
              <option value="ANUAL">Anual</option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Valor *</label>
            <input type="number" step="0.01" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" name="valor" v-model="item.valor" placeholder="0.00" required />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Próxima Execução *</label>
            <input type="date" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" name="proxima_execucao" v-model="item.proxima_execucao" required />
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" name="ativo" v-model="item.ativo">
              <option value="1">Ativo</option>
              <option value="0">Inativo</option>
            </select>
          </div>
        </div>

      </form>
      
      <template #footer>
        <div class="flex justify-end gap-4">
          <a href="/recorrencias" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition-colors">
            Cancelar
          </a>
          <button type="submit" form="recorrencia-form" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
            Salvar Alterações
          </button>
        </div>
      </template>
    </Card>
  </div>
</template>
<script setup>
import Card from '@/Components/Card.vue'

defineProps({ item: Object })
const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
</script>
