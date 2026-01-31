<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-8">
      <div class="text-center flex-1">
        <h1 class="text-3xl font-bold text-gray-900">Transações Financeiras</h1>
        <p class="mt-2 text-gray-600">Gerencie todas as entradas e saídas</p>
      </div>
      <div class="flex gap-3">
        <Link :href="route('financeiro.dashboard')" class="px-6 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors">
          Dashboard
        </Link>
        <Link :href="route('financeiro.create')" class="px-6 py-2 bg-dekra-800 text-white rounded-md hover:bg-dekra-700 transition-colors">
          + Nova Transação
        </Link>
      </div>
    </div>

    <!-- Pesquisa -->
    <Card variant="elevated" class="mb-6">
      <div class="flex gap-2">
        <input
          v-model="filtros.search"
          @input="buscar"
          type="text"
          placeholder="Pesquisar por qualquer campo..."
          class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-dekra-500 focus:ring-dekra-500"
        />
        <button
          v-if="filtros.search"
          @click="limparBusca"
          type="button"
          class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm"
        >
          Limpar Busca
        </button>
      </div>
    </Card>

    <!-- Filtros -->
    <Card title="Filtros" variant="elevated" class="mb-6">
      <form @submit.prevent="filtrar" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
          <select 
            v-model="filtros.tipo" 
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-dekra-500 focus:ring-dekra-500"
          >
            <option value="">Todos</option>
            <option value="ENTRADA">Entrada</option>
            <option value="SAIDA">Saída</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
          <select 
            v-model="filtros.categoria" 
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-dekra-500 focus:ring-dekra-500"
          >
            <option value="">Todas</option>
            <optgroup label="Entradas">
              <option v-for="(label, key) in categorias_entrada" :key="key" :value="key">{{ label }}</option>
            </optgroup>
            <optgroup label="Saídas">
              <option v-for="(label, key) in categorias_saida" :key="key" :value="key">{{ label }}</option>
            </optgroup>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select 
            v-model="filtros.status" 
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-dekra-500 focus:ring-dekra-500"
          >
            <option value="">Todos</option>
            <option value="PAGO">Pago</option>
            <option value="PENDENTE">Pendente</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Forma Pagamento</label>
          <select 
            v-model="filtros.forma_pagamento" 
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-dekra-500 focus:ring-dekra-500"
          >
            <option value="">Todas</option>
            <option v-for="(label, key) in formas_pagamento" :key="key" :value="key">{{ label }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Data Início</label>
          <input 
            v-model="filtros.data_inicio" 
            type="date" 
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-dekra-500 focus:ring-dekra-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Data Fim</label>
          <input 
            v-model="filtros.data_fim" 
            type="date" 
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-dekra-500 focus:ring-dekra-500"
          />
        </div>
        <div class="col-span-full flex justify-end gap-2">
          <button 
            type="submit"
            class="px-6 py-2 bg-dekra-800 text-white rounded-md hover:bg-dekra-700 transition-colors"
          >
            Filtrar
          </button>
          <button 
            type="button"
            @click="limparFiltros"
            class="px-6 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors"
          >
            Limpar
          </button>
        </div>
      </form>
    </Card>
    
    <Card title="Lista de Transações" variant="elevated">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="text-left p-3 border-b font-medium text-gray-900">Data</th>
              <th class="text-left p-3 border-b font-medium text-gray-900">Tipo</th>
              <th class="text-left p-3 border-b font-medium text-gray-900">Categoria</th>
              <th class="text-left p-3 border-b font-medium text-gray-900">Descrição</th>
              <th class="text-left p-3 border-b font-medium text-gray-900">Forma Pagamento</th>
              <th class="text-right p-3 border-b font-medium text-gray-900">Valor</th>
              <th class="text-center p-3 border-b font-medium text-gray-900">Status</th>
              <th class="text-center p-3 border-b font-medium text-gray-900">Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="t in data.data" :key="t.id" class="border-b last:border-b-0 hover:bg-gray-50">
              <td class="p-3 font-medium">{{ formatarData(t.data) }}</td>
              <td class="p-3">
                <span :class="[
                  'px-2 py-1 rounded-full text-xs font-medium',
                  t.tipo === 'ENTRADA' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                ]">
                  {{ t.tipo }}
                </span>
              </td>
              <td class="p-3 text-gray-600">{{ t.categoria_label }}</td>
              <td class="p-3 text-gray-600">
                {{ t.descricao }}
                <span v-if="t.venda_id" class="text-xs text-blue-600 ml-1">(Venda #{{ t.venda_id }})</span>
                <span v-if="t.comprovante_pagamento" class="text-xs text-green-600 ml-1">📎</span>
              </td>
              <td class="p-3 text-gray-600">{{ t.forma_pagamento_label || '-' }}</td>
              <td class="p-3 text-right font-semibold" :class="t.tipo === 'ENTRADA' ? 'text-green-600' : 'text-red-600'">
                {{ formatarMoeda(t.valor) }}
              </td>
              <td class="p-3 text-center">
                <span :class="[
                  'px-2 py-1 rounded-full text-xs font-medium',
                  t.status === 'PAGO' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                ]">
                  {{ t.status || 'PAGO' }}
                </span>
              </td>
              <td class="p-3 text-center">
                <div class="flex justify-center gap-2">
                  <Link 
                    v-if="!t.venda_id"
                    :href="route('financeiro.edit', t.id)" 
                    class="text-dekra-800 hover:text-dekra-600 font-medium"
                  >
                    Editar
                  </Link>
                  <button 
                    v-if="!t.venda_id"
                    @click="excluir(t.id)"
                    class="text-red-600 hover:text-red-800 font-medium"
                  >
                    Excluir
                  </button>
                  <span v-if="t.venda_id" class="text-gray-400 text-xs">Automático</span>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-if="data.data.length === 0" class="text-center py-8 text-gray-500">
          Nenhuma transação encontrada
        </div>
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
                    ? 'bg-dekra-800 text-white' 
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
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import Card from '@/Components/Card.vue'

