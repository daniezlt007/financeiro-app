<template>
  <div class="max-w-4xl mx-auto p-6">
    <div class="bg-white rounded-lg shadow-md p-6">
      <!-- Cabeçalho -->
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Detalhes da Transação</h1>
        <div class="flex space-x-2">
          <Link
            :href="route('financeiro.edit', transacao.id)"
            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md transition-colors"
          >
            Editar
          </Link>
          <Link
            :href="route('financeiro.index')"
            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition-colors"
          >
            Voltar
          </Link>
        </div>
      </div>

      <!-- Informações da Transação -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Coluna Esquerda -->
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
            <div class="flex items-center">
              <span
                :class="[
                  'px-3 py-1 rounded-full text-sm font-medium',
                  transacao.tipo === 'ENTRADA'
                    ? 'bg-green-100 text-green-800'
                    : 'bg-red-100 text-red-800'
                ]"
              >
                {{ transacao.tipo === 'ENTRADA' ? 'Entrada' : 'Saída' }}
              </span>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
            <p class="text-gray-900">{{ transacao.categoria_label || transacao.categoria }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Data</label>
            <p class="text-gray-900">{{ formatDate(transacao.data) }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Valor</label>
            <p class="text-2xl font-bold text-gray-900">
              {{ formatCurrency(transacao.valor) }}
            </p>
          </div>
        </div>

        <!-- Coluna Direita -->
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
            <p class="text-gray-900">{{ transacao.descricao }}</p>
          </div>

          <div v-if="transacao.observacoes">
            <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
            <p class="text-gray-900">{{ transacao.observacoes }}</p>
          </div>

          <div v-if="transacao.forma_pagamento">
            <label class="block text-sm font-medium text-gray-700 mb-1">Forma de Pagamento</label>
            <p class="text-gray-900">{{ transacao.forma_pagamento_label || transacao.forma_pagamento }}</p>
          </div>

          <div v-if="transacao.empresa">
            <label class="block text-sm font-medium text-gray-700 mb-1">Empresa</label>
            <p class="text-gray-900">{{ transacao.empresa.nome_fantasia }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Registrado por</label>
            <p class="text-gray-900">{{ transacao.user?.name || 'N/A' }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Data de Registro</label>
            <p class="text-gray-900">{{ formatDateTime(transacao.created_at) }}</p>
          </div>
        </div>
      </div>

      <!-- Comprovante -->
      <div v-if="transacao.comprovante_url" class="mt-6">
        <label class="block text-sm font-medium text-gray-700 mb-2">Comprovante</label>
        <div class="border rounded-lg p-4 bg-gray-50">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <svg class="w-8 h-8 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
              <div>
                <p class="text-sm font-medium text-gray-900">Comprovante de Pagamento</p>
                <p class="text-xs text-gray-500">Arquivo anexado</p>
              </div>
            </div>
            <button
              @click="verificarArquivo(transacao.comprovante_url)"
              class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition-colors"
            >
              Visualizar
            </button>
          </div>
        </div>
      </div>

      <!-- Venda Vinculada -->
      <div v-if="transacao.venda" class="mt-6">
        <label class="block text-sm font-medium text-gray-700 mb-2">Venda Vinculada</label>
        <div class="border rounded-lg p-4 bg-blue-50">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-900">Venda #{{ transacao.venda.id }}</p>
              <p class="text-xs text-gray-500">{{ formatDate(transacao.venda.data_venda) }}</p>
            </div>
            <Link
              :href="route('vendas.show', transacao.venda.id)"
              class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition-colors"
            >
              Ver Venda
            </Link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  transacao: Object
})

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('pt-BR')
}

const formatDateTime = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleString('pt-BR')
}

const formatCurrency = (value) => {
  if (!value) return 'R$ 0,00'
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value)
}

const verificarArquivo = async (url) => {
  try {
    const response = await fetch(url, { method: 'HEAD' })
    if (response.ok) {
      window.open(url, '_blank')
    } else {
      alert('Arquivo não encontrado ou não acessível.')
    }
  } catch (error) {
    console.error('Erro ao verificar arquivo:', error)
    alert('Erro ao acessar o arquivo.')
  }
}
</script>
