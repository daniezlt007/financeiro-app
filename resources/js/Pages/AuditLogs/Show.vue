<template>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
      <a
        href="/audit-logs"
        class="text-dekra-800 hover:text-dekra-600 flex items-center gap-2"
      >
        ← Voltar para Logs
      </a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
      <div class="p-6 border-b border-gray-200">
        <h1 class="text-2xl font-bold text-gray-900">Detalhes do Log #{{ log.id }}</h1>
      </div>

      <div class="p-6 space-y-6">
        <!-- Informações Gerais -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Data/Hora</label>
            <p class="text-gray-900">{{ formatarDataHora(log.created_at) }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Usuário</label>
            <p class="text-gray-900">{{ log.user?.name || 'Sistema' }}</p>
            <p v-if="log.user?.email" class="text-sm text-gray-500">{{ log.user.email }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Ação</label>
            <span
              :class="[
                'inline-block px-3 py-1 rounded-full text-sm font-medium',
                log.action === 'created' ? 'bg-green-100 text-green-800' :
                log.action === 'updated' ? 'bg-blue-100 text-blue-800' :
                log.action === 'deleted' ? 'bg-red-100 text-red-800' :
                'bg-gray-100 text-gray-800'
              ]"
            >
              {{ getActionName(log.action) }}
            </span>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Modelo</label>
            <p class="text-gray-900">{{ getModelName(log.model_type) }}</p>
            <p class="text-sm text-gray-500">ID do Registro: {{ log.model_id || '-' }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Endereço IP</label>
            <p class="text-gray-900">{{ log.ip_address || '-' }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Navegador</label>
            <p class="text-gray-900 text-sm break-all">{{ log.user_agent || '-' }}</p>
          </div>
        </div>

        <!-- Valores Antigos -->
        <div v-if="log.old_values && Object.keys(JSON.parse(log.old_values)).length > 0" class="border-t pt-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-3">Valores Anteriores</h3>
          <div class="bg-gray-50 rounded-lg p-4 overflow-x-auto">
            <pre class="text-sm text-gray-800">{{ formatJSON(log.old_values) }}</pre>
          </div>
        </div>

        <!-- Valores Novos -->
        <div v-if="log.new_values && Object.keys(JSON.parse(log.new_values)).length > 0" class="border-t pt-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-3">Valores Novos</h3>
          <div class="bg-gray-50 rounded-lg p-4 overflow-x-auto">
            <pre class="text-sm text-gray-800">{{ formatJSON(log.new_values) }}</pre>
          </div>
        </div>

        <!-- Comparação de Alterações (para ação updated) -->
        <div v-if="log.action === 'updated' && log.old_values && log.new_values" class="border-t pt-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-3">Alterações Realizadas</h3>
          <div class="bg-yellow-50 rounded-lg p-4">
            <table class="min-w-full text-sm">
              <thead>
                <tr class="border-b border-yellow-200">
                  <th class="text-left py-2 font-medium text-gray-700">Campo</th>
                  <th class="text-left py-2 font-medium text-gray-700">Valor Anterior</th>
                  <th class="text-left py-2 font-medium text-gray-700">Valor Novo</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(value, key) in getChangedFields()"
                  :key="key"
                  class="border-b border-yellow-100 last:border-b-0"
                >
                  <td class="py-2 font-medium text-gray-900">{{ key }}</td>
                  <td class="py-2 text-gray-600">
                    <span class="bg-red-100 text-red-800 px-2 py-1 rounded">
                      {{ value.old }}
                    </span>
                  </td>
                  <td class="py-2 text-gray-600">
                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded">
                      {{ value.new }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  log: Object
})

function formatarDataHora(dataHora) {
  if (!dataHora) return '-'
  const date = new Date(dataHora)
  return date.toLocaleString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
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

function formatJSON(jsonString) {
  try {
    const obj = JSON.parse(jsonString)
    return JSON.stringify(obj, null, 2)
  } catch (e) {
    return jsonString
  }
}

function getChangedFields() {
  try {
    const oldValues = JSON.parse(props.log.old_values)
    const newValues = JSON.parse(props.log.new_values)
    const changes = {}

    for (const key in newValues) {
      if (oldValues[key] !== newValues[key]) {
        changes[key] = {
          old: oldValues[key] ?? '-',
          new: newValues[key] ?? '-'
        }
      }
    }

    return changes
  } catch (e) {
    return {}
  }
}
</script>


