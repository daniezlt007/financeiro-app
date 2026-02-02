<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import Card from '@/Components/Card.vue';

const props = defineProps({
  empresas: Array,
  permissionGroups: Object
});

function togglePermission(name) {
  const i = form.permissions.indexOf(name)
  if (i >= 0) {
    form.permissions = form.permissions.filter((p) => p !== name)
  } else {
    form.permissions = [...form.permissions, name]
  }
}

function toggleGroup(groupPerms, checked) {
  groupPerms.forEach((p) => {
    const i = form.permissions.indexOf(p.name)
    if (checked && i < 0) {
      form.permissions = [...form.permissions, p.name]
    } else if (!checked && i >= 0) {
      form.permissions = form.permissions.filter((x) => x !== p.name)
    }
  })
}

const form = useForm({
  name: '',
  nome_completo: '',
  email: '',
  password: '',
  password_confirmation: '',
  empresa_id: '',
  is_admin: false,
  permissions: []
});

const submit = () => {
  form.post('/users', {
    onSuccess: () => {
      form.reset();
    }
  });
};
</script>

<script>
export default { layout: null }
</script>

<template>
  <Head title="Novo Usuário" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Novo Usuário
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
        <Card title="Dados do Usuário" variant="elevated">
          <form @submit.prevent="submit" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nome (Login) *</label>
                <input 
                  v-model="form.name" 
                  type="text" 
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" 
                  required 
                  placeholder="Nome de usuário para login"
                />
                <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nome Completo</label>
                <input 
                  v-model="form.nome_completo" 
                  type="text" 
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" 
                  placeholder="Nome completo do usuário"
                />
                <div v-if="form.errors.nome_completo" class="text-red-500 text-sm mt-1">{{ form.errors.nome_completo }}</div>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                <input 
                  v-model="form.email" 
                  type="email" 
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" 
                  required 
                />
                <div v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</div>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Senha *</label>
                <input 
                  v-model="form.password" 
                  type="password" 
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" 
                  required 
                />
                <div v-if="form.errors.password" class="text-red-500 text-sm mt-1">{{ form.errors.password }}</div>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Confirmar Senha *</label>
                <input 
                  v-model="form.password_confirmation" 
                  type="password" 
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" 
                  required 
                />
              </div>
            </div>

            <div class="grid grid-cols-1 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Empresa</label>
                <select 
                  v-model="form.empresa_id" 
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                >
                  <option value="">Selecione uma empresa</option>
                  <option v-for="empresa in empresas" :key="empresa.id" :value="empresa.id">
                    {{ empresa.nome_fantasia }}
                  </option>
                </select>
                <div v-if="form.errors.empresa_id" class="text-red-500 text-sm mt-1">{{ form.errors.empresa_id }}</div>
              </div>
            </div>

            <div class="border-t border-gray-200 pt-6">
              <label class="flex items-center mb-4">
                <input 
                  v-model="form.is_admin" 
                  type="checkbox" 
                  class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                />
                <span class="ml-2 text-sm font-medium text-gray-700">Administrador (acesso total ao sistema)</span>
              </label>
              <p class="text-sm text-gray-500 mb-4">Ou selecione os acessos específicos abaixo:</p>
              
              <div class="space-y-6">
                <div 
                  v-for="(perms, groupName) in permissionGroups" 
                  :key="groupName" 
                  class="bg-gray-50 rounded-lg p-4"
                >
                  <div class="flex items-center justify-between mb-3">
                    <h4 class="text-sm font-semibold text-gray-800">{{ groupName }}</h4>
                    <label class="flex items-center text-xs text-gray-600">
                      <input 
                        type="checkbox" 
                        :checked="perms.every((p) => form.permissions.includes(p.name))"
                        @change="toggleGroup(perms, $event.target.checked)"
                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                      />
                      <span class="ml-1">Todos</span>
                    </label>
                  </div>
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <label 
                      v-for="perm in perms" 
                      :key="perm.name"
                      class="flex items-center text-sm text-gray-700 hover:bg-gray-100 rounded px-2 py-1 -mx-2 -my-1"
                    >
                      <input 
                        type="checkbox" 
                        :checked="form.permissions.includes(perm.name)"
                        @change="togglePermission(perm.name)"
                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                      />
                      <span class="ml-2">{{ perm.label }}</span>
                    </label>
                  </div>
                </div>
              </div>
            </div>

            <div class="flex justify-end space-x-3">
              <a 
                href="/users" 
                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors"
              >
                Cancelar
              </a>
              <button 
                type="submit" 
                :disabled="form.processing"
                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors disabled:opacity-50"
              >
                {{ form.processing ? 'Salvando...' : 'Salvar' }}
              </button>
            </div>
          </form>
        </Card>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

