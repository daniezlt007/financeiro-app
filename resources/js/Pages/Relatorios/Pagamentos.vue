<script setup>
import { ref } from 'vue'
import { Head, useForm, Link } from '@inertiajs/vue3'
import Card from '@/Components/Card.vue'

const props = defineProps({
  filtros: Object,
  pagamentos: Object,
  totais: Object,
  servicos: Array,
  parceiros: Array,
})

const form = useForm({
  data_inicial: props.filtros?.data_inicial || '',
  data_final: props.filtros?.data_final || '',
  servico_id: props.filtros?.servico_id || '',
  status: props.filtros?.status || '',
  parceiro: props.filtros?.parceiro || '',
})

function getServico(venda) {
  if (!venda || !venda.itens || venda.itens.length === 0) {
    return null
  }
  
  const itemServico = venda.itens.find(item => item.tipo_item === 'SERVICO' && item.servico)
  if (itemServico && itemServico.servico) {
    return itemServico.servico.tipo_servico
  }
  
  return null
}
</script>

<template>
  <Head title="Relatório de Pagamentos" />

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold mb-8 text-center">Relatório de Pagamentos</h1>

    <!-- FILTROS -->
    <Card title="Filtros" class="mb-8">
      <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
      <div>
        <label class="block text-sm">Data Inicial</label>
        <input type="date" v-model="form.data_inicial" name="data_inicial"
          class="border rounded p-2 w-full"/>
      </div>

      <div>
        <label class="block text-sm">Data Final</label>
        <input type="date" v-model="form.data_final" name="data_final"
          class="border rounded p-2 w-full"/>
      </div>

      <div>
        <label class="block text-sm">Serviço</label>
        <select v-model="form.servico_id" name="servico_id" class="border rounded p-2 w-full">
          <option value="">Todos</option>
          <option v-for="s in servicos" :key="s.id" :value="s.id">{{ s.nome }}</option>
        </select>
      </div>

      <div>
        <label class="block text-sm">Status</label>
        <select v-model="form.status" name="status" class="border rounded p-2 w-full">
          <option value="">Todos</option>
          <option value="PAGO">Pago</option>
          <option value="PENDENTE">Pendente</option>
          <option value="CANCELADO">Cancelado</option>
        </select>
      </div>

      <div>
        <label class="block text-sm">Parceiro</label>
        <select v-model="form.parceiro" name="parceiro" class="border rounded p-2 w-full">
          <option value="">Todos</option>
          <option v-for="p in parceiros" :key="p" :value="p">{{ p }}</option>
        </select>
      </div>

      <div class="col-span-full">
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded">Filtrar</button>
      </div>
      </form>
    </Card>

    <Card class="mb-6">
      <div class="flex justify-center gap-4">
        <a :href="`/relatorios/pagamentos/export/excel?${new URLSearchParams(filtros).toString()}`"
            class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 transition-colors">Exportar CSV</a>
        <a :href="`/relatorios/pagamentos/export/pdf?${new URLSearchParams(filtros).toString()}`"
            class="bg-red-600 text-white px-6 py-2 rounded hover:bg-red-700 transition-colors">Exportar PDF</a>
      </div>
    </Card>

    <!-- TOTAIS -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
      <!-- Total Geral -->
      <div class="bg-blue-50 p-4 rounded-lg">
        <h3 class="text-lg font-semibold text-blue-800">Total Geral</h3>
        <p class="text-2xl font-bold text-blue-600">
          R$ {{ Number(totais?.totalGeral || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2}) }}
        </p>
      </div>

      <!-- Total Pago -->
      <div class="bg-green-50 p-4 rounded-lg">
        <h3 class="text-lg font-semibold text-green-800">Total Pago</h3>
        <p class="text-2xl font-bold text-green-600">
          R$ {{ Number(totais?.totalPago || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2}) }}
        </p>
      </div>

      <!-- Total Pendente -->
      <div class="bg-yellow-50 p-4 rounded-lg">
        <h3 class="text-lg font-semibold text-yellow-800">Total Pendente</h3>
        <p class="text-2xl font-bold text-yellow-600">
          R$ {{ Number(totais?.totalPendente || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2}) }}
        </p>
      </div>
    </div>

    <!-- LISTA DE PAGAMENTOS -->
    <Card title="Pagamentos" class="mb-6">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-100">
            <tr>
              <th class="p-2 text-left">ID</th>
              <th class="p-2 text-left">Data</th>
              <th class="p-2 text-left">Cliente</th>
              <th class="p-2 text-left">Placa</th>
              <th class="p-2 text-left">Serviço</th>
              <th class="p-2 text-left">Parceiro</th>
              <th class="p-2 text-right">Valor</th>
              <th class="p-2 text-left">Forma Pagamento</th>
              <th class="p-2 text-center">Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="pagamento in pagamentos.data" :key="pagamento.id" class="border-t">
              <td class="p-2">#{{ pagamento.id }}</td>
              <td class="p-2">{{ new Date(pagamento.data).toLocaleDateString('pt-BR') }}</td>
              <td class="p-2">{{ pagamento.venda?.cliente_nome_completo || '-' }}</td>
              <td class="p-2">{{ pagamento.venda?.cliente_placa || '-' }}</td>
              <td class="p-2">
                <span v-if="getServico(pagamento.venda)">
                  {{ getServico(pagamento.venda) }}
                </span>
                <span v-else>-</span>
              </td>
              <td class="p-2">{{ pagamento.venda?.parceiro || '-' }}</td>
              <td class="p-2 text-right font-semibold">R$ {{ Number(pagamento.valor).toLocaleString('pt-BR',{minimumFractionDigits:2}) }}</td>
              <td class="p-2">{{ pagamento.forma_pagamento }}</td>
              <td class="p-2 text-center">
                <span :class="[
                  'px-2 py-1 rounded-full text-xs font-medium',
                  pagamento.status === 'PAGO' ? 'bg-green-100 text-green-800' :
                  pagamento.status === 'PENDENTE' ? 'bg-yellow-100 text-yellow-800' :
                  'bg-red-100 text-red-800'
                ]">
                  {{ pagamento.status }}
                </span>
              </td>
            </tr>
            <tr v-if="pagamentos.data.length === 0">
              <td colspan="9" class="p-4 text-center text-gray-500">Nenhum pagamento encontrado</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginação -->
      <div v-if="pagamentos.last_page > 1" class="mt-6 flex justify-center">
        <nav class="flex gap-2">
          <Link 
            v-for="page in pagamentos.links" 
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

    <!-- TOTAIS POR SERVIÇO -->
    <Card title="Totais por Serviço" class="mb-6" v-if="Object.keys(totais?.totaisPorServico || {}).length > 0">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-100">
            <tr>
              <th class="p-2 text-left">Serviço</th>
              <th class="p-2 text-right">Total</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="[servico, total] in Object.entries(totais?.totaisPorServico || {}).sort((a,b) => b[1] - a[1])" :key="servico" class="border-t">
              <td class="p-2">{{ servico }}</td>
              <td class="p-2 text-right font-semibold">R$ {{ Number(total).toLocaleString('pt-BR',{minimumFractionDigits:2}) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </Card>

    <!-- TOTAIS POR PARCEIRO -->
    <Card title="Totais por Parceiro" class="mb-6" v-if="Object.keys(totais?.totaisPorParceiro || {}).length > 0">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-100">
            <tr>
              <th class="p-2 text-left">Parceiro</th>
              <th class="p-2 text-right">Total</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="[parceiro, total] in Object.entries(totais?.totaisPorParceiro || {}).sort((a,b) => b[1] - a[1])" :key="parceiro" class="border-t">
              <td class="p-2">{{ parceiro }}</td>
              <td class="p-2 text-right font-semibold">R$ {{ Number(total).toLocaleString('pt-BR',{minimumFractionDigits:2}) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </Card>
  </div>
</template>
