<template>
  <div class="p-6 max-w-4xl">
    <h1 class="text-xl font-bold mb-4">Editar Funcionário</h1>

    <form @submit.prevent="submit" class="space-y-6">
      <!-- empresa_id (sempre visível) -->
      <div>
        <label class="text-sm font-medium text-gray-700">Empresa</label>
        <select v-model="form.empresa_id" class="w-full border rounded p-2 mt-1" required>
          <option value="" disabled>Selecione…</option>
          <option v-for="e in empresas" :key="e.id" :value="e.id">
            {{ e.nome_fantasia }}
          </option>
        </select>
      </div>

      <!-- Informações Pessoais -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="text-sm font-medium text-gray-700">Nome *</label>
          <input v-model="form.nome_completo" class="w-full border rounded p-2 mt-1" placeholder="Nome" required />
        </div>
        <div>
          <label class="text-sm font-medium text-gray-700">Nome Completo</label>
          <input v-model="form.nome_extenso" class="w-full border rounded p-2 mt-1" placeholder="Nome completo do funcionário" />
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="text-sm font-medium text-gray-700">Telefone</label>
          <input v-model="form.telefone" class="w-full border rounded p-2 mt-1" placeholder="Telefone" />
        </div>
        <div></div>
      </div>

      <!-- Informações Profissionais -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="text-sm font-medium text-gray-700">Cargo *</label>
          <select v-model="form.cargo" class="w-full border rounded p-2 mt-1" required>
            <option value="VENDEDOR">Vendedor</option>
            <option value="GERENTE">Gerente</option>
            <option value="ADMINISTRATIVO">Administrativo</option>
            <option value="TECNICO">Técnico</option>
            <option value="ATENDENTE">Atendente</option>
            <option value="VISTORIADOR">Vistoriador</option>
            <option value="COORDENADOR">Coordenador</option>
            <option value="OUTRO">Outro</option>
          </select>
        </div>
        <div>
          <label class="text-sm font-medium text-gray-700">Status *</label>
          <select v-model="form.status" class="w-full border rounded p-2 mt-1" required>
            <option value="ATIVO">Ativo</option>
            <option value="INATIVO">Inativo</option>
            <option value="FERIAS">Férias</option>
            <option value="AFASTADO">Afastado</option>
          </select>
        </div>
      </div>

      <div>
        <label class="text-sm font-medium text-gray-700">Observações</label>
        <textarea v-model="form.observacoes" class="w-full border rounded p-2 mt-1" rows="3" placeholder="Observações adicionais"></textarea>
      </div>

      <div class="flex justify-end space-x-3">
        <Link :href="`/funcionarios`" class="px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50">
          Cancelar
        </Link>
        <button type="submit" class="px-4 py-2 bg-black text-white rounded hover:bg-gray-800" :disabled="form.processing">
          {{ form.processing ? 'Salvando...' : 'Atualizar Funcionário' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3'
import { useForm, Link } from '@inertiajs/vue3'

// props vindas do controller
const props = defineProps({
  item: Object,
  empresas: { type: Array, default: () => [] }
})

// dados globais do usuário (Breeze já compartilha)
const page = usePage()
const authUser = page.props?.auth?.user ?? null
const isAdmin = !!authUser?.is_admin

// Formulário usando Inertia
const form = useForm({
  empresa_id: props.item.empresa_id,
  nome_completo: props.item.nome_completo,
  nome_extenso: props.item.nome_extenso || '',
  telefone: props.item.telefone || '',
  cargo: props.item.cargo,
  status: props.item.status,
  observacoes: props.item.observacoes || ''
})

function submit() {
  form.put(`/funcionarios/${props.item.id}`)
}
</script>
