<script setup>
import { ref } from 'vue'
import { Head, useForm, Link } from '@inertiajs/vue3'
import Card from '@/Components/Card.vue'

const props = defineProps({
  filtros: Object,
  vendas: Object, // Agora é um objeto de paginação
  totais: Object,
  servicos: Array,
  usuarios: Array,
  parceiros: Array,
})

const form = useForm({
  data_inicial: props.filtros?.data_inicial || '',
  data_final: props.filtros?.data_final || '',
  servico_id: props.filtros?.servico_id || '',
  user_id: props.filtros?.user_id || '',
  parceiro: props.filtros?.parceiro || '',
})
</script>

<template>
  <Head title="Relatório de Vendas" />

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold mb-8 text-center">Relatório de Vendas</h1>

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
        <label class="block text-sm">Vendedor</label>
        <select v-model="form.user_id" name="user_id" class="border rounded p-2 w-full">
          <option value="">Todos</option>
          <option v-for="u in usuarios" :key="u.id" :value="u.id">{{ u.name }}</option>
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
        <a :href="route('relatorios.vendas.excel', filtros)"
            class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 transition-colors">Exportar CSV</a>
        <a :href="route('relatorios.vendas.pdf', filtros)"
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

      <!-- Total por Vistoriador (contagem) -->
      <div class="bg-green-50 p-4 rounded-lg">
        <h3 class="text-lg font-semibold text-green-800">Top Vistoriador</h3>
        <div v-if="Object.keys(totais?.totaisPorVendedor || {}).length > 0">
          <p class="font-semibold text-green-600">
            {{ Object.entries(totais?.totaisPorVendedor || {}).sort((a,b) => b[1] - a[1])[0]?.[0] || 'N/A' }}
          </p>
          <p class="text-lg font-bold text-green-600">
            {{ Number(Object.entries(totais?.totaisPorVendedor || {}).sort((a,b) => b[1] - a[1])[0]?.[1] || 0).toLocaleString('pt-BR') }} atendimentos
          </p>
        </div>
        <p v-else class="text-gray-500">Sem dados</p>
      </div>

      <!-- Total por Serviço -->
      <div class="bg-purple-50 p-4 rounded-lg">
        <h3 class="text-lg font-semibold text-purple-800">Top Serviço</h3>
        <div v-if="Object.keys(totais?.totaisPorServico || {}).length > 0">
          <p class="font-semibold text-purple-600">
            {{ Object.entries(totais?.totaisPorServico || {}).sort((a,b) => b[1] - a[1])[0]?.[0] || 'N/A' }}
          </p>
          <p class="text-lg font-bold text-purple-600">
            R$ {{ Number(Object.entries(totais?.totaisPorServico || {}).sort((a,b) => b[1] - a[1])[0]?.[1] || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2}) }}
          </p>
        </div>
        <p v-else class="text-gray-500">Sem dados</p>
      </div>
    </div>

    <!-- RESULTADOS -->
    <Card :title="`Vendas (${vendas?.total || 0} registros)`" variant="elevated">
      
      <table class="min-w-full">
        <thead>
          <tr class="bg-gray-100 text-left">
            <th class="p-3 border">Data</th>
            <th class="p-3 border">Cliente</th>
            <th class="p-3 border">Parceiro</th>
            <th class="p-3 border">Serviço</th>
            <th class="p-3 border">Valor</th>
            <th class="p-3 border">Vendedor</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="venda in vendas?.data || []" :key="venda.id">
            <td class="p-3 border">{{ (venda.data || '').split('-').reverse().join('/') }}</td>
            <td class="p-3 border">{{ venda.cliente_nome_completo || '-' }}</td>
            <td class="p-3 border">{{ venda.parceiro || '-' }}</td>
            <td class="p-3 border">{{ venda.itens?.find(i => i.tipo_item === 'SERVICO')?.servico?.tipo_servico || '-' }}</td>
            <td class="p-3 border text-right">R$ {{ Number(venda.valor_total || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2}) }}</td>
            <td class="p-3 border">{{ venda.funcionario?.nome_completo || '-' }}</td>
          </tr>
        </tbody>
      </table>

      <!-- Paginação -->
      <div v-if="vendas?.links" class="border-t border-gray-200 mt-4 pt-4">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Mostrando {{ vendas.from }} até {{ vendas.to }} de {{ vendas.total }} resultados
          </div>
          <div class="flex space-x-1">
            <template v-for="link in vendas.links" :key="link.label">
              <Link
                v-if="link.url"
                :href="link.url"
                v-html="link.label"
                :class="[
                  'px-3 py-1 rounded text-sm',
                  link.active 
                    ? 'bg-indigo-600 text-white' 
                    : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                ]"
              />
              <span
                v-else
                v-html="link.label"
                :class="[
                  'px-3 py-1 rounded text-sm opacity-50 cursor-not-allowed bg-gray-100'
                ]"
              />
            </template>
          </div>
        </div>
      </div>
    </Card>

    <!-- TOTAIS DETALHADOS -->
    <div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Totais por Vistoriador (contagem) -->
      <Card title="Totais por Vistoriador">
        <div v-if="Object.keys(totais?.totaisPorVendedor || {}).length > 0" class="space-y-2">
          <div 
            v-for="[vendedor, total] in Object.entries(totais?.totaisPorVendedor || {}).sort((a,b) => b[1] - a[1])" 
            :key="vendedor"
            class="flex justify-between items-center p-2 bg-gray-50 rounded"
          >
            <span class="font-medium">{{ vendedor }}</span>
            <span class="font-bold text-green-600">{{ Number(total).toLocaleString('pt-BR') }} atendimentos</span>
          </div>
        </div>
        <p v-else class="text-gray-500 text-center py-4">Nenhum vistoriador encontrado</p>
      </Card>

      <!-- Totais por Serviço -->
      <Card title="Totais por Serviço">
        <div v-if="Object.keys(totais?.totaisPorServico || {}).length > 0" class="space-y-2">
          <div 
            v-for="[servico, total] in Object.entries(totais?.totaisPorServico || {}).sort((a,b) => b[1] - a[1])" 
            :key="servico"
            class="flex justify-between items-center p-2 bg-gray-50 rounded"
          >
            <span class="font-medium">{{ servico }}</span>
            <span class="font-bold text-purple-600">
              R$ {{ Number(total).toLocaleString('pt-BR', {minimumFractionDigits: 2}) }}
            </span>
          </div>
        </div>
        <p v-else class="text-gray-500 text-center py-4">Nenhum serviço encontrado</p>
      </Card>

      <!-- Totais por Parceiro -->
      <Card title="Totais por Parceiro">
        <div v-if="Object.keys(totais?.totaisPorParceiro || {}).length > 0" class="space-y-2">
          <div 
            v-for="[parceiro, total] in Object.entries(totais?.totaisPorParceiro || {}).sort((a,b) => b[1] - a[1])" 
            :key="parceiro"
            class="flex justify-between items-center p-2 bg-gray-50 rounded"
          >
            <span class="font-medium">{{ parceiro }}</span>
            <span class="font-bold text-blue-600">
              R$ {{ Number(total).toLocaleString('pt-BR', {minimumFractionDigits: 2}) }}
            </span>
          </div>
        </div>
        <p v-else class="text-gray-500 text-center py-4">Nenhum parceiro encontrado</p>
      </Card>
    </div>
  </div>
</template>
