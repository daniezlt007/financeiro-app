<template>
  <div class="p-6 max-w-3xl space-y-4">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-xl font-bold">Venda #{{ item.id }}</h1>
      <div class="flex gap-2">
        <a :href="`/vendas/${item.id}/edit`" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 font-medium">Editar</a>
        <a :href="`/vendas/${item.id}/recibo`" target="_blank" class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700 font-medium">📄 Recibo PDF</a>
        <Link href="/vendas" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 font-medium">Voltar</Link>
      </div>
    </div>
    
    <!-- Informações Gerais -->
    <div class="bg-gray-50 p-4 rounded-lg">
      <h3 class="font-semibold mb-3">Informações Gerais</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div><strong>Data:</strong> {{ new Date(item.data).toLocaleDateString() }}</div>
        <div><strong>Consumidor:</strong> {{ item.consumidor_tipo }}</div>
        <div><strong>Resp. Venda:</strong> {{ item.funcionario?.nome_completo || '-' }}</div>
        <div><strong>Forma Pgto:</strong> {{ item.forma_pagamento }}</div>
        <div><strong>Valor Total:</strong> R$ {{ Number(item.valor_total||0).toLocaleString('pt-BR',{minimumFractionDigits:2}) }}</div>
        <div v-if="item.percentual_desconto"><strong>Desconto:</strong> {{ item.percentual_desconto }}%</div>
      </div>
    </div>

    <!-- Dados do Cliente -->
    <div class="bg-blue-50 p-4 rounded-lg">
      <h3 class="font-semibold text-blue-800 mb-3">Dados do Cliente</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div><strong>Nome:</strong> {{ item.cliente_nome_completo || '-' }}</div>
        <div v-if="item.cliente_cpf_cnpj"><strong>CPF/CNPJ:</strong> {{ item.cliente_cpf_cnpj }}</div>
        <div v-if="item.cliente_telefone"><strong>Telefone:</strong> {{ item.cliente_telefone }}</div>
        <div v-if="item.cliente_placa"><strong>Placa:</strong> {{ item.cliente_placa }}</div>
        <div v-if="item.cliente_chassi"><strong>Chassi:</strong> {{ item.cliente_chassi }}</div>
      </div>
    </div>

    <!-- Itens -->
    <div class="bg-white border rounded-lg p-4">
      <h3 class="font-semibold mb-3">Itens da Venda</h3>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="bg-gray-100">
              <th class="p-2 text-left">Tipo</th>
              <th class="p-2 text-left">Item</th>
              <th class="p-2 text-center">Qtde</th>
              <th class="p-2 text-right">Valor Unit.</th>
              <th class="p-2 text-right">Total</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="i in item.itens" :key="i.id" class="border-t">
              <td class="p-2">{{ i.tipo_item }}</td>
              <td class="p-2">
                {{ i.tipo_item === 'SERVICO' ? (i.servico?.tipo_servico || 'Serviço') : (i.produto?.nome || 'Produto') }}
              </td>
              <td class="p-2 text-center">{{ i.qtde }}</td>
              <td class="p-2 text-right">R$ {{ Number(i.valor_unitario).toLocaleString('pt-BR',{minimumFractionDigits:2}) }}</td>
              <td class="p-2 text-right">R$ {{ Number(i.qtde * i.valor_unitario).toLocaleString('pt-BR',{minimumFractionDigits:2}) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagamentos -->
    <div class="bg-white border rounded-lg p-4">
      <div class="flex items-center justify-between mb-3">
        <h3 class="font-semibold">Pagamentos</h3>
        <span class="text-sm text-gray-600">{{ item.pagamentos?.length || 0 }} pagamento(s)</span>
      </div>
      
      <div v-if="item.pagamentos && item.pagamentos.length > 0" class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="bg-gray-100">
              <th class="p-2 text-left">ID</th>
              <th class="p-2 text-left">Data</th>
              <th class="p-2 text-left">Forma de Pagamento</th>
              <th class="p-2 text-right">Valor</th>
              <th class="p-2 text-center">Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="pagamento in item.pagamentos" :key="pagamento.id" class="border-t">
              <td class="p-2">#{{ pagamento.id }}</td>
              <td class="p-2">{{ new Date(pagamento.data).toLocaleDateString('pt-BR') }}</td>
              <td class="p-2">{{ pagamento.forma_pagamento }}</td>
              <td class="p-2 text-right font-semibold">R$ {{ Number(pagamento.valor).toLocaleString('pt-BR',{minimumFractionDigits:2}) }}</td>
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
          </tbody>
        </table>
      </div>
      
      <div v-else class="text-center py-4 text-gray-500">
        Nenhum pagamento encontrado
      </div>
    </div>
  </div>
</template>
<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({ item: Object })
</script>
