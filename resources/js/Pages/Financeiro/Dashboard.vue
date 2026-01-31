<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Dashboard Financeiro</h1>
      <p class="mt-2 text-gray-600">Visão geral das entradas e saídas</p>
    </div>

    <!-- Filtros -->
    <Card title="Filtros" variant="elevated" class="mb-6">
      <form @submit.prevent="filtrar" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
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

    <!-- Cards de Resumo -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <Card variant="elevated" class="bg-green-50 border-l-4 border-green-500">
        <div class="p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Total Entradas</p>
              <p class="text-3xl font-bold text-green-600 mt-2">
                {{ formatarMoeda(resumo.total_entradas) }}
              </p>
            </div>
            <div class="p-3 bg-green-100 rounded-full">
              <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
            </div>
          </div>
        </div>
      </Card>

      <Card variant="elevated" class="bg-red-50 border-l-4 border-red-500">
        <div class="p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Total Saídas</p>
              <p class="text-3xl font-bold text-red-600 mt-2">
                {{ formatarMoeda(resumo.total_saidas) }}
              </p>
            </div>
            <div class="p-3 bg-red-100 rounded-full">
              <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
              </svg>
            </div>
          </div>
        </div>
      </Card>

      <Card variant="elevated" :class="[
        'border-l-4',
        resumo.saldo >= 0 ? 'bg-blue-50 border-blue-500' : 'bg-orange-50 border-orange-500'
      ]">
        <div class="p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Saldo</p>
              <p class="text-3xl font-bold mt-2" :class="resumo.saldo >= 0 ? 'text-blue-600' : 'text-orange-600'">
                {{ formatarMoeda(resumo.saldo) }}
              </p>
            </div>
            <div class="p-3 rounded-full" :class="resumo.saldo >= 0 ? 'bg-blue-100' : 'bg-orange-100'">
              <svg class="w-8 h-8" :class="resumo.saldo >= 0 ? 'text-blue-600' : 'text-orange-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
          </div>
        </div>
      </Card>

      <Card variant="elevated" class="bg-yellow-50 border-l-4 border-yellow-500">
        <div class="p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Pendente / À Receber</p>
              <p class="text-3xl font-bold text-yellow-600 mt-2">
                {{ formatarMoeda(resumo.total_pendente) }}
              </p>
              <p class="text-xs text-gray-500 mt-1">{{ resumo.qtd_pendente }} pagamento(s)</p>
            </div>
            <div class="p-3 bg-yellow-100 rounded-full">
              <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
          </div>
        </div>
      </Card>
    </div>

    <!-- Entradas e Saídas por Categoria -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
      <Card title="Entradas por Categoria" variant="elevated">
        <div class="space-y-3">
          <div v-for="item in resumo.entradas_por_categoria" :key="item.categoria" class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
            <span class="text-sm font-medium text-gray-700">{{ item.label }}</span>
            <span class="text-sm font-bold text-green-600">{{ formatarMoeda(item.total) }}</span>
          </div>
          <div v-if="resumo.entradas_por_categoria.length === 0" class="text-center py-4 text-gray-500">
            Nenhuma entrada no período
          </div>
        </div>
      </Card>

      <Card title="Saídas por Categoria" variant="elevated">
        <div class="space-y-3">
          <div v-for="item in resumo.saidas_por_categoria" :key="item.categoria" class="flex justify-between items-center p-3 bg-red-50 rounded-lg">
            <span class="text-sm font-medium text-gray-700">{{ item.label }}</span>
            <span class="text-sm font-bold text-red-600">{{ formatarMoeda(item.total) }}</span>
          </div>
          <div v-if="resumo.saidas_por_categoria.length === 0" class="text-center py-4 text-gray-500">
            Nenhuma saída no período
          </div>
        </div>
      </Card>
    </div>

    <!-- Botão para Nova Transação -->
    <div class="flex justify-center mb-6">
      <Link :href="route('financeiro.create')" class="px-6 py-3 bg-dekra-800 text-white rounded-md hover:bg-dekra-700 transition-colors font-medium">
        + Nova Transação
      </Link>
    </div>

    <!-- Transações Recentes -->
    <Card title="Transações Recentes" variant="elevated">
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
            <tr v-for="t in transacoes_recentes" :key="t.id" class="border-b last:border-b-0 hover:bg-gray-50">
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
              <td class="p-3 text-gray-600">{{ t.descricao }}</td>
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
                <Link :href="route('financeiro.index')" class="text-dekra-800 hover:text-dekra-600 font-medium">
                  Ver Todas
                </Link>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-if="transacoes_recentes.length === 0" class="text-center py-8 text-gray-500">
          Nenhuma transação registrada no período
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
  resumo: Object,
  transacoes_recentes: Array,
  periodo: Object,
  filtros: Object,
  categorias_entrada: Object,
  categorias_saida: Object,
  formas_pagamento: Object
})

const filtros = ref({
  data_inicio: props.periodo.data_inicio,
  data_fim: props.periodo.data_fim,
  tipo: props.filtros?.tipo || '',
  categoria: props.filtros?.categoria || '',
  status: props.filtros?.status || '',
  forma_pagamento: props.filtros?.forma_pagamento || ''
})

const filtrar = () => {
  router.get(route('financeiro.dashboard'), filtros.value, { preserveState: true })
}

const limparFiltros = () => {
  filtros.value = {
    data_inicio: '',
    data_fim: '',
    tipo: '',
    categoria: '',
    status: '',
    forma_pagamento: ''
  }
  filtrar()
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

