<template>
  <div class="p-6 max-w-3xl space-y-3">
    <h1 class="text-xl font-bold">Clientes — Detalhes</h1>
    <div><strong>Nome Completo:</strong> {{ item.nome_completo }}</div><div><strong>CPF/CNPJ:</strong> {{ item.cpf_cnpj }}</div><div><strong>Telefone:</strong> {{ item.telefone }}</div><div><strong>E-mail:</strong> {{ item.email }}</div><div><strong>Endereço Completo:</strong> {{ item.endereco_completo }}</div><div><strong>Placa do Veículo:</strong> {{ item.placa_veiculo }}</div>
  </div>
</template>
<script setup>
defineProps({ item: Object })
</script>
