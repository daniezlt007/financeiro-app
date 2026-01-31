<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-8">
      <div class="text-center flex-1">
        <h1 class="text-3xl font-bold text-gray-900">Metas</h1>
        <p class="mt-2 text-gray-600">Gerencie as metas mensais</p>
      </div>
      <a href="/metas/create" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
        + Nova Meta
      </a>
    </div>
    
    <Card title="Lista de Metas" variant="elevated">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="text-left p-3 border-b font-medium text-gray-900">Mês</th>
              <th class="text-left p-3 border-b font-medium text-gray-900">Ano</th>
              <th class="text-right p-3 border-b font-medium text-gray-900">Meta Faturamento</th>
              <th class="text-right p-3 border-b font-medium text-gray-900">Meta Varejo</th>
              <th class="text-right p-3 border-b font-medium text-gray-900">Meta Atacado</th>
              <th class="text-center p-3 border-b font-medium text-gray-900">Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="i in data.data" :key="i.id" class="border-b last:border-b-0 hover:bg-gray-50">
              <td class="p-3 font-medium">{{ getMesNome(i.mes) }}</td>
              <td class="p-3 text-gray-600">{{ i.ano }}</td>
              <td class="p-3 text-right text-gray-600">R$ {{ Number(i.meta_faturamento || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2}) }}</td>
              <td class="p-3 text-right text-gray-600">R$ {{ Number(i.meta_producao_varejo || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2}) }}</td>
              <td class="p-3 text-right text-gray-600">R$ {{ Number(i.meta_producao_atacado || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2}) }}</td>
              <td class="p-3 text-center">
                <div class="flex justify-center gap-3">
                  <a :href="`/metas/${i.id}`" class="text-indigo-600 hover:text-indigo-800 font-medium">Visualizar</a>
                  <a :href="`/metas/${i.id}/edit`" class="text-green-600 hover:text-green-800 font-medium">Editar</a>
                  <button @click="excluir(i.id)" class="text-red-600 hover:text-red-800 font-medium">Excluir</button>
                </div>
              </td>
            </tr>
            <tr v-if="!data.data || data.data.length === 0">
              <td colspan="6" class="p-4 text-center text-gray-500">Nenhuma meta encontrada</td>
            </tr>
          </tbody>
        </table>
      </div>
    </Card>
  </div>
</template>
<script setup>
import { router } from '@inertiajs/vue3'
import Card from '@/Components/Card.vue'

defineProps({ data: Object })

const meses = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro']

function getMesNome(mes) {
  return meses[mes - 1] || mes
}

function excluir(id) {
  if (confirm('Tem certeza que deseja excluir esta meta?')) {
    router.delete(`/metas/${id}`)
  }
}
</script>
