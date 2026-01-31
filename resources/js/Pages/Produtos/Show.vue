<template>
  <div class="p-6 max-w-3xl space-y-3">
    <h1 class="text-xl font-bold">Produtos — Detalhes</h1>
    <div><strong>Nome:</strong> {{ item.nome }}</div><div><strong>SKU:</strong> {{ item.sku }}</div><div><strong>Preço:</strong> {{ item.preco }}</div>
  </div>
</template>
<script setup>
defineProps({ item: Object })
</script>
