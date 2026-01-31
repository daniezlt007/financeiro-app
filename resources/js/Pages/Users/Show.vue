<template>
  <div class="p-6 max-w-3xl space-y-4">
    <div class="flex items-center justify-between">
      <h1 class="text-xl font-bold">Usuário #{{ item.id }}</h1>
      <div class="flex space-x-2">
        <a :href="`/users/${item.id}/edit`" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors">
          Editar
        </a>
        <a href="/users" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors">
          Voltar
        </a>
      </div>
    </div>
    
    <!-- Informações Gerais -->
    <div class="bg-gray-50 p-4 rounded-lg">
      <h3 class="font-semibold mb-3">Informações do Usuário</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div><strong>Nome:</strong> {{ item.name }}</div>
        <div><strong>Email:</strong> {{ item.email }}</div>
        <div>
          <strong>Tipo:</strong> 
          <span :class="[
            'ml-2 px-2 py-1 rounded-full text-xs font-medium',
            item.is_admin 
              ? 'bg-purple-100 text-purple-800' 
              : 'bg-blue-100 text-blue-800'
          ]">
            {{ item.is_admin ? 'Administrador' : 'Funcionário' }}
          </span>
        </div>
        <div><strong>Empresa:</strong> {{ item.empresa?.nome_fantasia || 'Não associado' }}</div>
        <div><strong>Criado em:</strong> {{ new Date(item.created_at).toLocaleDateString('pt-BR') }}</div>
        <div><strong>Última atualização:</strong> {{ new Date(item.updated_at).toLocaleDateString('pt-BR') }}</div>
      </div>
    </div>

    <!-- Estatísticas -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="bg-white border rounded-lg p-4">
        <h4 class="font-semibold text-gray-800 mb-2">Status da Conta</h4>
        <div class="space-y-2">
          <div class="flex justify-between">
            <span>Verificado:</span>
            <span :class="item.email_verified_at ? 'text-green-600' : 'text-red-600'">
              {{ item.email_verified_at ? 'Sim' : 'Não' }}
            </span>
          </div>
          <div class="flex justify-between">
            <span>Ativo:</span>
            <span class="text-green-600">Sim</span>
          </div>
        </div>
      </div>

      <div class="bg-white border rounded-lg p-4">
        <h4 class="font-semibold text-gray-800 mb-2">Permissões</h4>
        <div class="space-y-1">
          <div v-if="item.is_admin" class="text-purple-600">• Administrador do Sistema</div>
          <div v-else class="text-blue-600">• Funcionário</div>
          <div v-if="item.empresa_id" class="text-green-600">• Associado a Empresa</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'

defineProps({
  item: Object
})
</script>

