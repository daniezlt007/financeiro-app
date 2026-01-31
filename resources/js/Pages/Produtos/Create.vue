<template>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="text-center mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Novo Produto</h1>
      <p class="mt-2 text-gray-600">Cadastre um novo produto no sistema</p>
    </div>

    <Card title="Informações do Produto" variant="elevated">
      <form id="produto-form" method="post" action="/produtos" class="space-y-6">
        <input type="hidden" name="_token" :value="csrf">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Nome *</label>
            <input class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" name="nome" placeholder="Nome do produto" required />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">SKU</label>
            <input class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" name="sku" placeholder="Código SKU" />
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Preço *</label>
            <input type="number" step="0.01" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" name="preco" placeholder="0.00" required />
          </div>
        </div>

      </form>
      
      <template #footer>
        <div class="flex justify-end gap-4">
          <a href="/produtos" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition-colors">
            Cancelar
          </a>
          <button type="submit" form="produto-form" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
            Salvar Produto
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

