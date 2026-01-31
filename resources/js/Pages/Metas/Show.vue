<template>
  <div class="p-6 max-w-3xl space-y-3">
    <h1 class="text-xl font-bold">Metas — Detalhes</h1>
    <div><strong>Mês:</strong> {{ item.mes }}</div><div><strong>Ano:</strong> {{ item.ano }}</div><div><strong>Meta Faturamento:</strong> {{ item.meta_faturamento }}</div><div><strong>Meta Produção Varejo:</strong> {{ item.meta_producao_varejo }}</div><div><strong>Meta Produção Atacado:</strong> {{ item.meta_producao_atacado }}</div>
  </div>
</template>
<script setup>
defineProps({ item: Object })
</script>