const props = defineProps({
  data: Object,
  filtros: Object,
  categorias_entrada: Object,
  categorias_saida: Object,
  formas_pagamento: Object
})

const filtros = ref({
  search: props.filtros?.search || '',
  tipo: props.filtros?.tipo || '',
  categoria: props.filtros?.categoria || '',
  status: props.filtros?.status || '',
  forma_pagamento: props.filtros?.forma_pagamento || '',
  data_inicio: props.filtros?.data_inicio || '',
  data_fim: props.filtros?.data_fim || ''
})

let searchTimeout = null

const filtrar = () => {
  router.get(route('financeiro.index'), filtros.value, { preserveState: true })
}

const buscar = () => {
  // Debounce para evitar muitas requisições
  if (searchTimeout) {
    clearTimeout(searchTimeout)
  }
  
  searchTimeout = setTimeout(() => {
    router.get(route('financeiro.index'), filtros.value, {
      preserveState: true,
      preserveScroll: true,
      only: ['data']
    })
  }, 500)
}

const limparBusca = () => {
  filtros.value.search = ''
  router.get(route('financeiro.index'), filtros.value, { preserveState: true })
}

const limparFiltros = () => {
  filtros.value = {
    search: '',
    tipo: '',
    categoria: '',
    status: '',
    forma_pagamento: '',
    data_inicio: '',
    data_fim: ''
  }
  filtrar()
}

const excluir = (id) => {
  if (confirm('Tem certeza que deseja excluir esta transação?')) {
    router.delete(route('financeiro.destroy', id))
  }
}

const formatarMoeda = (valor) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(valor || 0)
}

const formatarData = (data) => {
  if (!data) return '';
  
  // Para datas no formato YYYY-MM-DD, não usar new Date() pois considera UTC
  // e pode retornar dia anterior devido ao timezone
  if (typeof data === 'string' && data.match(/^\d{4}-\d{2}-\d{2}$/)) {
    const [year, month, day] = data.split('-');
    return `${day}/${month}/${year}`;
  }
  
  // Caso contrário, usar conversão normal
  return new Date(data).toLocaleDateString('pt-BR');
}
</script>

