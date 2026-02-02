<script setup>
import { ref, computed, watch } from 'vue'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import Dropdown from '@/Components/Dropdown.vue'
import DropdownLink from '@/Components/DropdownLink.vue'
import NavLink from '@/Components/NavLink.vue'
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue'
import NotificationContainer from '@/Components/NotificationContainer.vue'
import { Link, usePage } from '@inertiajs/vue3'
import { useNotificationsStore } from '@/stores/notifications'
import { usePermissions } from '@/composables/usePermissions'

const showingNavigationDropdown = ref(false)
const page = usePage()
const user = computed(() => page.props?.auth?.user ?? null)
const { can, canAny, isAdmin } = usePermissions()

const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''

// Sistema de notificações
const notificationsStore = useNotificationsStore()

// Observar mudanças nas flash messages do Inertia
watch(
  () => page.props.flash,
  (flash) => {
    if (flash?.success) {
      notificationsStore.success('Sucesso!', flash.success)
    }
    if (flash?.error) {
      notificationsStore.error('Erro!', flash.error)
    }
    if (flash?.warning) {
      notificationsStore.warning('Atenção!', flash.warning)
    }
    if (flash?.info) {
      notificationsStore.info('Informação', flash.info)
    }
  },
  { immediate: true, deep: true }
)
</script>

