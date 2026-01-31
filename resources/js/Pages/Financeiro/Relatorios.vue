<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    resumo: Object,
    periodo: Object,
    filtros: Object,
    categorias_entrada: Object,
    categorias_saida: Object,
    formas_pagamento: Object,
});

// Filtros
const dataInicio = ref(props.periodo.data_inicio);
const dataFim = ref(props.periodo.data_fim);
const tipo = ref(props.filtros?.tipo || '');
const categoria = ref(props.filtros?.categoria || '');
const status = ref(props.filtros?.status || '');
const formaPagamento = ref(props.filtros?.forma_pagamento || '');

// Categorias dinâmicas baseadas no tipo selecionado
const categorias = computed(() => {
    if (tipo.value === 'ENTRADA') {
        return props.categorias_entrada;
    } else if (tipo.value === 'SAIDA') {
        return props.categorias_saida;
    }
    return { ...props.categorias_entrada, ...props.categorias_saida };
});

function filtrar() {
    router.get(route('financeiro.relatorios'), {
        data_inicio: dataInicio.value,
        data_fim: dataFim.value,
        tipo: tipo.value,
        categoria: categoria.value,
        status: status.value,
        forma_pagamento: formaPagamento.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function limparFiltros() {
    dataInicio.value = new Date().toISOString().split('T')[0].substring(0, 8) + '01';
    const hoje = new Date();
    const ultimoDia = new Date(hoje.getFullYear(), hoje.getMonth() + 1, 0);
    dataFim.value = ultimoDia.toISOString().split('T')[0];
    tipo.value = '';
    categoria.value = '';
    status.value = '';
    formaPagamento.value = '';
    filtrar();
}

function formatarValor(valor) {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(valor || 0);
}

function formatarData(data) {
    if (!data) return '';
    const [ano, mes, dia] = data.split('-');
    return `${dia}/${mes}/${ano}`;
}

function gerarPDF() {
    window.open(route('financeiro.relatorios.pdf', {
        data_inicio: dataInicio.value,
        data_fim: dataFim.value,
        tipo: tipo.value,
        categoria: categoria.value,
        status: status.value,
        forma_pagamento: formaPagamento.value,
    }), '_blank');
}

function imprimirRelatorio() {
    window.print();
}
</script>

<script>
export default { layout: null }
</script>

<template>
    <Head title="Relatórios Financeiros" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Relatórios Financeiros</h2>
                <div class="flex gap-2 no-print">
                    <button
                        @click="imprimirRelatorio"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Imprimir
                    </button>
                    <button
                        @click="gerarPDF"
                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Gerar PDF
                    </button>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filtros -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 no-print">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Filtros</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Data Início</label>
                                <input 
                                    type="date" 
                                    v-model="dataInicio" 
                                    @change="filtrar"
                                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                >
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Data Fim</label>
                                <input 
                                    type="date" 
                                    v-model="dataFim" 
                                    @change="filtrar"
                                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                >
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
                                <select 
                                    v-model="tipo" 
                                    @change="filtrar"
                                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                >
                                    <option value="">Todos</option>
                                    <option value="ENTRADA">Entrada</option>
                                    <option value="SAIDA">Saída</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Categoria</label>
                                <select 
                                    v-model="categoria" 
                                    @change="filtrar"
                                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                >
                                    <option value="">Todas</option>
                                    <option v-for="(label, key) in categorias" :key="key" :value="key">{{ label }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select 
                                    v-model="status" 
                                    @change="filtrar"
                                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                >
                                    <option value="">Todos</option>
                                    <option value="PAGO">Pago</option>
                                    <option value="PENDENTE">Pendente</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Forma de Pagamento</label>
                                <select 
                                    v-model="formaPagamento" 
                                    @change="filtrar"
                                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                >
                                    <option value="">Todas</option>
                                    <option v-for="(label, key) in formas_pagamento" :key="key" :value="key">{{ label }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button 
                                @click="limparFiltros"
                                class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                Limpar Filtros
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Relatório -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 print-content">
                        <!-- Cabeçalho do Relatório -->
                        <div class="text-center mb-6">
                            <h1 class="text-2xl font-bold text-gray-800">Relatório Financeiro</h1>
                            <p class="text-gray-600 mt-2">Período: {{ formatarData(periodo.data_inicio) }} a {{ formatarData(periodo.data_fim) }}</p>
                        </div>

                        <!-- Resumo Geral -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Resumo Geral (Valores Pagos)</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="border rounded-lg p-4">
                                    <div class="text-sm text-gray-600 mb-1">Total de Entradas</div>
                                    <div class="text-2xl font-bold text-green-600">{{ formatarValor(resumo.total_entradas) }}</div>
                                </div>
                                <div class="border rounded-lg p-4">
                                    <div class="text-sm text-gray-600 mb-1">Total de Saídas</div>
                                    <div class="text-2xl font-bold text-red-600">{{ formatarValor(resumo.total_saidas) }}</div>
                                </div>
                                <div class="border rounded-lg p-4">
                                    <div class="text-sm text-gray-600 mb-1">Saldo</div>
                                    <div class="text-2xl font-bold" :class="resumo.saldo >= 0 ? 'text-blue-600' : 'text-red-600'">
                                        {{ formatarValor(resumo.saldo) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pendentes -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Valores Pendentes</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="border rounded-lg p-4">
                                    <div class="text-sm text-gray-600 mb-1">Entradas Pendentes (no período)</div>
                                    <div class="text-2xl font-bold text-yellow-600">{{ formatarValor(resumo.total_entradas_pendentes) }}</div>
                                </div>
                                <div class="border rounded-lg p-4">
                                    <div class="text-sm text-gray-600 mb-1">Saídas Pendentes (no período)</div>
                                    <div class="text-2xl font-bold text-orange-600">{{ formatarValor(resumo.total_saidas_pendentes) }}</div>
                                </div>
                                <div class="border rounded-lg p-4">
                                    <div class="text-sm text-gray-600 mb-1">Total à Receber (geral)</div>
                                    <div class="text-2xl font-bold text-purple-600">{{ formatarValor(resumo.total_pendente) }}</div>
                                    <div class="text-xs text-gray-500 mt-1">{{ resumo.qtd_pendente }} pagamento(s)</div>
                                </div>
                            </div>
                        </div>

                        <!-- Entradas por Categoria (Pagas) -->
                        <div class="mb-8" v-if="resumo.entradas_por_categoria && resumo.entradas_por_categoria.length > 0">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Entradas por Categoria (Pagas)</h3>
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoria</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="item in resumo.entradas_por_categoria" :key="item.categoria">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ item.label }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-green-600">
                                            {{ formatarValor(item.total) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Saídas por Categoria (Pagas) -->
                        <div class="mb-8" v-if="resumo.saidas_por_categoria && resumo.saidas_por_categoria.length > 0">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Saídas por Categoria (Pagas)</h3>
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoria</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="item in resumo.saidas_por_categoria" :key="item.categoria">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ item.label }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-red-600">
                                            {{ formatarValor(item.total) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Entradas Pendentes por Categoria -->
                        <div class="mb-8" v-if="resumo.entradas_pendentes_por_categoria && resumo.entradas_pendentes_por_categoria.length > 0">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Entradas Pendentes por Categoria</h3>
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoria</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="item in resumo.entradas_pendentes_por_categoria" :key="item.categoria">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ item.label }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-yellow-600">
                                            {{ formatarValor(item.total) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Saídas Pendentes por Categoria -->
                        <div class="mb-8" v-if="resumo.saidas_pendentes_por_categoria && resumo.saidas_pendentes_por_categoria.length > 0">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Saídas Pendentes por Categoria</h3>
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoria</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="item in resumo.saidas_pendentes_por_categoria" :key="item.categoria">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ item.label }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-orange-600">
                                            {{ formatarValor(item.total) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@media print {
    .no-print {
        display: none !important;
    }
    
    .print-content {
        page-break-inside: avoid;
    }
    
    body {
        margin: 0;
        padding: 0;
    }
}
</style>

