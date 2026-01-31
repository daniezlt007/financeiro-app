<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-8">
      <div class="text-center flex-1">
        <h1 class="text-3xl font-bold text-gray-900">Clientes</h1>
        <p class="mt-2 text-gray-600">Gerencie seus clientes cadastrados</p>
      </div>
      <a href="/clientes/create" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
        + Novo Cliente
      </a>
    </div>
    
    <Card title="Lista de Clientes" variant="elevated">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="text-left p-3 border-b font-medium text-gray-900">Nome</th>
              <th class="text-left p-3 border-b font-medium text-gray-900">CPF/CNPJ</th>
              <th class="text-left p-3 border-b font-medium text-gray-900">Telefone</th>
              <th class="text-center p-3 border-b font-medium text-gray-900">Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="c in data.data" :key="c.id" class="border-b last:border-b-0 hover:bg-gray-50">
              <td class="p-3 font-medium">{{ c.nome_completo }}</td>
              <td class="p-3 text-gray-600">{{ c.cpf_cnpj || '-' }}</td>
              <td class="p-3 text-gray-600">{{ c.telefone || '-' }}</td>
              <td class="p-3 text-center">
                <div class="flex justify-center gap-3">
                  <a :href="`/clientes/${c.id}`" class="text-indigo-600 hover:text-indigo-800 font-medium">Visualizar</a>
                  <a :href="`/clientes/${c.id}/edit`" class="text-green-600 hover:text-green-800 font-medium">Editar</a>
                  <button @click="excluirCliente(c.id)" class="text-red-600 hover:text-red-800 font-medium">Excluir</button>
                </div>
              </td>
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

function excluirCliente(id) {
  if (confirm('Tem certeza que deseja excluir este cliente? Esta ação não pode ser desfeita.')) {
    router.delete(`/clientes/${id}`)
  }
}
</script>
