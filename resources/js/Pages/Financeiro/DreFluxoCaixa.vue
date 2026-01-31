<template>
  <Head title="DRE e Fluxo de Caixa" />
  
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">DRE e Fluxo de Caixa</h1>
      <p class="mt-2 text-gray-600">Demonstração do Resultado do Exercício e Fluxo de Caixa</p>
    </div>
      <!-- Filtros de Período -->
      <Card title="Filtros de Período" variant="elevated" class="mb-6">
        <form @submit.prevent="filtrar" class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
          <div class="flex items-end gap-2">
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

      <!-- DRE - Demonstração do Resultado do Exercício -->
      <Card title="DRE - Demonstração do Resultado do Exercício" variant="elevated" class="mb-6">
        <div class="space-y-4">
          <!-- Receitas -->
          <div class="border-b pb-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-3">Receitas</h3>
            <div class="space-y-2">
              <div class="flex justify-between items-center py-2 px-4 bg-green-50 rounded">
                <span class="text-gray-700">Receitas de Vendas</span>
                <span class="font-semibold text-green-600">{{ formatarMoeda(dre.receitas_vendas) }}</span>
              </div>
              <div class="flex justify-between items-center py-2 px-4 bg-green-50 rounded">
                <span class="text-gray-700">Outras Receitas</span>
                <span class="font-semibold text-green-600">{{ formatarMoeda(dre.receitas_outras) }}</span>
              </div>
              <div class="flex justify-between items-center py-2 px-4 bg-green-100 border-t-2 border-green-500 rounded mt-2">
                <span class="font-bold text-gray-800">Total de Receitas</span>
                <span class="font-bold text-green-700 text-lg">{{ formatarMoeda(dre.total_receitas) }}</span>
              </div>
            </div>
            
            <!-- Detalhamento de Receitas por Categoria -->
            <div v-if="dre.receitas_por_categoria.length > 0" class="mt-4">
              <h4 class="text-sm font-medium text-gray-600 mb-2">Detalhamento:</h4>
              <div class="space-y-1">
                <div 
                  v-for="item in dre.receitas_por_categoria" 
                  :key="item.categoria"
                  class="flex justify-between items-center py-1 px-3 text-sm"
                >
                  <span class="text-gray-600">{{ item.label }}</span>
                  <span class="text-gray-700">{{ formatarMoeda(item.total) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Despesas -->
          <div class="border-b pb-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-3">Despesas</h3>
            <div class="space-y-2">
              <div class="flex justify-between items-center py-2 px-4 bg-red-50 rounded">
                <span class="text-gray-700">Despesas Operacionais</span>
                <span class="font-semibold text-red-600">{{ formatarMoeda(dre.despesas_operacionais) }}</span>
              </div>
              <div class="flex justify-between items-center py-2 px-4 bg-red-50 rounded">
                <span class="text-gray-700">Impostos e Taxas</span>
                <span class="font-semibold text-red-600">{{ formatarMoeda(dre.despesas_impostos) }}</span>
              </div>
              <div class="flex justify-between items-center py-2 px-4 bg-red-50 rounded">
                <span class="text-gray-700">Retiradas de Sócios</span>
                <span class="font-semibold text-red-600">{{ formatarMoeda(dre.retiradas) }}</span>
              </div>
              <div class="flex justify-between items-center py-2 px-4 bg-red-50 rounded">
                <span class="text-gray-700">Outras Despesas</span>
                <span class="font-semibold text-red-600">{{ formatarMoeda(dre.outras_despesas) }}</span>
              </div>
              <div class="flex justify-between items-center py-2 px-4 bg-red-100 border-t-2 border-red-500 rounded mt-2">
                <span class="font-bold text-gray-800">Total de Despesas</span>
                <span class="font-bold text-red-700 text-lg">{{ formatarMoeda(dre.total_despesas) }}</span>
              </div>
            </div>
            
            <!-- Detalhamento de Despesas por Categoria -->
            <div v-if="dre.despesas_por_categoria.length > 0" class="mt-4">
              <h4 class="text-sm font-medium text-gray-600 mb-2">Detalhamento:</h4>
              <div class="space-y-1">
                <div 
                  v-for="item in dre.despesas_por_categoria" 
                  :key="item.categoria"
                  class="flex justify-between items-center py-1 px-3 text-sm"
                >
                  <span class="text-gray-600">{{ item.label }}</span>
                  <span class="text-gray-700">{{ formatarMoeda(item.total) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Resultado do Exercício -->
          <div class="pt-4">
            <div 
              :class="[
                'flex justify-between items-center py-4 px-6 rounded-lg',
                dre.resultado_exercicio >= 0 
                  ? 'bg-green-100 border-2 border-green-500' 
                  : 'bg-red-100 border-2 border-red-500'
              ]"
            >
              <span class="font-bold text-lg text-gray-800">
                {{ dre.resultado_exercicio >= 0 ? 'Lucro' : 'Prejuízo' }}
              </span>
              <span 
                :class="[
                  'font-bold text-2xl',
                  dre.resultado_exercicio >= 0 ? 'text-green-700' : 'text-red-700'
                ]"
              >
                {{ formatarMoeda(dre.resultado_exercicio) }}
              </span>
            </div>
          </div>
        </div>
      </Card>

      <!-- Fluxo de Caixa -->
      <Card title="Fluxo de Caixa" variant="elevated" class="mb-6">
        <div class="space-y-4">
          <!-- Entradas de Caixa -->
          <div class="border-b pb-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-3">Entradas de Caixa</h3>
            <div class="space-y-2">
              <div class="flex justify-between items-center py-2 px-4 bg-blue-50 rounded">
                <span class="text-gray-700">Entradas Pagas</span>
                <span class="font-semibold text-blue-600">{{ formatarMoeda(fluxo_caixa.entradas_pagas) }}</span>
              </div>
              <div class="flex justify-between items-center py-2 px-4 bg-yellow-50 rounded">
                <span class="text-gray-700">Entradas Pendentes</span>
                <span class="font-semibold text-yellow-600">{{ formatarMoeda(fluxo_caixa.entradas_pendentes) }}</span>
              </div>
              <div class="flex justify-between items-center py-2 px-4 bg-blue-100 border-t-2 border-blue-500 rounded mt-2">
                <span class="font-bold text-gray-800">Total de Entradas</span>
                <span class="font-bold text-blue-700 text-lg">{{ formatarMoeda(fluxo_caixa.entradas_total) }}</span>
              </div>
            </div>
            
            <!-- Detalhamento de Entradas por Categoria -->
            <div v-if="fluxo_caixa.entradas_por_categoria.length > 0" class="mt-4">
              <h4 class="text-sm font-medium text-gray-600 mb-2">Detalhamento (Apenas Pagas):</h4>
              <div class="space-y-1">
                <div 
                  v-for="item in fluxo_caixa.entradas_por_categoria" 
                  :key="item.categoria"
                  class="flex justify-between items-center py-1 px-3 text-sm"
                >
                  <span class="text-gray-600">{{ item.label }}</span>
                  <span class="text-gray-700">{{ formatarMoeda(item.total) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Saídas de Caixa -->
          <div class="border-b pb-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-3">Saídas de Caixa</h3>
            <div class="space-y-2">
              <div class="flex justify-between items-center py-2 px-4 bg-red-50 rounded">
                <span class="text-gray-700">Saídas Pagas</span>
                <span class="font-semibold text-red-600">{{ formatarMoeda(fluxo_caixa.saidas_pagas) }}</span>
              </div>
              <div class="flex justify-between items-center py-2 px-4 bg-yellow-50 rounded">
                <span class="text-gray-700">Saídas Pendentes</span>
                <span class="font-semibold text-yellow-600">{{ formatarMoeda(fluxo_caixa.saidas_pendentes) }}</span>
              </div>
              <div class="flex justify-between items-center py-2 px-4 bg-red-100 border-t-2 border-red-500 rounded mt-2">
                <span class="font-bold text-gray-800">Total de Saídas</span>
                <span class="font-bold text-red-700 text-lg">{{ formatarMoeda(fluxo_caixa.saidas_total) }}</span>
              </div>
            </div>
            
            <!-- Detalhamento de Saídas por Categoria -->
            <div v-if="fluxo_caixa.saidas_por_categoria.length > 0" class="mt-4">
              <h4 class="text-sm font-medium text-gray-600 mb-2">Detalhamento (Apenas Pagas):</h4>
              <div class="space-y-1">
                <div 
                  v-for="item in fluxo_caixa.saidas_por_categoria" 
                  :key="item.categoria"
                  class="flex justify-between items-center py-1 px-3 text-sm"
                >
                  <span class="text-gray-600">{{ item.label }}</span>
                  <span class="text-gray-700">{{ formatarMoeda(item.total) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Saldo de Caixa -->
          <div class="pt-4">
            <div 
              :class="[
                'flex justify-between items-center py-4 px-6 rounded-lg',
                fluxo_caixa.saldo_caixa >= 0 
                  ? 'bg-green-100 border-2 border-green-500' 
                  : 'bg-red-100 border-2 border-red-500'
              ]"
            >
              <span class="font-bold text-lg text-gray-800">Saldo de Caixa (Pagas)</span>
              <span 
                :class="[
                  'font-bold text-2xl',
                  fluxo_caixa.saldo_caixa >= 0 ? 'text-green-700' : 'text-red-700'
                ]"
              >
                {{ formatarMoeda(fluxo_caixa.saldo_caixa) }}
              </span>
            </div>
            <p class="text-xs text-gray-500 mt-2">
              * O saldo considera apenas transações pagas no período selecionado
            </p>
          </div>
        </div>
      </Card>

      <!-- Informações do Período -->
      <div class="text-sm text-gray-600 text-center">
        Período: {{ formatarData(periodo.data_inicio) }} até {{ formatarData(periodo.data_fim) }}
      </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import Card from '@/Components/Card.vue'

const props = defineProps({
  dre: {
    type: Object,
    required: true
  },
  fluxo_caixa: {
    type: Object,
    required: true
  },
  periodo: {
    type: Object,
    required: true
  },
  categorias_entrada: {
    type: Object,
    required: true
  },
  categorias_saida: {
    type: Object,
    required: true
  }
})

const filtros = ref({
  data_inicio: props.periodo.data_inicio,
  data_fim: props.periodo.data_fim
})

const filtrar = () => {
  router.get(route('financeiro.dre-fluxo-caixa'), filtros.value, {
    preserveState: true,
    preserveScroll: true
  })
}

const limparFiltros = () => {
  const hoje = new Date()
  const primeiroDiaMes = new Date(hoje.getFullYear(), hoje.getMonth(), 1)
  const ultimoDiaMes = new Date(hoje.getFullYear(), hoje.getMonth() + 1, 0)
  
  filtros.value = {
    data_inicio: primeiroDiaMes.toISOString().split('T')[0],
    data_fim: ultimoDiaMes.toISOString().split('T')[0]
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
  if (!data) return ''
  const date = new Date(data + 'T00:00:00')
  return date.toLocaleDateString('pt-BR')
}
</script>

