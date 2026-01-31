<template>
  <div class="p-6 max-w-3xl">
    <h1 class="text-xl font-bold mb-4">Editar Cliente</h1>
    <form :action="`/clientes/${item.id}`" method="post" class="space-y-4">
      <input type="hidden" name="_token" :value="csrf">
      <input type="hidden" name="_method" value="PUT">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div>
        <label class="text-sm">Nome Completo</label>
        <input class="w-full border rounded p-2" name="nome_completo" v-model="item.nome_completo"  />
      </div>
<div>
        <label class="text-sm">CPF/CNPJ</label>
        <input class="w-full border rounded p-2" name="cpf_cnpj" v-model="item.cpf_cnpj"  />
      </div>
<div>
        <label class="text-sm">Telefone</label>
        <input class="w-full border rounded p-2" name="telefone" v-model="item.telefone"  />
      </div>
<div>
        <label class="text-sm">E-mail</label>
        <input class="w-full border rounded p-2" name="email" v-model="item.email"  />
      </div>
<div>
        <label class="text-sm">Endereço Completo</label>
        <input class="w-full border rounded p-2" name="endereco_completo" v-model="item.endereco_completo"  />
      </div>
<div>
        <label class="text-sm">Placa do Veículo</label>
        <input class="w-full border rounded p-2" name="placa_veiculo" v-model="item.placa_veiculo"  />
      </div>

      </div>
      <button class="px-4 py-2 bg-black text-white rounded">Salvar alterações</button>
    </form>
  </div>
</template>
<script setup>
defineProps({ item: Object })
const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
</script>
