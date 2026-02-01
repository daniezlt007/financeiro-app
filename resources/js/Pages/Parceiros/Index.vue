<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-8">
      <div class="text-center flex-1">
        <h1 class="text-3xl font-bold text-gray-900">Parceiros</h1>
        <p class="mt-2 text-gray-600">Gerencie os parceiros cadastrados</p>
      </div>
      <Link :href="route('parceiros.create')" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
        + Novo Parceiro
      </Link>
    </div>
    
    <Card title="Lista de Parceiros" variant="elevated">
      <!-- Busca -->
      <div class="mb-4 flex gap-2">
        <input
          v-model="search"
          @input="buscar"
          type="text"
          placeholder="Pesquisar por nome..."
          class="flex-1 max-w-md border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
        />
        <button v-if="search" @click="limparBusca" class="px-3 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm whitespace-nowrap">
          Limpar
        </button>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="text-left p-3 border-b font-medium text-gray-900">Nome</th>
              <th class="text-center p-3 border-b font-medium text-gray-900">Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in data.data" :key="p.id" class="border-b last:border-b-0 hover:bg-gray-50">
              <td class="p-3 font-medium">{{ p.nome_parceiro }}</td>
              <td class="p-3 text-center">
                <div class="flex justify-center gap-3">
                  <Link :href="route('parceiros.edit', p.id)" class="text-green-600 hover:text-green-800 font-medium">Editar</Link>
                  <button @click="excluir(p.id)" class="text-red-600 hover:text-red-800 font-medium">Excluir</button>
                </div>
              </td>
            </tr>
            <tr v-if="!data.data || data.data.length === 0">
              <td colspan="2" class="p-4 text-center text-gray-500">Nenhum parceiro encontrado</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginação -->
      <div v-if="data.last_page > 1" class="mt-6 flex justify-center">
        <nav class="flex gap-2">
          <Link 
            v-for="page in data.links" 
            :key="page.label"
            :href="page.url || '#'"
            :class="[
              'px-3 py-2 text-sm rounded border',
              page.active ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50',
              !page.url && 'opacity-50 cursor-not-allowed'
            ]"
            v-html="page.label"
            :disabled="!page.url"
          />
        </nav>
      </div>
    </Card>
  </div>
</template>
<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import Card from '@/Components/Card.vue'

const props = defineProps({ data: Object, filters: Object })

const search = ref(props.filters?.search || '')

let debounceTimeout = null

function buscar() {
  if (debounceTimeout) clearTimeout(debounceTimeout)
  debounceTimeout = setTimeout(() => {
    const params = search.value ? { search: search.value } : {}
    router.get(route('parceiros.index'), params, { preserveState: true, preserveScroll: true })
  }, 500)
}

function limparBusca() {
  search.value = ''
  router.get(route('parceiros.index'), {}, { preserveState: true, preserveScroll: true })
}

function excluir(id) {
  if (confirm('Tem certeza que deseja excluir este parceiro?')) {
    router.delete(route('parceiros.destroy', id))
  }
}
</script>
