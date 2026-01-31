<template>
  <div class="p-6 max-w-3xl space-y-3">
    <h1 class="text-xl font-bold">Servicos — Detalhes</h1>
    <div><strong>Tipo de Serviço:</strong> {{ item.tipo_servico }}</div><div><strong>Preço Base:</strong> {{ item.preco_base }}</div><div><strong>% Comissão:</strong> {{ item.comissao_percentual }}</div>
  </div>
</template>
<script setup>
defineProps({ item: Object })
</script>
