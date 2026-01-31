<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-8">
      <div class="text-center flex-1">
        <h1 class="text-3xl font-bold text-gray-900">Logs de Auditoria</h1>
        <p class="mt-2 text-gray-600">Histórico completo de ações realizadas no sistema</p>
      </div>
    </div>

    <!-- Busca Global -->
    <Card variant="elevated" class="mb-6">
      <div class="flex gap-2">
        <input
          v-model="filtros.search"
          @input="buscar"
          type="text"
          placeholder="Pesquisar por usuário, ação, modelo, IP..."
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
          <label class="block text-sm font-medium text-gray-700 mb-1">Modelo</label>
          <input
            v-model="filtros.model_type"
            type="text"
            placeholder="Ex: Cliente, Venda..."
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-dekra-500 focus:ring-dekra-500"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">ID do Registro</label>
          <input
            v-model="filtros.model_id"
            type="number"
            placeholder="Ex: 123"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-dekra-500 focus:ring-dekra-500"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Ação</label>
          <select
            v-model="filtros.action"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-dekra-500 focus:ring-dekra-500"
          >
            <option value="">Todas</option>
            <option value="created">Criação</option>
            <option value="updated">Atualização</option>
            <option value="deleted">Exclusão</option>
            <option value="viewed">Visualização</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Usuário</label>
          <select
            v-model="filtros.user_id"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-dekra-500 focus:ring-dekra-500"
          >
            <option value="">Todos</option>
            <option v-for="user in users" :key="user.id" :value="user.id">
              {{ user.name }}
            </option>
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

        <div class="md:col-span-3 lg:col-span-6 flex gap-2">
          <button
            type="submit"
            class="px-4 py-2 bg-dekra-800 text-white rounded-md hover:bg-dekra-700 text-sm"
          >
            Aplicar Filtros
          </button>
          <button
            type="button"
            @click="limparFiltros"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm"
          >
            Limpar Filtros
          </button>
        </div>
      </form>
    </Card>

    <!-- Tabela de Logs -->
    <Card title="Registros" variant="elevated">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="text-left p-3 border-b font-medium text-gray-900">ID</th>
              <th class="text-left p-3 border-b font-medium text-gray-900">Data/Hora</th>
              <th class="text-left p-3 border-b font-medium text-gray-900">Usuário</th>
              <th class="text-left p-3 border-b font-medium text-gray-900">Ação</th>
              <th class="text-left p-3 border-b font-medium text-gray-900">Modelo</th>
              <th class="text-left p-3 border-b font-medium text-gray-900">ID Registro</th>
              <th class="text-left p-3 border-b font-medium text-gray-900">IP</th>
              <th class="text-center p-3 border-b font-medium text-gray-900">Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="log in data.data"
              :key="log.id"
              class="border-b last:border-b-0 hover:bg-gray-50"
            >
              <td class="p-3 font-medium">{{ log.id }}</td>
              <td class="p-3 text-gray-600">
                {{ formatarDataHora(log.created_at) }}
              </td>
              <td class="p-3 text-gray-600">
                {{ log.user?.name || 'Sistema' }}
              </td>
              <td class="p-3">
                <span
                  :class="[
                    'px-2 py-1 rounded-full text-xs font-medium',
                    log.action === 'created' ? 'bg-green-100 text-green-800' :
                    log.action === 'updated' ? 'bg-blue-100 text-blue-800' :
                    log.action === 'deleted' ? 'bg-red-100 text-red-800' :
                    'bg-gray-100 text-gray-800'
                  ]"
                >
                  {{ getActionName(log.action) }}
                </span>
              </td>
              <td class="p-3 text-gray-600">
                {{ getModelName(log.model_type) }}
              </td>
              <td class="p-3 text-gray-600">
                {{ log.model_id || '-' }}
              </td>
              <td class="p-3 text-gray-600 text-xs">
                {{ log.ip_address || '-' }}
              </td>
              <td class="p-3 text-center">
                <a
                  :href="`/audit-logs/${log.id}`"
                  class="text-indigo-600 hover:text-indigo-800 font-medium"
                >
                  Detalhes
                </a>
              </td>
            </tr>
            <tr v-if="data.data.length === 0">
              <td colspan="8" class="p-4 text-center text-gray-500">
                Nenhum registro encontrado
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginação -->
      <div v-if="data.last_page > 1" class="mt-6 flex justify-center">
        <nav class="flex gap-2">
          <Link
            v-for="page in data.links"
            :key="page.label"
            :href="page.url || '#'"
            :class="[
              'px-3 py-2 text-sm rounded border',
              page.active
                ? 'bg-indigo-600 text-white border-indigo-600'
                : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50',
              !page.url && 'opacity-50 cursor-not-allowed'
            ]"
            v-html="page.label"
            :disabled="!page.url"
          />
        </nav>
      </div>
    </Card>
  </div>
</template>

<script setup>
import Card from '@/Components/Card.vue'
import { ref, reactive } from 'vue'
import { router, Link } from '@inertiajs/vue3'

const props = defineProps({
  data: Object,
  users: Array,
  filters: Object
})

const filtros = reactive({
  search: props.filters?.search || '',
  model_type: props.filters?.model_type || '',
  model_id: props.filters?.model_id || '',
  action: props.filters?.action || '',
  user_id: props.filters?.user_id || '',
  data_inicio: props.filters?.data_inicio || '',
  data_fim: props.filters?.data_fim || ''
})

let debounceTimeout = null

function buscar() {
  if (debounceTimeout) {
    clearTimeout(debounceTimeout)
  }

  debounceTimeout = setTimeout(() => {
    router.get('/audit-logs', { search: filtros.search }, {
      preserveState: true,
      preserveScroll: true
    })
  }, 500)
}

function limparBusca() {
  filtros.search = ''
  router.get('/audit-logs', {}, {
    preserveState: true,
    preserveScroll: true
  })
}

function filtrar() {
  router.get('/audit-logs', filtros, {
    preserveState: true,
    preserveScroll: true
  })
}

function limparFiltros() {
  filtros.model_type = ''
  filtros.model_id = ''
  filtros.action = ''
  filtros.user_id = ''
  filtros.data_inicio = ''
  filtros.data_fim = ''
  router.get('/audit-logs', {}, {
    preserveState: true,
    preserveScroll: true
  })
}

function formatarDataHora(dataHora) {
  if (!dataHora) return '-'
  const date = new Date(dataHora)
  return date.toLocaleString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

function getActionName(action) {
  const names = {
    created: 'Criação',
    updated: 'Atualização',
    deleted: 'Exclusão',
    viewed: 'Visualização'
  }
  return names[action] || action
}

function getModelName(modelType) {
  const names = {
    'App\\Models\\Cliente': 'Cliente',
    'App\\Models\\Venda': 'Venda',
    'App\\Models\\Produto': 'Produto',
    'App\\Models\\Servico': 'Serviço',
    'App\\Models\\Funcionario': 'Funcionário',
    'App\\Models\\Empresa': 'Empresa',
    'App\\Models\\Pagamento': 'Pagamento',
    'App\\Models\\Transacao': 'Transação',
    'App\\Models\\Meta': 'Meta',
    'App\\Models\\Recorrencia': 'Recorrência',
    'App\\Models\\User': 'Usuário'
  }
  return names[modelType] || modelType.split('\\').pop()
}
</script>

