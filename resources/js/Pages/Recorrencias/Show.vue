<template>
  <div class="p-6 max-w-3xl space-y-3">
    <h1 class="text-xl font-bold">Recorrencias — Detalhes</h1>
    <div><strong>Tipo:</strong> {{ item.tipo }}</div><div><strong>Periodicidade:</strong> {{ item.periodicidade }}</div><div><strong>Valor:</strong> {{ item.valor }}</div><div><strong>Próxima Execução:</strong> {{ item.proxima_execucao }}</div><div><strong>Ativo (0/1):</strong> {{ item.ativo }}</div>
  </div>
</template>
<script setup>
defineProps({ item: Object })
</script>
