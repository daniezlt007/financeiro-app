<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-8">
      <div class="text-center flex-1">
        <h1 class="text-3xl font-bold text-gray-900">Serviços</h1>
        <p class="mt-2 text-gray-600">Gerencie os serviços oferecidos</p>
      </div>
      <a href="/servicos/create" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
        + Novo Serviço
      </a>
    </div>
    
    <Card title="Lista de Serviços" variant="elevated">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="text-left p-3 border-b font-medium text-gray-900">Tipo de Serviço</th>
              <th class="text-right p-3 border-b font-medium text-gray-900">Preço Base</th>
              <th class="text-center p-3 border-b font-medium text-gray-900">Comissão %</th>
              <th class="text-center p-3 border-b font-medium text-gray-900">Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="i in data.data" :key="i.id" class="border-b last:border-b-0 hover:bg-gray-50">
              <td class="p-3 font-medium">{{ i.tipo_servico }}</td>
              <td class="p-3 text-right text-gray-600">R$ {{ Number(i.preco_base || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2}) }}</td>
              <td class="p-3 text-center text-gray-600">{{ Number(i.comissao_percentual || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2}) }}%</td>
              <td class="p-3 text-center">
                <div class="flex justify-center gap-3">
                  <a :href="`/servicos/${i.id}`" class="text-indigo-600 hover:text-indigo-800 font-medium">Visualizar</a>
                  <a :href="`/servicos/${i.id}/edit`" class="text-green-600 hover:text-green-800 font-medium">Editar</a>
                  <button @click="excluir(i.id)" class="text-red-600 hover:text-red-800 font-medium">Excluir</button>
                </div>
              </td>
            </tr>
            <tr v-if="!data.data || data.data.length === 0">
              <td colspan="4" class="p-4 text-center text-gray-500">Nenhum serviço encontrado</td>
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

function excluir(id) {
  if (confirm('Tem certeza que deseja excluir este serviço?')) {
    router.delete(`/servicos/${id}`)
  }
}
</script>
