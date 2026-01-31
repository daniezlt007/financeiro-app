<template>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Pagamento #{{ pagamento.id }}</h1>
      <p class="text-gray-600 mt-1">Detalhes do pagamento</p>
    </div>

    <div class="space-y-6">
      <!-- Informações do Pagamento -->
      <Card title="Informações do Pagamento" variant="elevated">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">ID do Pagamento</label>
            <p class="text-lg font-semibold text-gray-900">#{{ pagamento.id }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Data do Pagamento</label>
            <p class="text-lg text-gray-900">{{ new Date(pagamento.data).toLocaleDateString('pt-BR') }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Forma de Pagamento</label>
            <p class="text-lg text-gray-900">{{ pagamento.forma_pagamento }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Valor</label>
            <p class="text-xl font-bold text-green-600">R$ {{ Number(pagamento.valor).toLocaleString('pt-BR',{minimumFractionDigits:2}) }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <span :class="[
              'px-3 py-1 rounded-full text-sm font-medium',
              pagamento.status === 'PAGO' ? 'bg-green-100 text-green-800' :
              pagamento.status === 'PENDENTE' ? 'bg-yellow-100 text-yellow-800' :
              'bg-red-100 text-red-800'
            ]">
              {{ pagamento.status }}
            </span>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Data de Criação</label>
            <p class="text-sm text-gray-600">{{ new Date(pagamento.created_at).toLocaleString('pt-BR') }}</p>
          </div>
        </div>
      </Card>

      <!-- Informações da Venda Associada -->
      <Card title="Venda Associada" variant="elevated" v-if="pagamento.venda">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">ID da Venda</label>
            <p class="text-lg font-semibold text-gray-900">#{{ pagamento.venda.id }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Data da Venda</label>
            <p class="text-lg text-gray-900">{{ new Date(pagamento.venda.data).toLocaleDateString('pt-BR') }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
            <p class="text-lg text-gray-900">{{ pagamento.venda.cliente_nome_completo || 'Não informado' }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Valor Total da Venda</label>
            <p class="text-lg font-semibold text-blue-600">R$ {{ Number(pagamento.venda.valor_total).toLocaleString('pt-BR',{minimumFractionDigits:2}) }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Consumidor</label>
            <p class="text-lg text-gray-900">{{ pagamento.venda.consumidor_tipo }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">CPF/CNPJ</label>
            <p class="text-lg text-gray-900">{{ pagamento.venda.cliente_cpf_cnpj || 'Não informado' }}</p>
          </div>
        </div>
        
        <div class="mt-6 pt-6 border-t">
          <a :href="`/vendas/${pagamento.venda.id}`" 
             class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
            </svg>
            Visualizar Venda Completa
          </a>
        </div>
      </Card>

      <!-- Ações -->
      <Card title="Ações" variant="elevated">
        <div class="flex flex-wrap gap-4">
          <a :href="`/pagamentos/${pagamento.id}/edit`" 
             class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Editar Pagamento
          </a>
          
          <a href="/pagamentos" 
             class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Voltar para Pagamentos
          </a>
        </div>
      </Card>
    </div>
  </div>
</template>

<script setup>
import Card from '@/Components/Card.vue'

defineProps({ pagamento: Object })
</script>
