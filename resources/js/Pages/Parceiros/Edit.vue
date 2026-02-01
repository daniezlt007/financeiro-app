<template>
  <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Editar Parceiro</h1>
      <p class="text-gray-600 mt-1">Atualize os dados do parceiro</p>
    </div>

    <Card title="Dados do Parceiro" variant="elevated">
      <form @submit.prevent="form.put(route('parceiros.update', item.id))" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Nome *</label>
          <input
            v-model="form.nome_parceiro"
            type="text"
            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            required
          />
          <p v-if="form.errors.nome_parceiro" class="mt-1 text-sm text-red-600">{{ form.errors.nome_parceiro }}</p>
        </div>

        <div class="flex gap-3 pt-4">
          <button
            type="submit"
            :disabled="form.processing"
            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
          >
            {{ form.processing ? 'Salvando...' : 'Salvar' }}
          </button>
          <Link :href="route('parceiros.index')" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
            Cancelar
          </Link>
        </div>
      </form>
    </Card>
  </div>
</template>
<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import Card from '@/Components/Card.vue'

const props = defineProps({ item: Object })
const form = useForm({ nome_parceiro: props.item.nome_parceiro })
</script>
