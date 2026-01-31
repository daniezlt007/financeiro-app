<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-8">
      <div class="text-center flex-1">
        <h1 class="text-3xl font-bold text-gray-900">Pagamentos</h1>
        <p class="mt-2 text-gray-600">Gerencie todos os pagamentos do sistema</p>
      </div>
      <a href="/pagamentos/create" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
        + Novo Pagamento
      </a>
    </div>
    
    <Card title="Lista de Pagamentos" variant="elevated">
      <!-- Filtros -->
      <div class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-3">
        <select
          v-model="filtroServico"
          @change="aplicarFiltros"
          class="border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
        >
          <option value="">Todos os Serviços</option>
          <option v-for="servico in servicos" :key="servico.id" :value="servico.id">
            {{ servico.nome }}
          </option>
        </select>
        
        <select
          v-model="filtroStatus"
          @change="aplicarFiltros"
          class="border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
        >
          <option value="">Todos os Status</option>
          <option value="PAGO">Pago</option>
          <option value="PENDENTE">Pendente</option>
          <option value="CANCELADO">Cancelado</option>
        </select>
        
        <select
          v-model="filtroParceiro"
          @change="aplicarFiltros"
          class="border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
        >
          <option value="">Todos os Parceiros</option>
          <option v-for="parceiro in parceiros" :key="parceiro" :value="parceiro">
            {{ parceiro }}
          </option>
        </select>
        
        <div class="flex gap-2">
          <input
            v-model="search"
            @input="buscar"
            type="text"
            placeholder="Pesquisar..."
            class="flex-1 border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
          />
          <button v-if="temFiltros" @click="limparFiltros" class="px-3 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm whitespace-nowrap">
            Limpar
          </button>
        </div>
      </div>
      
      <!-- Link para relatório -->
      <div class="mb-4 flex justify-end">
        <a href="/relatorios/pagamentos" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm">
          📊 Ver Relatório Completo
        </a>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="text-left p-3 border-b font-medium text-gray-900">ID</th>
              <th class="text-left p-3 border-b font-medium text-gray-900">Cliente</th>
              <th class="text-left p-3 border-b font-medium text-gray-900">Placa</th>
              <th class="text-left p-3 border-b font-medium text-gray-900">Serviço</th>
              <th class="text-left p-3 border-b font-medium text-gray-900">Data</th>
              <th class="text-left p-3 border-b font-medium text-gray-900">Forma de Pagamento</th>
              <th class="text-right p-3 border-b font-medium text-gray-900">Valor</th>
              <th class="text-center p-3 border-b font-medium text-gray-900">Status</th>
              <th class="text-center p-3 border-b font-medium text-gray-900">Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="pagamento in data.data" :key="pagamento.id" class="border-b last:border-b-0 hover:bg-gray-50">
              <td class="p-3 font-medium">{{ pagamento.id }}</td>
              <td class="p-3 text-gray-600">{{ pagamento.venda?.cliente_nome_completo || '-' }}</td>
              <td class="p-3 text-gray-600">{{ pagamento.venda?.cliente_placa || '-' }}</td>
              <td class="p-3 text-gray-600">
                <div v-if="getServicos(pagamento.venda).length > 0" class="flex flex-col gap-1">
                  <span v-for="(servico, idx) in getServicos(pagamento.venda)" :key="idx" class="text-xs">
                    {{ servico }}
                    <span v-if="idx < getServicos(pagamento.venda).length - 1" class="text-gray-400">, </span>
                  </span>
                </div>
                <span v-else class="text-gray-400">-</span>
              </td>
              <td class="p-3 text-gray-600">{{ (pagamento.data || '').split('-').reverse().join('/') }}</td>
              <td class="p-3 text-gray-600">{{ pagamento.forma_pagamento }}</td>
              <td class="p-3 text-right font-semibold text-green-600">R$ {{ Number(pagamento.valor).toLocaleString('pt-BR', {minimumFractionDigits: 2}) }}</td>
              <td class="p-3 text-center">
                <span :class="[
                  'px-2 py-1 rounded-full text-xs font-medium',
                  pagamento.status === 'PAGO' ? 'bg-green-100 text-green-800' :
                  pagamento.status === 'PENDENTE' ? 'bg-yellow-100 text-yellow-800' :
                  'bg-red-100 text-red-800'
                ]">
                  {{ pagamento.status }}
                </span>
              </td>
              <td class="p-3 text-center">
                <div class="flex justify-center gap-3">
                  <a :href="`/pagamentos/${pagamento.id}`" class="text-indigo-600 hover:text-indigo-800 font-medium">Visualizar</a>
                  <a :href="`/pagamentos/${pagamento.id}/edit`" class="text-green-600 hover:text-green-800 font-medium">Editar</a>
                  <button @click="excluir(pagamento.id)" class="text-red-600 hover:text-red-800 font-medium">Excluir</button>
                </div>
              </td>
            </tr>
            <tr v-if="data.data.length === 0">
              <td colspan="9" class="p-4 text-center text-gray-500">Nenhum registro encontrado</td>
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
import Card from '@/Components/Card.vue'
import { ref, computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'

const props = defineProps({ 
  data: Object,
  filters: Object,
  servicos: Array,
  parceiros: Array
})

const search = ref(props.filters?.search || '')
const filtroServico = ref(props.filters?.servico_id || '')
const filtroStatus = ref(props.filters?.status || '')
const filtroParceiro = ref(props.filters?.parceiro || '')

const servicos = props.servicos || []
const parceiros = props.parceiros || []

let debounceTimeout = null

const temFiltros = computed(() => {
  return search.value || filtroServico.value || filtroStatus.value || filtroParceiro.value
})

function buscar() {
  if (debounceTimeout) {
    clearTimeout(debounceTimeout)
  }

  debounceTimeout = setTimeout(() => {
    aplicarFiltros()
  }, 500)
}

function aplicarFiltros() {
  const params = {}
  if (search.value) params.search = search.value
  if (filtroServico.value) params.servico_id = filtroServico.value
  if (filtroStatus.value) params.status = filtroStatus.value
  if (filtroParceiro.value) params.parceiro = filtroParceiro.value
  
  router.get('/pagamentos', params, { 
    preserveState: true, 
    preserveScroll: true 
  })
}

function limparFiltros() {
  search.value = ''
  filtroServico.value = ''
  filtroStatus.value = ''
  filtroParceiro.value = ''
  router.get('/pagamentos', {}, { 
    preserveState: true, 
    preserveScroll: true 
  })
}

function excluir(id) {
  if (confirm('Tem certeza que deseja excluir este pagamento?')) {
    router.delete(`/pagamentos/${id}`)
  }
}

function getServicos(venda) {
  if (!venda || !venda.itens || venda.itens.length === 0) {
    return []
  }
  
  // Retorna todos os serviços da venda
  const servicos = []
  venda.itens.forEach(item => {
    if (item.tipo_item === 'SERVICO' && item.servico) {
      servicos.push(item.servico.tipo_servico)
    }
  })
  
  return servicos
}
</script>
