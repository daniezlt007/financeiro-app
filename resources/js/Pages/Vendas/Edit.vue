<template>
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="text-center mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Editar Venda</h1>
      <p class="mt-2 text-gray-600">Atualize os dados da venda</p>
    </div>
    
    <form @submit.prevent="submit" class="space-y-8">
      <!-- Informações Básicas -->
      <Card title="Informações Básicas" variant="elevated">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Data *</label>
            <input v-model="form.data_venda" type="date" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Consumidor *</label>
            <select v-model="form.consumidor_tipo" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
              <option value="CONSUMIDOR FINAL">CONSUMIDOR FINAL</option>
              <option value="PARCEIRO FATURADO">PARCEIRO FATURADO</option>
              <option value="PARCEIRO PRÉ-PAGO">PARCEIRO PRÉ-PAGO</option>
              <option value="CONTRATO CORPORATIVO">CONTRATO CORPORATIVO</option>
              <option value="CORTESIA FUNCIONÁRIO">CORTESIA FUNCIONÁRIO</option>
            </select>
          </div>
        </div>
      </Card>

      <!-- Dados do Cliente -->
      <Card title="Dados do Cliente" variant="elevated">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Nome Completo *</label>
            <input v-model="form.cliente_nome_completo" type="text" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">CPF/CNPJ</label>
            <input v-model="form.cliente_cpf_cnpj" type="text" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Telefone</label>
            <input v-model="form.cliente_telefone" type="text" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Placa do Veículo</label>
            <input v-model="form.cliente_placa" type="text" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Chassi</label>
            <input v-model="form.cliente_chassi" type="text" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
          </div>
          <div class="relative">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Parceiro
              <span class="text-xs text-gray-500 ml-1">(digite para buscar parceiros cadastrados)</span>
            </label>
            <div class="relative">
              <input 
                v-model="form.parceiro" 
                type="text" 
                @input="buscarParceirosPorNome"
                @focus="showParceirosSuggestions = true"
                @blur="hideParceirosSuggestions"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" 
                placeholder="Digite o nome do parceiro"
              />
              <div v-if="buscandoParceiro" class="absolute right-3 top-1/2 -translate-y-1/2">
                <svg class="animate-spin h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              </div>
            </div>
            <div v-if="showParceirosSuggestions && parceirosSugeridos.length > 0" class="absolute z-50 mt-1 w-full bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
              <div 
                v-for="p in parceirosSugeridos" 
                :key="p.id"
                @mousedown.prevent="selecionarParceiro(p)"
                class="px-3 py-2 hover:bg-indigo-50 cursor-pointer border-b last:border-b-0"
              >
                <div class="font-medium text-gray-900">{{ p.nome_parceiro }}</div>
              </div>
            </div>
          </div>
        </div>
      </Card>

      <!-- Informações de Pagamento e Venda -->
      <Card title="Informações de Pagamento e Venda" variant="elevated">
        <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
          <div class="flex items-center">
            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-sm text-blue-800">
              <strong>Informação:</strong> O pagamento vinculado será atualizado automaticamente quando a venda for salva.
            </p>
          </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">% Desconto</label>
            <input v-model="form.percentual_desconto" type="number" min="0" max="100" step="0.01" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Forma de Pagamento</label>
            <select v-model="form.forma_pagamento" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
              <option value="DINHEIRO">DINHEIRO</option>
              <option value="PIX">PIX</option>
              <option value="CREDITO">CRÉDITO</option>
              <option value="DEBITO">DÉBITO</option>
              <option value="BOLETO">BOLETO</option>
              <option value="TRANSFERENCIA">TRANSFERENCIA</option>
              <option value="OUTRO">OUTRO</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Vistoriador Responsável</label>
            <select v-model="form.funcionario_id" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
              <option :value="null">Selecione um funcionário</option>
              <option v-for="funcionario in funcionarios" :key="funcionario.id" :value="funcionario.id">
                {{ funcionario.nome_completo }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Subtotal</label>
            <input :value="subtotal" type="number" step="0.01" min="0" class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-100 text-gray-700" readonly />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Valor Total (com desconto)</label>
            <input :value="valorTotal" type="number" step="0.01" min="0" class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-100 text-gray-700 font-semibold" readonly />
          </div>
        </div>
      </Card>

      <!-- Itens da Venda -->
      <Card title="Itens da Venda" variant="elevated">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end mb-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
            <select v-model="novo.tipo" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
              <option value="SERVICO">Serviço</option>
              <option value="PRODUTO">Produto</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Item</label>
            <select v-model.number="novo.id" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
              <option :value="null">Selecione</option>
              <option v-for="opt in (novo.tipo==='SERVICO'?servicos:produtos)" :key="opt.id" :value="opt.id">
                {{ novo.tipo==='SERVICO'?opt.tipo_servico:opt.nome }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Quantidade</label>
            <input v-model.number="novo.qtde" type="number" step="1" min="1" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Qtde" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Valor Unitário</label>
            <input v-model.number="novo.valor" type="number" step="0.01" min="0" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Valor unit." />
          </div>
          <div>
            <button @click.prevent="add" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
              Adicionar
            </button>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-sm border border-gray-200 rounded-lg">
            <thead class="bg-gray-50">
              <tr>
                <th class="text-left p-3 border-b">Tipo</th>
                <th class="text-left p-3 border-b">Item</th>
                <th class="text-center p-3 border-b">Qtde</th>
                <th class="text-right p-3 border-b">Valor Unit.</th>
                <th class="text-center p-3 border-b">Ação</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(i,idx) in itens" :key="idx" class="border-b last:border-b-0">
                <td class="p-3">{{ i.tipo_item }}</td>
                <td class="p-3">{{ i.nome }}</td>
                <td class="p-3 text-center">{{ i.qtde }}</td>
                <td class="p-3 text-right">R$ {{ i.valor_unitario.toLocaleString('pt-BR',{minimumFractionDigits:2}) }}</td>
                <td class="p-3 text-center">
                  <button 
                    @click.prevent="remover(idx)" 
                    type="button"
                    class="text-red-600 hover:text-red-800 font-medium text-sm px-2 py-1 rounded hover:bg-red-50"
                  >
                    Remover
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </Card>

      <Card>
        <template #footer>
          <div class="flex justify-center gap-3">
            <a href="/vendas" class="px-8 py-3 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors text-lg font-medium">
              Cancelar
            </a>
            <button type="submit" class="px-8 py-3 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors text-lg font-medium" :disabled="form.processing">
              {{ form.processing ? 'Salvando...' : 'Atualizar Venda' }}
            </button>
      </div>
        </template>
      </Card>
    </form>
  </div>
</template>
<script setup>
import { reactive, computed, onMounted, ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import Card from '@/Components/Card.vue'
import axios from 'axios'

const props = defineProps({ item:Object, funcionarios:Array, servicos:Array, produtos:Array, parceiros:Array })

const buscandoParceiro = ref(false)
const parceirosSugeridos = ref([])
const showParceirosSuggestions = ref(false)
let debounceTimeoutParceiro = null

const itens = reactive([])
const novo = reactive({ tipo:'SERVICO', id:null, qtde:1, valor:0 })

// Populando itens com os dados da venda atual
onMounted(() => {
  if (props.item.itens) {
    props.item.itens.forEach(item => {
      const nome = item.tipo_item === 'SERVICO'
        ? (item.servico?.tipo_servico || 'Serviço')
        : (item.produto?.nome || 'Produto')
      itens.push({
        tipo_item: item.tipo_item,
        id: item.tipo_item === 'SERVICO' ? item.servico_id : item.produto_id,
        qtde: item.qtde,
        valor_unitario: item.valor_unitario,
        nome
      })
    })
  }
})

const payloadItens = computed(()=> itens.map(i=>({ tipo_item:i.tipo_item, id:i.id, qtde:i.qtde, valor_unitario:i.valor_unitario })))

// Calcular subtotal (sem desconto)
const subtotal = computed(() => {
  return itens.reduce((total, item) => {
    return total + (item.qtde * item.valor_unitario)
  }, 0).toFixed(2)
})

// Calcular valor total com desconto aplicado
const valorTotal = computed(() => {
  const subtotalValue = parseFloat(subtotal.value)
  const desconto = form.percentual_desconto || 0
  const valorComDesconto = subtotalValue - (subtotalValue * (desconto / 100))
  
  return valorComDesconto.toFixed(2)
})

// Formulário usando Inertia
const form = useForm({
  data_venda: props.item.data?.split(' ')[0] || '',
  cliente_nome_completo: props.item.cliente_nome_completo || '',
  cliente_cpf_cnpj: props.item.cliente_cpf_cnpj || '',
  cliente_telefone: props.item.cliente_telefone || '',
  cliente_placa: props.item.cliente_placa || '',
  cliente_chassi: props.item.cliente_chassi || '',
  parceiro: props.item.parceiro || '',
  consumidor_tipo: props.item.consumidor_tipo || 'CONSUMIDOR FINAL',
  funcionario_id: props.item.funcionario_id || null,
  valor_total: props.item.valor_total || '',
  percentual_desconto: props.item.percentual_desconto || 0,
  comissao_venda: props.item.comissao_venda || 0,
  forma_pagamento: props.item.forma_pagamento || 'DINHEIRO',
  itens: []
})

function add(){
  if(!novo.id || !novo.valor) return
  const nome = novo.tipo==='SERVICO'
    ? (props.servicos.find(s=>s.id===novo.id)?.tipo_servico || 'Serviço')
    : (props.produtos.find(p=>p.id===novo.id)?.nome || 'Produto')
  itens.push({ tipo_item: novo.tipo, id: novo.id, qtde: novo.qtde, valor_unitario: novo.valor, nome })
  novo.id=null; novo.qtde=1; novo.valor=0
}

function remover(idx) {
  itens.splice(idx, 1)
}

function submit() {
  // Preparar dados para envio
  const dados = {
    data: form.data_venda,
    cliente_nome_completo: form.cliente_nome_completo,
    cliente_cpf_cnpj: form.cliente_cpf_cnpj,
    cliente_telefone: form.cliente_telefone,
    cliente_placa: form.cliente_placa,
    cliente_chassi: form.cliente_chassi,
    parceiro: form.parceiro,
    consumidor_tipo: form.consumidor_tipo,
    funcionario_id: form.funcionario_id,
    valor_total: valorTotal.value,
    percentual_desconto: form.percentual_desconto || 0,
    comissao_venda: form.comissao_venda || 0,
    forma_pagamento: form.forma_pagamento,
    itens: payloadItens.value
  }
  
  // Enviar usando form.transform
  form.transform(() => dados).put(`/vendas/${props.item.id}`)
}

function buscarParceirosPorNome() {
  if (debounceTimeoutParceiro) clearTimeout(debounceTimeoutParceiro)
  const nome = form.parceiro?.trim()
  if (!nome || nome.length < 2) {
    parceirosSugeridos.value = []
    showParceirosSuggestions.value = false
    return
  }
  debounceTimeoutParceiro = setTimeout(async () => {
    buscandoParceiro.value = true
    try {
      const response = await axios.get(route('parceiros.buscarPorNome'), { params: { nome } })
      parceirosSugeridos.value = response.data.parceiros || []
      showParceirosSuggestions.value = true
    } catch (error) {
      console.error('Erro ao buscar parceiros:', error)
      parceirosSugeridos.value = []
    } finally {
      buscandoParceiro.value = false
    }
  }, 300)
}

function selecionarParceiro(p) {
  form.parceiro = p.nome_parceiro
  parceirosSugeridos.value = []
  showParceirosSuggestions.value = false
}

function hideParceirosSuggestions() {
  setTimeout(() => {
    showParceirosSuggestions.value = false
  }, 200)
}
</script>