<template>
  <div>
    <div class="min-h-screen bg-gray-100">
      <nav class="border-b border-dekra-700 bg-dekra-800">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div class="flex h-16 justify-between">
            <div class="flex">
              <!-- Logo -->
              <div class="flex shrink-0 items-center">
                <Link :href="route('dashboard')">
                  <ApplicationLogo class="block h-9 w-auto fill-current text-white" />
                </Link>
              </div>

              <!-- Links (apenas se logado) -->
              <div v-if="user" class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                  Dashboard
                </NavLink>

                <!-- ▼ Vendas -->
                <Dropdown v-if="canAny('vendas.ver','vendas.criar','vendas.editar','vendas.excluir','relatorios.vendas')" align="left" width="48">
                  <template #trigger>
                    <button
                      type="button"
                      class="inline-flex items-center h-16 px-3 text-sm font-medium transition text-dekra-200 hover:text-dekra-500"
                    >
                      <span
                        :class="(route().current('vendas.*') || route().current('relatorios.vendas')) ? 'text-dekra-500 font-semibold' : ''"
                      >
                        Vendas
                      </span>
                      <svg class="ms-1 h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/>
                      </svg>
                    </button>
                  </template>
                  <template #content>
                    <DropdownLink v-if="canAny('vendas.ver','vendas.criar','vendas.editar','vendas.excluir')" :href="route('vendas.index')" :class="{ 'font-semibold': route().current('vendas.*') }">
                      Vendas
                    </DropdownLink>
                    <DropdownLink v-if="can('relatorios.vendas')" :href="route('relatorios.vendas')" :class="{ 'font-semibold': route().current('relatorios.vendas') }">
                      Relatórios
                    </DropdownLink>
                  </template>
                </Dropdown>
                <!-- ▲ Fim do menu Vendas -->

                <!-- ▼ Pagamentos -->
                <Dropdown v-if="canAny('pagamentos.ver','pagamentos.criar','pagamentos.editar','pagamentos.excluir','pagamentos.baixa_lote')" align="left" width="48">
                  <template #trigger>
                    <button
                      type="button"
                      class="inline-flex items-center h-16 px-3 text-sm font-medium transition text-dekra-200 hover:text-dekra-500"
                    >
                      <span
                        :class="(route().current('pagamentos.*') || route().current('financeiro.baixa-lote')) ? 'text-dekra-500 font-semibold' : ''"
                      >
                        Pagamentos
                      </span>
                      <svg class="ms-1 h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/>
                      </svg>
                    </button>
                  </template>
                  <template #content>
                    <DropdownLink v-if="canAny('pagamentos.ver','pagamentos.criar','pagamentos.editar','pagamentos.excluir')" :href="route('pagamentos.index')" :class="{ 'font-semibold': route().current('pagamentos.*') && !route().current('financeiro.baixa-lote') }">
                      Pagamentos
                    </DropdownLink>
                    <DropdownLink v-if="can('pagamentos.baixa_lote')" :href="route('financeiro.baixa-lote')" :class="{ 'font-semibold': route().current('financeiro.baixa-lote') }">
                      Baixa em Lote
                    </DropdownLink>
                  </template>
                </Dropdown>
                <!-- ▲ Fim do menu Pagamentos -->

                <!-- ▼ Cadastros (dropdown) - todos os usuários -->
                <Dropdown align="left" width="48">
                  <template #trigger>
                    <button
                      type="button"
                      class="inline-flex items-center h-16 px-3 text-sm font-medium transition text-dekra-200 hover:text-dekra-500"
                    >
                      <span
                        :class="(route().current('clientes.*') || route().current('servicos.*') || route().current('produtos.*') || route().current('parceiros.*')) ? 'text-dekra-500 font-semibold' : ''"
                      >
                        Cadastros
                      </span>
                      <svg class="ms-1 h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/>
                      </svg>
                    </button>
                  </template>
                  <template #content>
                    <DropdownLink :href="route('clientes.index')" :class="{ 'font-semibold': route().current('clientes.*') }">
                      Clientes
                    </DropdownLink>
                    <DropdownLink :href="route('servicos.index')" :class="{ 'font-semibold': route().current('servicos.*') }">
                      Serviços
                    </DropdownLink>
                    <DropdownLink :href="route('parceiros.index')" :class="{ 'font-semibold': route().current('parceiros.*') }">
                      Parceiros
                    </DropdownLink>
                    <DropdownLink v-if="user.is_admin" :href="route('produtos.index')" :class="{ 'font-semibold': route().current('produtos.*') }">
                      Produtos
                    </DropdownLink>
                  </template>
                </Dropdown>
                <!-- ▲ Fim do menu Cadastros -->

                <!-- Financeiro (admin ou gerente) -->
                <Dropdown v-if="canAny('financeiro.ver','financeiro.criar','financeiro.editar','financeiro.excluir')" align="left" width="48">
                    <template #trigger>
                      <button
                        type="button"
                        class="inline-flex items-center h-16 px-3 text-sm font-medium transition text-dekra-200 hover:text-dekra-500"
                      >
                        <span
                          :class="route().current('financeiro.*') ? 'text-dekra-500 font-semibold' : ''"
                        >
                          Financeiro
                        </span>
                        <svg class="ms-1 h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/>
                        </svg>
                      </button>
                    </template>
                    <template #content>
                      <DropdownLink :href="route('financeiro.dashboard')" :class="{ 'font-semibold': route().current('financeiro.dashboard') }">
                        Dashboard Financeiro
                      </DropdownLink>
                      <DropdownLink :href="route('financeiro.index')" :class="{ 'font-semibold': route().current('financeiro.index') || route().current('financeiro.create') || route().current('financeiro.edit') }">
                        Transações
                      </DropdownLink>
                      <DropdownLink :href="route('financeiro.dre-fluxo-caixa')" :class="{ 'font-semibold': route().current('financeiro.dre-fluxo-caixa') }">
                        DRE e Fluxo de Caixa
                      </DropdownLink>
                      <DropdownLink :href="route('financeiro.lucro-despesas')" :class="{ 'font-semibold': route().current('financeiro.lucro-despesas') }">
                        Lucro x Despesas
                      </DropdownLink>
                      <DropdownLink :href="route('financeiro.relatorios')" :class="{ 'font-semibold': route().current('financeiro.relatorios') }">
                        Relatórios
                      </DropdownLink>
                    </template>
                </Dropdown>

                <Dropdown v-if="isAdmin" align="left" width="48">
                    <template #trigger>
                      <button
                        type="button"
                        class="inline-flex items-center h-16 px-3 text-sm font-medium transition text-dekra-200 hover:text-dekra-500"
                      >
                        <span
                          :class="(route().current('empresas.*') || route().current('funcionarios.*') || route().current('users.*') || route().current('audit-logs.*')) ? 'text-dekra-500 font-semibold' : ''"
                        >
                          Configurações
                        </span>
                        <svg class="ms-1 h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/>
                        </svg>
                      </button>
                    </template>
                    <template #content>
                      <DropdownLink :href="route('empresas.index')" :class="{ 'font-semibold': route().current('empresas.*') }">
                        Empresas
                      </DropdownLink>
                      <DropdownLink :href="route('funcionarios.index')" :class="{ 'font-semibold': route().current('funcionarios.*') }">
                        Funcionários
                      </DropdownLink>
                      <DropdownLink :href="route('users.index')" :class="{ 'font-semibold': route().current('users.*') }">
                        Usuários
                      </DropdownLink>
                      <DropdownLink :href="route('audit-logs.index')" :class="{ 'font-semibold': route().current('audit-logs.*') }">
                        Auditoria
                      </DropdownLink>
                    </template>
                </Dropdown>

              </div>
            </div>

            <!-- Usuário (apenas se logado) -->
            <div v-if="user" class="hidden sm:ms-6 sm:flex sm:items-center">
              <div class="relative ms-3">
                <Dropdown align="right" width="48">
                  <template #trigger>
                    <span class="inline-flex rounded-md">
                      <button
                        type="button"
                        class="inline-flex items-center rounded-md border border-transparent bg-dekra-100 px-3 py-2 text-sm font-medium leading-4 text-dekra-800 transition hover:bg-dekra-200 hover:text-dekra-900 focus:outline-none"
                      >
                        {{ user.name || 'Usuário' }}
                        <svg class="-me-0.5 ms-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                      </button>
                    </span>
                  </template>
                  <template #content>
                    <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
                    <DropdownLink :href="route('logout')" method="post" as="button">Log Out</DropdownLink>
                  </template>
                </Dropdown>
              </div>
            </div>

            <!-- Hambúrguer (mobile) -->
            <div v-if="user" class="-me-2 flex items-center sm:hidden">
              <button
                @click="showingNavigationDropdown = !showingNavigationDropdown"
                class="inline-flex items-center justify-center rounded-md p-2 text-dekra-200 transition hover:bg-dekra-700 hover:text-dekra-500 focus:bg-dekra-700 focus:text-dekra-500"
              >
                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                  <path :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                  <path :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Menu responsivo (mobile) -->
        <div v-if="user" :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }" class="sm:hidden">
          <div class="space-y-1 pb-3 pt-2">
            <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
              Dashboard
            </ResponsiveNavLink>

            <template v-if="canAny('vendas.ver','vendas.criar','vendas.editar','vendas.excluir','relatorios.vendas')">
              <div class="px-3 pt-2 text-xs uppercase text-dekra-300">Vendas</div>
              <ResponsiveNavLink v-if="canAny('vendas.ver','vendas.criar','vendas.editar','vendas.excluir')" :href="route('vendas.index')" :active="route().current('vendas.*')">
                Vendas
              </ResponsiveNavLink>
              <ResponsiveNavLink v-if="can('relatorios.vendas')" :href="route('relatorios.vendas')" :active="route().current('relatorios.vendas')">
                Relatórios
              </ResponsiveNavLink>
            </template>

            <template v-if="canAny('pagamentos.ver','pagamentos.criar','pagamentos.editar','pagamentos.excluir','pagamentos.baixa_lote')">
              <div class="px-3 pt-2 text-xs uppercase text-dekra-300">Pagamentos</div>
              <ResponsiveNavLink v-if="canAny('pagamentos.ver','pagamentos.criar','pagamentos.editar','pagamentos.excluir')" :href="route('pagamentos.index')" :active="route().current('pagamentos.*') && !route().current('financeiro.baixa-lote')">
                Pagamentos
              </ResponsiveNavLink>
              <ResponsiveNavLink v-if="can('pagamentos.baixa_lote')" :href="route('financeiro.baixa-lote')" :active="route().current('financeiro.baixa-lote')">
                Baixa em Lote
              </ResponsiveNavLink>
            </template>

            <div class="px-3 pt-2 text-xs uppercase text-dekra-300">Cadastros</div>
            <ResponsiveNavLink :href="route('clientes.index')" :active="route().current('clientes.*')">
              Clientes
            </ResponsiveNavLink>
            <ResponsiveNavLink :href="route('servicos.index')" :active="route().current('servicos.*')">
              Serviços
            </ResponsiveNavLink>
            <ResponsiveNavLink :href="route('parceiros.index')" :active="route().current('parceiros.*')">
              Parceiros
            </ResponsiveNavLink>
            <ResponsiveNavLink v-if="isAdmin" :href="route('produtos.index')" :active="route().current('produtos.*')">
              Produtos
            </ResponsiveNavLink>

            <template v-if="canAny('financeiro.ver','financeiro.criar','financeiro.editar','financeiro.excluir')">
              <div class="border-l-4 border-dekra-500 pl-4 space-y-1">
                <div class="text-xs font-semibold text-dekra-300 uppercase tracking-wider px-4 py-2">
                  Financeiro
                </div>
                <ResponsiveNavLink :href="route('financeiro.dashboard')" :active="route().current('financeiro.dashboard')">
                  Dashboard Financeiro
                </ResponsiveNavLink>
                <ResponsiveNavLink :href="route('financeiro.index')" :active="route().current('financeiro.index') || route().current('financeiro.create') || route().current('financeiro.edit')">
                  Transações
                </ResponsiveNavLink>
                <ResponsiveNavLink :href="route('financeiro.dre-fluxo-caixa')" :active="route().current('financeiro.dre-fluxo-caixa')">
                  DRE e Fluxo de Caixa
                </ResponsiveNavLink>
                <ResponsiveNavLink :href="route('financeiro.lucro-despesas')" :active="route().current('financeiro.lucro-despesas')">
                  Lucro x Despesas
                </ResponsiveNavLink>
                <ResponsiveNavLink :href="route('financeiro.relatorios')" :active="route().current('financeiro.relatorios')">
                  Relatórios
                </ResponsiveNavLink>
              </div>
            </template>

            <template v-if="isAdmin">
              <div class="border-l-4 border-dekra-500 pl-4 space-y-1">
                <div class="text-xs font-semibold text-dekra-300 uppercase tracking-wider px-4 py-2">
                  Configurações
                </div>
                <ResponsiveNavLink :href="route('empresas.index')" :active="route().current('empresas.*')">
                  Empresas
                </ResponsiveNavLink>
                <ResponsiveNavLink :href="route('funcionarios.index')" :active="route().current('funcionarios.*')">
                  Funcionários
                </ResponsiveNavLink>
                <ResponsiveNavLink :href="route('users.index')" :active="route().current('users.*')">
                  Usuários
                </ResponsiveNavLink>
                <ResponsiveNavLink :href="route('audit-logs.index')" :active="route().current('audit-logs.*')">
                  Auditoria
                </ResponsiveNavLink>
              </div>
            </template>
          </div>

          <div class="border-t border-dekra-700 pb-1 pt-4">
            <div class="px-4">
              <div class="text-base font-medium text-white">{{ user?.name || '' }}</div>
              <div class="text-sm font-medium text-dekra-200">{{ user?.email || '' }}</div>
            </div>
            <div class="mt-3 space-y-1">
              <ResponsiveNavLink :href="route('profile.edit')">Profile</ResponsiveNavLink>
              <ResponsiveNavLink :href="route('logout')" method="post" as="button">Log Out</ResponsiveNavLink>
            </div>
          </div>
        </div>
      </nav>

      <!-- Page Heading -->
      <header class="bg-white shadow" v-if="$slots.header">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
          <slot name="header" />
        </div>
      </header>

      <!-- Page Content -->
      <main>
        <slot />
      </main>
    </div>
    
    <!-- Notification Container -->
    <NotificationContainer />
  </div>
</template>
