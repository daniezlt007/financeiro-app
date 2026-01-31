<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
  item: { type: Object, required: true }
})

const form = useForm({
  tipo_servico: props.item?.tipo_servico ?? '',
  preco_base: props.item?.preco_base ?? '',
  comissao_percentual: props.item?.comissao_percentual ?? '',
})

function submit () {
  form.put(route('servicos.update', props.item.id))
}
</script>

<template>
  <Head title="Editar Serviço" />
  <div class="p-6 max-w-3xl space-y-4">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-semibold">Editar Serviço</h1>
      <Link :href="route('servicos.index')" class="text-sm text-gray-600 hover:underline">Voltar</Link>
    </div>

    <form @submit.prevent="submit" class="space-y-4">
      <!-- empresa fixa em 1 (desativado por enquanto) -->
      <div class="p-3 bg-gray-50 border border-gray-200 rounded mb-4">
        <div class="text-sm text-gray-600">
          <strong>Empresa:</strong> Empresa 1 (fixo)
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div>
          <label class="text-sm">Tipo de Serviço *</label>
          <input v-model="form.tipo_servico" class="w-full border rounded p-2" required />
          <div v-if="form.errors.tipo_servico" class="text-red-600 text-xs mt-1">{{ form.errors.tipo_servico }}</div>
        </div>
        <div>
          <label class="text-sm">Preço Base</label>
          <input v-model="form.preco_base" type="number" step="0.01" min="0" class="w-full border rounded p-2" />
          <div v-if="form.errors.preco_base" class="text-red-600 text-xs mt-1">{{ form.errors.preco_base }}</div>
        </div>
        <div>
          <label class="text-sm">Comissão Percentual</label>
          <input v-model="form.comissao_percentual" type="number" step="0.01" min="0" max="100" class="w-full border rounded p-2" />
          <div v-if="form.errors.comissao_percentual" class="text-red-600 text-xs mt-1">{{ form.errors.comissao_percentual }}</div>
        </div>
      </div>

      <div class="flex justify-end gap-2">
        <button type="submit" class="px-4 py-2 rounded bg-indigo-600 text-white" :disabled="form.processing">
          Salvar
        </button>
      </div>
    </form>
  </div>
</template>
