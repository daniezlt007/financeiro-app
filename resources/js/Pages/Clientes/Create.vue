<template>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="text-center mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Novo Cliente</h1>
      <p class="mt-2 text-gray-600">Preencha os dados do novo cliente</p>
    </div>

    <Card title="Informações do Cliente" variant="elevated">
      <form id="cliente-form" @submit.prevent="submit" class="space-y-6">
        <!-- empresa_id -->
        <div v-if="isAdmin" class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Empresa</label>
            <select v-model="form.empresa_id" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
              <option value="" disabled>Selecione uma empresa…</option>
              <option v-for="e in empresas" :key="e.id" :value="e.id">
                {{ e.nome_fantasia }}
              </option>
            </select>
          </div>
          <div></div>
        </div>
        <div v-else class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
          <div class="text-sm text-blue-800">
            <strong>Empresa:</strong> {{ authUser?.empresa?.nome_fantasia || 'Empresa do usuário' }}
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Nome Completo *</label>
            <input v-model="form.nome_completo" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Nome completo" required />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">CPF/CNPJ</label>
            <input v-model="form.cpf_cnpj" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="CPF/CNPJ" />
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Telefone</label>
            <input v-model="form.telefone" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Telefone" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">E-mail</label>
            <input v-model="form.email" type="email" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="E-mail" />
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Endereço</label>
            <input v-model="form.endereco_completo" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Endereço completo" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Placa do Veículo</label>
            <input v-model="form.placa_veiculo" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Placa do veículo" />
          </div>
        </div>

      </form>
      
      <template #footer>
        <div class="flex justify-end gap-4">
          <button type="submit" form="cliente-form" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors" :disabled="form.processing">
            {{ form.processing ? 'Salvando...' : 'Salvar Cliente' }}
          </button>
        </div>
      </template>
    </Card>
  </div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3'
import { useForm } from '@inertiajs/vue3'
import Card from '@/Components/Card.vue'

// props vindas do controller
const props = defineProps({
  empresas: { type: Array, default: () => [] } // só vem preenchido para admin
})

// dados globais do usuário (Breeze já compartilha)
const page = usePage()
const authUser = page.props?.auth?.user ?? null
const isAdmin = !!authUser?.is_admin
const empresaIdPadrao = authUser?.empresa_id ?? ''

// Formulário usando Inertia
const form = useForm({
  empresa_id: isAdmin ? '' : empresaIdPadrao,
  nome_completo: '',
  cpf_cnpj: '',
  telefone: '',
  email: '',
  endereco_completo: '',
  placa_veiculo: ''
})

function submit() {
  form.post('/clientes')
}
</script>
