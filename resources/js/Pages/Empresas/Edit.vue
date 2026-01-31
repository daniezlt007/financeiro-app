<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import Card from '@/Components/Card.vue'

// 👇 agora "empresa" existe no template (sem usar props.)
const { empresa } = defineProps({
  empresa: { type: Object, required: true }
})

const form = useForm({
  nome_fantasia: empresa?.nome_fantasia ?? '',
  razao_social:  empresa?.razao_social  ?? '',
  cnpj:          empresa?.cnpj          ?? '',
  email:         empresa?.email         ?? '',
  telefone:      empresa?.telefone      ?? '',
  endereco:      empresa?.endereco      ?? '',
  status:        empresa?.status        ?? 'ATIVA',
})

function submit () {
  form.put(route('empresas.update', empresa.id))
}

function destroyEmpresa () {
  if (!confirm('Tem certeza que deseja excluir esta empresa? Esta ação não pode ser desfeita.')) return
  form.delete(route('empresas.destroy', empresa.id))
}
</script>

<template>
  <Head title="Editar Empresa" />
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8" v-if="empresa && empresa.id">
    <div class="text-center mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Editar Empresa</h1>
      <p class="mt-2 text-gray-600">Atualize as informações da empresa</p>
    </div>

    <Card title="Informações da Empresa" variant="elevated">
      <form @submit.prevent="submit" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Nome Fantasia *</label>
            <input v-model="form.nome_fantasia" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Nome fantasia da empresa" required />
            <div v-if="form.errors.nome_fantasia" class="text-red-600 text-xs mt-1">{{ form.errors.nome_fantasia }}</div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Razão Social *</label>
            <input v-model="form.razao_social" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Razão social da empresa" required />
            <div v-if="form.errors.razao_social" class="text-red-600 text-xs mt-1">{{ form.errors.razao_social }}</div>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">CNPJ</label>
            <input v-model="form.cnpj" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="00.000.000/0000-00" />
            <div v-if="form.errors.cnpj" class="text-red-600 text-xs mt-1">{{ form.errors.cnpj }}</div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Telefone</label>
            <input v-model="form.telefone" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="(00) 0000-0000" />
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">E-mail</label>
            <input v-model="form.email" type="email" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="empresa@exemplo.com" />
            <div v-if="form.errors.email" class="text-red-600 text-xs mt-1">{{ form.errors.email }}</div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select v-model="form.status" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
              <option value="1">Ativa</option>
              <option value="0">Inativa</option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Endereço</label>
            <input v-model="form.endereco" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Endereço completo da empresa" />
          </div>
        </div>
      </form>
      
      <template #footer>
        <div class="flex justify-between items-center">
          <button type="button" @click="destroyEmpresa" class="px-6 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors" :disabled="form.processing">
            Excluir Empresa
          </button>
          <div class="flex gap-4">
            <Link :href="route('empresas.index')" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition-colors">
              Cancelar
            </Link>
            <button type="submit" @click="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors" :disabled="form.processing">
              Salvar Alterações
            </button>
          </div>
        </div>
      </template>
    </Card>
  </div>

  <div v-else class="p-6 text-sm text-gray-500">Carregando…</div>
</template>
