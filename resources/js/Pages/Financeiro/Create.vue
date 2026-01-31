<template>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Nova Transação</h1>
      <p class="mt-2 text-gray-600">Registre uma nova entrada ou saída financeira</p>
    </div>

    <Card title="Dados da Transação" variant="elevated">
      <form @submit.prevent="salvar" class="space-y-6">
        <!-- Tipo -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Tipo <span class="text-red-500">*</span>
          </label>
          <div class="flex gap-4">
            <label class="flex items-center cursor-pointer">
              <input 
                v-model="form.tipo" 
                type="radio" 
                value="ENTRADA" 
                class="mr-2 text-green-600 focus:ring-green-500"
              />
              <span class="text-sm font-medium text-gray-700">Entrada</span>
            </label>
            <label class="flex items-center cursor-pointer">
              <input 
                v-model="form.tipo" 
                type="radio" 
                value="SAIDA" 
                class="mr-2 text-red-600 focus:ring-red-500"
              />
              <span class="text-sm font-medium text-gray-700">Saída</span>
            </label>
          </div>
          <span v-if="errors.tipo" class="text-sm text-red-600">{{ errors.tipo }}</span>
        </div>

        <!-- Categoria -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Categoria <span class="text-red-500">*</span>
          </label>
          <select 
            v-model="form.categoria" 
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-dekra-500 focus:ring-dekra-500"
            :disabled="!form.tipo"
          >
            <option value="">Selecione uma categoria</option>
            <optgroup v-if="form.tipo === 'ENTRADA'" label="Entradas">
              <option v-for="(label, key) in categorias_entrada" :key="key" :value="key">
                {{ label }}
              </option>
            </optgroup>
            <optgroup v-if="form.tipo === 'SAIDA'" label="Saídas">
              <option v-for="(label, key) in categorias_saida" :key="key" :value="key">
                {{ label }}
              </option>
            </optgroup>
          </select>
          <span v-if="errors.categoria" class="text-sm text-red-600">{{ errors.categoria }}</span>
        </div>

        <!-- Data e Valor -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Data <span class="text-red-500">*</span>
            </label>
            <input 
              v-model="form.data" 
              type="date" 
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-dekra-500 focus:ring-dekra-500"
            />
            <span v-if="errors.data" class="text-sm text-red-600">{{ errors.data }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Valor <span class="text-red-500">*</span>
            </label>
            <input 
              v-model="form.valor" 
              type="number" 
              step="0.01" 
              min="0"
              placeholder="0,00"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-dekra-500 focus:ring-dekra-500"
            />
            <span v-if="errors.valor" class="text-sm text-red-600">{{ errors.valor }}</span>
          </div>
        </div>

        <!-- Descrição -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Descrição <span class="text-red-500">*</span>
          </label>
          <input 
            v-model="form.descricao" 
            type="text" 
            maxlength="255"
            placeholder="Ex: Pagamento de fornecedor X"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-dekra-500 focus:ring-dekra-500"
          />
          <span v-if="errors.descricao" class="text-sm text-red-600">{{ errors.descricao }}</span>
        </div>

        <!-- Forma de Pagamento -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Forma de Pagamento
          </label>
          <select 
            v-model="form.forma_pagamento" 
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-dekra-500 focus:ring-dekra-500"
          >
            <option value="">Selecione</option>
            <option v-for="(label, key) in formas_pagamento" :key="key" :value="key">
              {{ label }}
            </option>
          </select>
          <span v-if="errors.forma_pagamento" class="text-sm text-red-600">{{ errors.forma_pagamento }}</span>
        </div>

        <!-- Empresa (apenas admin) -->
        <div v-if="empresas.length > 0">
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Empresa <span class="text-red-500">*</span>
          </label>
          <select 
            v-model="form.empresa_id" 
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-dekra-500 focus:ring-dekra-500"
          >
            <option value="">Selecione uma empresa</option>
            <option v-for="empresa in empresas" :key="empresa.id" :value="empresa.id">
              {{ empresa.nome_fantasia }}
            </option>
          </select>
          <span v-if="errors.empresa_id" class="text-sm text-red-600">{{ errors.empresa_id }}</span>
        </div>

        <!-- Observações -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Observações
          </label>
          <textarea 
            v-model="form.observacoes" 
            rows="3"
            placeholder="Informações adicionais (opcional)"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-dekra-500 focus:ring-dekra-500"
          ></textarea>
          <span v-if="errors.observacoes" class="text-sm text-red-600">{{ errors.observacoes }}</span>
        </div>

        <!-- Comprovante de Pagamento -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Comprovante de Pagamento
          </label>
          <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-dekra-500 transition-colors">
            <div class="space-y-1 text-center">
              <svg v-if="!form.comprovante_pagamento" class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
              <svg v-else class="mx-auto h-12 w-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <div class="flex text-sm text-gray-600">
                <label for="comprovante_pagamento" class="relative cursor-pointer bg-white rounded-md font-medium text-dekra-600 hover:text-dekra-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-dekra-500">
                  <span v-if="!form.comprovante_pagamento">Adicionar arquivo</span>
                  <span v-else>{{ form.comprovante_pagamento.name }}</span>
                  <input 
                    id="comprovante_pagamento" 
                    type="file" 
                    accept=".pdf,.jpg,.jpeg,.png"
                    @change="handleFileUpload"
                    class="sr-only"
                  />
                </label>
                <p class="pl-1">ou arraste e solte</p>
              </div>
              <p class="text-xs text-gray-500">
                PNG, JPG, JPEG, PDF até 5MB
              </p>
              <button 
                v-if="form.comprovante_pagamento"
                @click="removeFile"
                type="button"
                class="mt-2 text-sm text-red-600 hover:text-red-800"
              >
                Remover arquivo
              </button>
            </div>
          </div>
          <span v-if="errors.comprovante_pagamento" class="text-sm text-red-600">{{ errors.comprovante_pagamento }}</span>
        </div>

        <!-- Botões -->
        <div class="flex justify-end gap-3 pt-4 border-t">
          <Link 
            :href="route('financeiro.index')" 
            class="px-6 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors"
          >
            Cancelar
          </Link>
          <button 
            type="submit"
            :disabled="processing"
            class="px-6 py-2 bg-dekra-800 text-white rounded-md hover:bg-dekra-700 transition-colors disabled:opacity-50"
          >
            {{ processing ? 'Salvando...' : 'Salvar Transação' }}
          </button>
        </div>
      </form>
    </Card>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import Card from '@/Components/Card.vue'

const props = defineProps({
  empresas: Array,
  categorias_entrada: Object,
  categorias_saida: Object,
  formas_pagamento: Object,
  errors: Object
})

const form = ref({
  tipo: 'ENTRADA',
  categoria: '',
  data: new Date().toISOString().split('T')[0],
  valor: '',
  descricao: '',
  forma_pagamento: '',
  empresa_id: '',
  observacoes: '',
  comprovante_pagamento: null
})

const processing = ref(false)

const handleFileUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    // Validar tamanho do arquivo (5MB)
    if (file.size > 5 * 1024 * 1024) {
      alert('O arquivo deve ter no máximo 5MB')
      return
    }
    
    // Validar tipo do arquivo
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf']
    if (!allowedTypes.includes(file.type)) {
      alert('Apenas arquivos PNG, JPG, JPEG e PDF são permitidos')
      return
    }
    
    form.value.comprovante_pagamento = file
  }
}

const removeFile = () => {
  form.value.comprovante_pagamento = null
}

const salvar = () => {
  processing.value = true
  
  // Criar FormData para enviar arquivo
  const formData = new FormData()
  
  // Adicionar todos os campos do form
  Object.keys(form.value).forEach(key => {
    if (form.value[key] !== null && form.value[key] !== '') {
      formData.append(key, form.value[key])
    }
  })
  
  router.post(route('financeiro.store'), formData, {
    onFinish: () => {
      processing.value = false
    }
  })
}
</script>

