<template>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="text-center mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Nova Empresa</h1>
      <p class="mt-2 text-gray-600">Cadastre uma nova empresa no sistema</p>
    </div>

    <Card title="Informações da Empresa" variant="elevated">
      <form id="empresa-form" method="post" action="/empresas" class="space-y-6">
        <input type="hidden" name="_token" :value="csrf">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Nome Fantasia *</label>
            <input class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" name="nome_fantasia" placeholder="Nome fantasia da empresa" required />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Razão Social *</label>
            <input class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" name="razao_social" placeholder="Razão social da empresa" required />
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">CNPJ</label>
            <input class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" name="cnpj" placeholder="00.000.000/0000-00" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Telefone</label>
            <input class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" name="telefone" placeholder="(00) 0000-0000" />
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">E-mail</label>
            <input type="email" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" name="email" placeholder="empresa@exemplo.com" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Endereço</label>
            <input class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" name="endereco" placeholder="Endereço completo da empresa" />
          </div>
        </div>

      </form>
      
      <template #footer>
        <div class="flex justify-end gap-4">
          <a href="/empresas" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition-colors">
            Cancelar
          </a>
          <button type="submit" form="empresa-form" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
            Salvar Empresa
          </button>
        </div>
      </template>
    </Card>
  </div>
</template>
<script setup>
import Card from '@/Components/Card.vue'

const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
</script>

