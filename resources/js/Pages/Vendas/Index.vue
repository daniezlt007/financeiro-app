<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-8">
      <div class="text-center flex-1">
        <h1 class="text-3xl font-bold text-gray-900">Vendas</h1>
        <p class="mt-2 text-gray-600">Visualize todas as vendas realizadas</p>
      </div>
      <a v-if="can('vendas.criar')" href="/vendas/create" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
        + Nova Venda
      </a>
    </div>
    
    <Card title="Lista de Vendas" variant="elevated">
      <div class="mb-4 flex items-center justify-end">
        <form @submit.prevent="buscar" class="flex gap-2">
          <input
            v-model="search"
            type="text"
            placeholder="Pesquisar por qualquer campo..."
            class="w-full md:w-80 border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
            @input="buscar"
          />
          <button
            v-if="search"
            @click.prevent="limparBusca"
            type="button"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm"
          >
            Limpar
          </button>
        </form>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="text-left p-3 border-b font-medium text-gray-900">Data</th>
              <th class="text-left p-3 border-b font-medium text-gray-900">Cliente</th>
              <th class="text-left p-3 border-b font-medium text-gray-900">Placa</th>
              <th class="text-left p-3 border-b font-medium text-gray-900">Parceiro</th>
              <th class="text-left p-3 border-b font-medium text-gray-900">Vistoriador</th>
              <th class="text-right p-3 border-b font-medium text-gray-900">Valor Total</th>
              <th class="text-center p-3 border-b font-medium text-gray-900">Status Pagamento</th>
              <th class="text-center p-3 border-b font-medium text-gray-900">Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="v in (data?.data || [])" :key="v.id" class="border-b last:border-b-0 hover:bg-gray-50">
              <td class="p-3 font-medium">{{ (v.data || '').split('-').reverse().join('/') }}</td>
              <td class="p-3 text-gray-600">{{ v.cliente_nome_completo || '-' }}</td>
              <td class="p-3 text-gray-600">{{ v.cliente_placa || '-' }}</td>
              <td class="p-3 text-gray-600">{{ v.parceiro || '-' }}</td>
              <td class="p-3 text-gray-600">{{ v.funcionario?.nome_completo || '-' }}</td>
              <td class="p-3 text-right font-semibold text-green-600">R$ {{ Number(v.valor_total).toLocaleString('pt-BR',{minimumFractionDigits:2}) }}</td>
              <td class="p-3 text-center">
                <span :class="[
                  'px-2 py-1 rounded-full text-xs font-medium',
                  v.pagamentos && v.pagamentos.length > 0 && v.pagamentos[0].status === 'PAGO' ? 'bg-green-100 text-green-800' :
                  v.pagamentos && v.pagamentos.length > 0 && v.pagamentos[0].status === 'PENDENTE' ? 'bg-yellow-100 text-yellow-800' :
                  v.pagamentos && v.pagamentos.length > 0 && v.pagamentos[0].status === 'CANCELADO' ? 'bg-red-100 text-red-800' :
                  'bg-gray-100 text-gray-800'
                ]">
                  {{ v.pagamentos && v.pagamentos.length > 0 ? v.pagamentos[0].status : 'SEM PAGAMENTO' }}
                </span>
              </td>
              <td class="p-3 text-center">
                <div class="flex justify-center gap-3">
                  <a v-if="can('vendas.visualizar')" :href="`/vendas/${v.id}`" class="text-indigo-600 hover:text-indigo-800 font-medium">Visualizar</a>
                  <a v-if="can('vendas.editar')" :href="`/vendas/${v.id}/edit`" class="text-green-600 hover:text-green-800 font-medium">Editar</a>
                  <a v-if="can('vendas.visualizar')" :href="`/vendas/${v.id}/recibo`" target="_blank" class="text-purple-600 hover:text-purple-800 font-medium">Recibo</a>
                  <button v-if="can('vendas.excluir')" @click.prevent="deleteVenda(v.id)" class="text-red-600 hover:text-red-800 font-medium">Excluir</button>
                </div>
              </td>
            </tr>
            <tr v-if="!data?.data || data.data.length === 0">
              <td colspan="8" class="p-4 text-center text-gray-500">Nenhum registro encontrado</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginação -->
      <div v-if="data?.links && data.links.length > 3" class="mt-6 border-t border-gray-200 pt-4">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Mostrando {{ data?.from || 0 }} até {{ data?.to || 0 }} de {{ data?.total || 0 }} resultados
          </div>
          <div class="flex space-x-1">
            <template v-for="link in data.links" :key="link.label">
              <Link
                v-if="link.url"
                :href="link.url"
                v-html="link.label"
                :class="[
                  'px-3 py-1 rounded text-sm font-medium',
                  link.active 
                    ? 'bg-indigo-600 text-white' 
                    : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                ]"
              />
              <span
                v-else
                v-html="link.label"
                class="px-3 py-1 rounded text-sm opacity-50 cursor-not-allowed bg-gray-100 text-gray-700"
              />
            </template>
          </div>
        </div>
      </div>
    </Card>
  </div>
</template>
<script setup>
import { router, Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import Card from '@/Components/Card.vue'
import { usePermissions } from '@/composables/usePermissions'

const { can } = usePermissions()
const props = defineProps({ data: Object })

// Inicializar busca da URL (query string)
const search = ref(new URLSearchParams(window.location.search).get('search') || '')
let timeoutId = null

// Busca no servidor - usar dados diretos do backend
const filtered = computed(() => {
  return props?.data?.data || []
})

function buscar() {
  // Limpar timeout anterior para evitar múltiplas requisições
  if (timeoutId) {
    clearTimeout(timeoutId)
  }
  
  // Debounce: aguardar 500ms após parar de digitar
  timeoutId = setTimeout(() => {
    router.get('/vendas', {
      search: search.value.trim() || null
    }, {
      preserveState: true,
      preserveScroll: true,
      only: ['data']
    })
  }, 500)
}

function limparBusca() {
  search.value = ''
  router.get('/vendas', {}, {
    preserveState: true
  })
}

function deleteVenda(id) {
  if (confirm('Tem certeza que deseja excluir esta venda? Esta ação também excluirá os pagamentos, transações financeiras e itens vinculados.')) {
    router.delete(`/vendas/${id}`)
  }
}
</script>
