<template>
  <Head title="Baixa em Lote" />
  
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Baixa em Lote</h1>
      <p class="mt-2 text-gray-600">Selecione os pagamentos pendentes para dar baixa</p>
    </div>
    
    <Card title="Pagamentos Pendentes" variant="elevated">
      <!-- Filtros -->
      <div class="mb-4 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <!-- Filtro de Data Início -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Data Início</label>
            <input
              v-model="filtros.data_inicio"
              @change="aplicarFiltros"
              type="date"
              class="w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-dekra-500"
            />
          </div>
          
          <!-- Filtro de Data Fim -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Data Fim</label>
            <input
              v-model="filtros.data_fim"
              @change="aplicarFiltros"
              type="date"
              class="w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-dekra-500"
            />
          </div>
          
          <!-- Filtro de Parceiro -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Parceiro</label>
            <select
              v-model="filtros.parceiro"
              @change="aplicarFiltros"
              class="w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-dekra-500"
            >
              <option value="">Todos</option>
              <option v-for="parceiro in parceiros" :key="parceiro" :value="parceiro">
                {{ parceiro }}
              </option>
            </select>
          </div>
          
          <!-- Botão Limpar Filtros -->
          <div class="flex items-end">
            <button
              @click="limparFiltros"
              class="w-full px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm font-medium"
            >
              Limpar Filtros
            </button>
          </div>
        </div>
      </div>

      <!-- Botão de Baixa e Busca -->
      <div class="mb-4 flex items-center justify-between gap-4 flex-wrap">
        <button
          @click="baixarSelecionados"
          :disabled="selecionados.length === 0 || processando"
          :class="[
            'px-6 py-2 rounded-md font-medium transition-colors',
            selecionados.length === 0 || processando
              ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
              : 'bg-dekra-800 text-white hover:bg-dekra-700'
          ]"
        >
          <span v-if="processando">Processando...</span>
          <span v-else>Baixar Selecionados ({{ selecionados.length }})</span>
        </button>
        
        <div class="flex items-center gap-2 flex-1 justify-end">
          <input
            v-model="search"
            @input="buscar"
            type="text"
            placeholder="Pesquisar..."
            class="w-full md:w-80 border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-dekra-500"
          />
          <button 
            v-if="search" 
            @click="limparBusca" 
            class="px-3 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm"
          >
            Limpar
          </button>
        </div>
      </div>

      <!-- Grid de Pagamentos -->
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="text-left p-3 border-b font-medium text-gray-900 w-12">
                <input
                  type="checkbox"
                  :checked="todosSelecionados"
                  @change="selecionarTodos"
                  class="rounded border-gray-300 text-dekra-600 focus:ring-dekra-500"
                />
              </th>
              <th class="text-left p-3 border-b font-medium text-gray-900">ID</th>
              <th class="text-left p-3 border-b font-medium text-gray-900">Cliente</th>
              <th class="text-left p-3 border-b font-medium text-gray-900">Placa</th>
              <th class="text-left p-3 border-b font-medium text-gray-900">Parceiro</th>
              <th class="text-left p-3 border-b font-medium text-gray-900">Data</th>
              <th class="text-left p-3 border-b font-medium text-gray-900">Forma de Pagamento</th>
              <th class="text-right p-3 border-b font-medium text-gray-900">Valor</th>
              <th class="text-center p-3 border-b font-medium text-gray-900">Status</th>
              <th class="text-center p-3 border-b font-medium text-gray-900">Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="pagamento in data.data" 
              :key="pagamento.id" 
              class="border-b last:border-b-0 hover:bg-gray-50"
            >
              <td class="p-3">
                <input
                  type="checkbox"
                  :value="pagamento.id"
                  v-model="selecionados"
                  class="rounded border-gray-300 text-dekra-600 focus:ring-dekra-500"
                />
              </td>
              <td class="p-3 font-medium">{{ pagamento.id }}</td>
              <td class="p-3 text-gray-600">{{ pagamento.venda?.cliente_nome_completo || '-' }}</td>
              <td class="p-3 text-gray-600">{{ pagamento.venda?.cliente_placa || '-' }}</td>
              <td class="p-3 text-gray-600">{{ pagamento.venda?.parceiro || '-' }}</td>
              <td class="p-3 text-gray-600">{{ formatarData(pagamento.data) }}</td>
              <td class="p-3 text-gray-600">{{ pagamento.forma_pagamento }}</td>
              <td class="p-3 text-right font-semibold text-green-600">
                {{ formatarMoeda(pagamento.valor) }}
              </td>
              <td class="p-3 text-center">
                <span class="px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                  {{ pagamento.status }}
                </span>
              </td>
              <td class="p-3 text-center">
                <Link 
                  :href="route('pagamentos.show', pagamento.id)" 
                  class="text-dekra-600 hover:text-dekra-800 font-medium"
                >
                  Visualizar
                </Link>
              </td>
            </tr>
            <tr v-if="data.data.length === 0">
              <td colspan="10" class="p-4 text-center text-gray-500">
                Nenhum pagamento pendente encontrado
              </td>
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
              page.active ? 'bg-dekra-600 text-white border-dekra-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50',
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
import { ref, computed } from 'vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import Card from '@/Components/Card.vue'

