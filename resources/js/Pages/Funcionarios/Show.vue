<template>
  <div class="p-6 max-w-4xl">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">Detalhes do Funcionário</h1>
      <div class="space-x-2">
        <Link :href="`/funcionarios/${item.id}/edit`" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
          Editar
        </Link>
        <Link href="/funcionarios" class="px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50">
          Voltar
        </Link>
      </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-medium text-gray-900">{{ item.nome_completo }}</h2>
        <p class="text-sm text-gray-600">{{ item.cargo }} - {{ item.empresa?.nome_fantasia }}</p>
      </div>
      
      <div class="px-6 py-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Informações Pessoais -->
          <div>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Informações Pessoais</h3>
            <dl class="space-y-3">
              <div>
                <dt class="text-sm font-medium text-gray-500">Nome Completo</dt>
                <dd class="text-sm text-gray-900">{{ item.nome_completo }}</dd>
              </div>
              <div v-if="item.telefone">
                <dt class="text-sm font-medium text-gray-500">Telefone</dt>
                <dd class="text-sm text-gray-900">{{ item.telefone }}</dd>
              </div>
            </dl>
          </div>

          <!-- Informações Profissionais -->
          <div>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Informações Profissionais</h3>
            <dl class="space-y-3">
              <div>
                <dt class="text-sm font-medium text-gray-500">Cargo</dt>
                <dd class="text-sm text-gray-900">{{ item.cargo }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Status</dt>
                <dd>
                  <span :class="getStatusClass(item.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                    {{ item.status }}
                  </span>
                </dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Empresa</dt>
                <dd class="text-sm text-gray-900">{{ item.empresa?.nome_fantasia || '-' }}</dd>
              </div>
            </dl>
          </div>
        </div>


        <!-- Observações -->
        <div v-if="item.observacoes" class="mt-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Observações</h3>
          <p class="text-sm text-gray-900 whitespace-pre-line">{{ item.observacoes }}</p>
        </div>

        <!-- Informações do Sistema -->
        <div class="mt-6 pt-6 border-t border-gray-200">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Informações do Sistema</h3>
          <dl class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
              <dt class="text-sm font-medium text-gray-500">Criado em</dt>
              <dd class="text-sm text-gray-900">{{ formatDateTime(item.created_at) }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Última atualização</dt>
              <dd class="text-sm text-gray-900">{{ formatDateTime(item.updated_at) }}</dd>
            </div>
          </dl>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  item: Object
})

function getStatusClass(status) {
  switch (status) {
    case 'ATIVO':
      return 'bg-green-100 text-green-800'
    case 'INATIVO':
      return 'bg-red-100 text-red-800'
    case 'FERIAS':
      return 'bg-yellow-100 text-yellow-800'
    case 'AFASTADO':
      return 'bg-blue-100 text-blue-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

function formatDate(dateString) {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleDateString('pt-BR')
}

function formatDateTime(dateString) {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleString('pt-BR')
}

function formatCurrency(value) {
  if (!value) return '0,00'
  return parseFloat(value).toLocaleString('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  })
}
</script>
