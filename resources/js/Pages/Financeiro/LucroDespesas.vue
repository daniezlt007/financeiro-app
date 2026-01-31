<template>
  <Head title="Lucro x Despesas" />
  
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Lucro x Despesas</h1>
      <p class="mt-2 text-gray-600">Análise mensal de receitas e despesas</p>
    </div>

    <!-- Filtro de Ano -->
    <Card title="Filtros" variant="elevated" class="mb-6">
      <form @submit.prevent="filtrar" class="flex items-end gap-4">
        <div class="w-48">
          <label class="block text-sm font-medium text-gray-700 mb-1">Ano</label>
          <select 
            v-model="anoSelecionado" 
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-dekra-500 focus:ring-dekra-500"
          >
            <option v-for="ano in anos_disponiveis" :key="ano" :value="ano">
              {{ ano }}
            </option>
          </select>
        </div>
        <button 
          type="submit"
          class="px-6 py-2 bg-dekra-800 text-white rounded-md hover:bg-dekra-700 transition-colors"
        >
          Filtrar
        </button>
      </form>
    </Card>

    <!-- Tabela de Lucro x Despesas -->
    <Card variant="elevated" class="mb-6">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm border-collapse">
          <thead>
            <tr class="bg-gray-50">
              <th class="px-4 py-3 text-left font-semibold text-gray-900 border-b">Item</th>
              <th 
                v-for="dado in dados_mensais" 
                :key="dado.mes"
                class="px-4 py-3 text-center font-semibold text-gray-900 border-b"
              >
                {{ dado.mes_nome }}
              </th>
              <th class="px-4 py-3 text-center font-bold text-gray-900 border-b bg-gray-100">Total</th>
            </tr>
          </thead>
          <tbody>
            <!-- Linha: Lucro Líquido Mensal -->
            <tr>
              <td class="px-4 py-3 font-medium text-gray-800 border-b bg-green-50">
                Lucro Liq Mes
              </td>
              <td 
                v-for="dado in dados_mensais" 
                :key="`lucro-${dado.mes}`"
                :class="[
                  'px-4 py-3 text-right border-b',
                  dado.lucro_liquido >= 0 ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'
                ]"
              >
                {{ formatarMoeda(dado.lucro_liquido) }}
              </td>
              <td 
                :class="[
                  'px-4 py-3 text-right font-bold border-b',
                  totais.lucro_liquido_total >= 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                ]"
              >
                {{ formatarMoeda(totais.lucro_liquido_total) }}
              </td>
            </tr>
            
            <!-- Linha: Despesas Mensais -->
            <tr>
              <td class="px-4 py-3 font-medium text-gray-800 border-b bg-red-50">
                Despesas Mes
              </td>
              <td 
                v-for="dado in dados_mensais" 
                :key="`despesas-${dado.mes}`"
                class="px-4 py-3 text-right border-b bg-red-50 text-red-700"
              >
                {{ formatarMoeda(dado.despesas) }}
              </td>
              <td class="px-4 py-3 text-right font-bold border-b bg-red-100 text-red-800">
                {{ formatarMoeda(totais.total_despesas) }}
              </td>
            </tr>
            
            <!-- Linha: Receitas Mensais (opcional, para referência) -->
            <tr class="bg-gray-50">
              <td class="px-4 py-3 font-medium text-gray-700 border-b">
                Receitas Mes
              </td>
              <td 
                v-for="dado in dados_mensais" 
                :key="`receitas-${dado.mes}`"
                class="px-4 py-3 text-right border-b text-green-700"
              >
                {{ formatarMoeda(dado.receitas) }}
              </td>
              <td class="px-4 py-3 text-right font-bold border-b bg-green-50 text-green-800">
                {{ formatarMoeda(totais.total_receitas) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </Card>

    <!-- Resumo -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
      <Card variant="elevated" class="bg-green-50 border-l-4 border-green-500">
        <div class="p-6">
          <p class="text-sm font-medium text-gray-600">Total de Receitas</p>
          <p class="text-3xl font-bold text-green-600 mt-2">
            {{ formatarMoeda(totais.total_receitas) }}
          </p>
        </div>
      </Card>
      
      <Card variant="elevated" class="bg-red-50 border-l-4 border-red-500">
        <div class="p-6">
          <p class="text-sm font-medium text-gray-600">Total de Despesas</p>
          <p class="text-3xl font-bold text-red-600 mt-2">
            {{ formatarMoeda(totais.total_despesas) }}
          </p>
        </div>
      </Card>
      
      <Card 
        variant="elevated" 
        :class="[
          'border-l-4',
          totais.lucro_liquido_total >= 0 ? 'bg-green-50 border-green-500' : 'bg-red-50 border-red-500'
        ]"
      >
        <div class="p-6">
          <p class="text-sm font-medium text-gray-600">Lucro Líquido Total</p>
          <p 
            :class="[
              'text-3xl font-bold mt-2',
              totais.lucro_liquido_total >= 0 ? 'text-green-600' : 'text-red-600'
            ]"
          >
            {{ formatarMoeda(totais.lucro_liquido_total) }}
          </p>
        </div>
      </Card>
    </div>

    <!-- Informação do Ano -->
    <div class="text-sm text-gray-600 text-center">
      Dados referentes ao ano de {{ ano }}
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import Card from '@/Components/Card.vue'

const props = defineProps({
  dados_mensais: {
    type: Array,
    required: true
  },
  totais: {
    type: Object,
    required: true
  },
  ano: {
    type: Number,
    required: true
  },
  anos_disponiveis: {
    type: Array,
    required: true
  }
})

const anoSelecionado = ref(props.ano)

const filtrar = () => {
  router.get(route('financeiro.lucro-despesas'), {
    ano: anoSelecionado.value
  }, {
    preserveState: true,
    preserveScroll: true
  })
}

const formatarMoeda = (valor) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(valor || 0)
}
</script>

