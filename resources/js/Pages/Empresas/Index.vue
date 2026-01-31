<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-8">
      <div class="text-center flex-1">
        <h1 class="text-3xl font-bold text-gray-900">Empresas</h1>
        <p class="mt-2 text-gray-600">Gerencie as empresas do sistema</p>
      </div>
      <Link :href="route('empresas.create')" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
        + Nova Empresa
      </Link>
    </div>
    
    <Card title="Lista de Empresas" variant="elevated">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="text-left p-3 border-b font-medium text-gray-900">Nome Fantasia</th>
              <th class="text-left p-3 border-b font-medium text-gray-900">Razão Social</th>
              <th class="text-left p-3 border-b font-medium text-gray-900">CNPJ</th>
              <th class="text-center p-3 border-b font-medium text-gray-900">Status</th>
              <th class="text-center p-3 border-b font-medium text-gray-900">Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="e in empresas.data" :key="e.id" class="border-b last:border-b-0 hover:bg-gray-50">
              <td class="p-3 font-medium">{{ e.nome_fantasia }}</td>
              <td class="p-3 text-gray-600">{{ e.razao_social || '-' }}</td>
              <td class="p-3 text-gray-600">{{ e.cnpj || '-' }}</td>
              <td class="p-3 text-center">
                <span :class="[
                  'px-2 py-1 rounded-full text-xs font-medium',
                  e.status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                ]">
                  {{ e.status ? 'Ativa' : 'Inativa' }}
                </span>
              </td>
              <td class="p-3 text-center">
                <div class="flex justify-center gap-3">
                  <Link :href="route('empresas.show', e.id)" class="text-indigo-600 hover:text-indigo-800 font-medium">Visualizar</Link>
                  <Link :href="route('empresas.edit', e.id)" class="text-green-600 hover:text-green-800 font-medium">Editar</Link>
                  <button @click="excluir(e.id)" class="text-red-600 hover:text-red-800 font-medium">Excluir</button>
                </div>
              </td>
            </tr>
            <tr v-if="!empresas.data || empresas.data.length === 0">
              <td colspan="5" class="p-4 text-center text-gray-500">Nenhuma empresa encontrada</td>
            </tr>
          </tbody>
        </table>
      </div>
    </Card>
  </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import Card from '@/Components/Card.vue'

const props = defineProps({ empresas: Object, filters: Object })

function excluir(id) {
  if (confirm('Tem certeza que deseja excluir esta empresa?')) {
    router.delete(route('empresas.destroy', id))
  }
}
</script>
