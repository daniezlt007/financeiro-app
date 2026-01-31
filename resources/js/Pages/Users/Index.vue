<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-8">
      <div class="text-center flex-1">
        <h1 class="text-3xl font-bold text-gray-900">Usuários</h1>
        <p class="mt-2 text-gray-600">Gerencie os usuários do sistema</p>
      </div>
      <a href="/users/create" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
        + Novo Usuário
      </a>
    </div>
    
    <Card title="Lista de Usuários" variant="elevated">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="text-left p-3 border-b font-medium text-gray-900">Nome</th>
              <th class="text-left p-3 border-b font-medium text-gray-900">Nome Completo</th>
              <th class="text-left p-3 border-b font-medium text-gray-900">Email</th>
              <th class="text-left p-3 border-b font-medium text-gray-900">Tipo</th>
              <th class="text-left p-3 border-b font-medium text-gray-900">Empresa</th>
              <th class="text-center p-3 border-b font-medium text-gray-900">Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in data.data" :key="user.id" class="border-b">
              <td class="p-3">{{ user.name }}</td>
              <td class="p-3">{{ user.nome_completo || '-' }}</td>
              <td class="p-3">{{ user.email }}</td>
              <td class="p-3">
                <span :class="[
                  'px-2 py-1 rounded-full text-xs font-medium',
                  user.is_admin 
                    ? 'bg-purple-100 text-purple-800' 
                    : 'bg-blue-100 text-blue-800'
                ]">
                  {{ user.is_admin ? 'Administrador' : 'Funcionário' }}
                </span>
              </td>
              <td class="p-3">{{ user.empresa?.nome_fantasia || '-' }}</td>
              <td class="p-3 text-center">
                <div class="flex justify-center space-x-2">
                  <a :href="`/users/${user.id}`" class="text-indigo-600 hover:text-indigo-900 text-sm">Ver</a>
                  <a :href="`/users/${user.id}/edit`" class="text-green-600 hover:text-green-900 text-sm">Editar</a>
                  <button 
                    @click="deleteUser(user.id)" 
                    :disabled="user.id === $page.props.auth.user.id"
                    :class="[
                      'text-sm',
                      user.id === $page.props.auth.user.id 
                        ? 'text-gray-400 cursor-not-allowed' 
                        : 'text-red-600 hover:text-red-900'
                    ]"
                  >
                    Excluir
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginação -->
      <div v-if="data.links" class="border-t border-gray-200 mt-4 pt-4">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Mostrando {{ data.from }} até {{ data.to }} de {{ data.total }} resultados
          </div>
          <div class="flex space-x-1">
            <template v-for="link in data.links" :key="link.label">
              <Link
                v-if="link.url"
                :href="link.url"
                v-html="link.label"
                :class="[
                  'px-3 py-1 rounded text-sm',
                  link.active 
                    ? 'bg-indigo-600 text-white' 
                    : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                ]"
              />
              <span
                v-else
                v-html="link.label"
                :class="[
                  'px-3 py-1 rounded text-sm opacity-50 cursor-not-allowed bg-gray-100'
                ]"
              />
            </template>
          </div>
        </div>
      </div>
    </Card>
  </div>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import Card from '@/Components/Card.vue'

defineProps({
  data: Object
})

function deleteUser(userId) {
  if (confirm('Tem certeza que deseja excluir este usuário?')) {
    router.delete(`/users/${userId}`)
  }
}
</script>
