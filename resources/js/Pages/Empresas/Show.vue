<script setup>
import { Head, Link } from '@inertiajs/vue3'

// desestrutura a prop para usar direto no template
const { empresa } = defineProps({
  empresa: { type: Object, required: true, default: () => ({}) }
})
</script>

<template>
  <Head title="Empresa — Detalhes" />

  <!-- guarda caso o HMR ainda não tenha hidratado -->
  <div v-if="empresa && empresa.id" class="p-6 max-w-3xl space-y-3">
    <div class="flex items-center justify-between">
      <h1 class="text-xl font-bold">Empresas — Detalhes</h1>
      <Link :href="route('empresas.index')" class="text-sm text-gray-600 hover:underline">Voltar</Link>
    </div>

    <div><strong>Nome Fantasia:</strong> {{ empresa.nome_fantasia }}</div>
    <div><strong>Razão Social:</strong> {{ empresa.razao_social }}</div>
    <div><strong>CNPJ:</strong> {{ empresa.cnpj ?? '—' }}</div>
    <div><strong>Telefone:</strong> {{ empresa.telefone ?? '—' }}</div>
    <div><strong>E-mail:</strong> {{ empresa.email ?? '—' }}</div>
    <div><strong>Endereço:</strong> {{ empresa.endereco ?? '—' }}</div>
    <div><strong>Status:</strong> {{ (empresa.status ?? 0) ? 'ATIVA' : 'INATIVA' }}</div>
  </div>

  <div v-else class="p-6 text-sm text-gray-500">Carregando…</div>
</template>
