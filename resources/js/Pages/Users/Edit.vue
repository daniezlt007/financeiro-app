<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import Card from '@/Components/Card.vue';

const props = defineProps({
  item: Object,
  empresas: Array
});

const form = useForm({
  name: props.item.name,
  nome_completo: props.item.nome_completo || '',
  email: props.item.email,
  password: '',
  password_confirmation: '',
  empresa_id: props.item.empresa_id || '',
  is_admin: props.item.is_admin
});

const submit = () => {
  form.put(`/users/${props.item.id}`);
};
</script>

<script>
export default { layout: null }
</script>

<template>
  <Head title="Editar Usuário" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Editar Usuário
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
                <label class="block text-sm font-medium text-gray-700 mb-2">Nova Senha</label>
                <input 
                  v-model="form.password" 
                  type="password" 
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" 
                  placeholder="Deixe em branco para manter a senha atual"
                />
                <div v-if="form.errors.password" class="text-red-500 text-sm mt-1">{{ form.errors.password }}</div>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Confirmar Nova Senha</label>
                <input 
                  v-model="form.password_confirmation" 
                  type="password" 
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" 
                  placeholder="Confirme a nova senha"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
              
              <div>
                <label class="flex items-center">
                  <input 
                    v-model="form.is_admin" 
                    type="checkbox" 
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                  />
                  <span class="ml-2 text-sm text-gray-700">Administrador</span>
                </label>
                <div v-if="form.errors.is_admin" class="text-red-500 text-sm mt-1">{{ form.errors.is_admin }}</div>
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

