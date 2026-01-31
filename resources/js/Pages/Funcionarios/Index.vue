<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">Funcionários</h1>
      <Link href="/funcionarios/create" class="px-4 py-2 bg-black text-white rounded hover:bg-gray-800">
        Novo Funcionário
      </Link>
    </div>

    <!-- Mensagens de sucesso/erro -->
    <div v-if="$page.props.flash?.success" class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
      {{ $page.props.flash.success }}
    </div>
    <div v-if="$page.props.flash?.error" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
      {{ $page.props.flash.error }}
    </div>

    <!-- Tabela de funcionários -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome Completo</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telefone</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cargo</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Empresa</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="funcionario in (data?.data || [])" :key="funcionario.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-gray-900">{{ funcionario.nome_completo }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-900">{{ funcionario.nome_extenso || '-' }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ funcionario.telefone || '-' }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ funcionario.cargo }}</td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span :class="getStatusClass(funcionario.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                {{ funcionario.status }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ funcionario.empresa?.nome_fantasia || '-' }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <Link :href="`/funcionarios/${funcionario.id}`" class="text-indigo-600 hover:text-indigo-900 mr-3">
                Ver
              </Link>
              <Link :href="`/funcionarios/${funcionario.id}/edit`" class="text-indigo-600 hover:text-indigo-900 mr-3">
                Editar
              </Link>
              <button @click="confirmDelete(funcionario)" class="text-red-600 hover:text-red-900">
                Excluir
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Paginação -->
    <div v-if="data?.links" class="mt-6">
      <nav class="flex items-center justify-between">
        <div class="flex-1 flex justify-between sm:hidden">
          <Link v-if="data?.prev_page_url" :href="data.prev_page_url" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
            Anterior
          </Link>
          <Link v-if="data?.next_page_url" :href="data.next_page_url" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
            Próximo
          </Link>
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
          <div>
            <p class="text-sm text-gray-700">
              Mostrando {{ data?.from || 0 }} até {{ data?.to || 0 }} de {{ data?.total || 0 }} resultados
            </p>
          </div>
          <div>
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
              <template v-for="link in (data?.links || [])" :key="link.label">
                <Link v-if="link.url" :href="link.url" 
                      :class="link.active ? 'bg-indigo-50 border-indigo-500 text-indigo-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'"
                      class="relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                  <span v-html="link.label"></span>
                </Link>
                <span v-else class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                  <span v-html="link.label"></span>
                </span>
              </template>
            </nav>
          </div>
        </div>
      </nav>
    </div>

    <!-- Modal de confirmação de exclusão -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
          <h3 class="text-lg font-medium text-gray-900">Confirmar Exclusão</h3>
          <div class="mt-2 px-7 py-3">
            <p class="text-sm text-gray-500">
              Tem certeza que deseja excluir o funcionário <strong>{{ funcionarioToDelete?.nome_completo }}</strong>?
            </p>
          </div>
          <div class="items-center px-4 py-3">
            <button @click="deleteFuncionario" class="px-4 py-2 bg-red-500 text-white text-base font-medium rounded-md shadow-sm hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300 mr-2">
              Excluir
            </button>
            <button @click="showDeleteModal = false" class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md shadow-sm hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
              Cancelar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'

const props = defineProps({
  data: Object
})

const showDeleteModal = ref(false)
const funcionarioToDelete = ref(null)

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

function confirmDelete(funcionario) {
  funcionarioToDelete.value = funcionario
  showDeleteModal.value = true
}

function deleteFuncionario() {
  if (funcionarioToDelete.value) {
    router.delete(`/funcionarios/${funcionarioToDelete.value.id}`)
    showDeleteModal.value = false
    funcionarioToDelete.value = null
  }
}
</script>