const props = defineProps({
  data: Object,
  filters: Object,
  parceiros: Array
})

const selecionados = ref([])
const search = ref(props.filters?.search || '')
const processando = ref(false)
const filtros = ref({
  data_inicio: props.filters?.data_inicio || '',
  data_fim: props.filters?.data_fim || '',
  parceiro: props.filters?.parceiro || ''
})

let debounceTimeout = null

const todosSelecionados = computed(() => {
  return props.data.data.length > 0 && selecionados.value.length === props.data.data.length
})

function selecionarTodos(event) {
  if (event.target.checked) {
    selecionados.value = props.data.data.map(p => p.id)
  } else {
    selecionados.value = []
  }
}

function aplicarFiltros() {
  const params = {
    search: search.value,
    data_inicio: filtros.value.data_inicio || null,
    data_fim: filtros.value.data_fim || null,
    parceiro: filtros.value.parceiro || null
  }
  
  // Remove valores vazios/null
  Object.keys(params).forEach(key => {
    if (!params[key]) {
      delete params[key]
    }
  })
  
  router.get(route('financeiro.baixa-lote'), params, { 
    preserveState: true, 
    preserveScroll: true 
  })
  // Limpar seleções ao filtrar
  selecionados.value = []
}

function buscar() {
  if (debounceTimeout) {
    clearTimeout(debounceTimeout)
  }

  debounceTimeout = setTimeout(() => {
    aplicarFiltros()
  }, 500)
}

function limparBusca() {
  search.value = ''
  aplicarFiltros()
}

function limparFiltros() {
  search.value = ''
  filtros.value = {
    data_inicio: '',
    data_fim: '',
    parceiro: ''
  }
  router.get(route('financeiro.baixa-lote'), {}, { 
    preserveState: true, 
    preserveScroll: true 
  })
  selecionados.value = []
}

function baixarSelecionados() {
  if (selecionados.value.length === 0) {
    return
  }

  if (!confirm(`Deseja realmente dar baixa em ${selecionados.value.length} pagamento(s) selecionado(s)?`)) {
    return
  }

  processando.value = true

  const form = useForm({
    pagamento_ids: selecionados.value
  })

  form.post(route('financeiro.baixa-lote.processar'), {
    preserveScroll: true,
    onSuccess: () => {
      selecionados.value = []
      processando.value = false
    },
    onError: () => {
      processando.value = false
    },
    onFinish: () => {
      processando.value = false
    }
  })
}

function formatarMoeda(valor) {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(valor || 0)
}

function formatarData(data) {
  if (!data) return '-'
  const [year, month, day] = data.split('-')
  return `${day}/${month}/${year}`
}
</script>

