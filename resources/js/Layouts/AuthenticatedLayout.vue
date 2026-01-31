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

const showingNavigationDropdown = ref(false)

// props globais do Inertia
const page = usePage()
const user = computed(() => page.props?.auth?.user ?? null)

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

                <!-- ▼ Vendas (dropdown com subitens) - todos os usuários -->
                <Dropdown align="left" width="48">
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
                    <DropdownLink :href="route('vendas.index')" :class="{ 'font-semibold': route().current('vendas.*') }">
                      Vendas
                    </DropdownLink>
                    <DropdownLink :href="route('relatorios.vendas')" :class="{ 'font-semibold': route().current('relatorios.vendas') }">
                      Relatórios
                    </DropdownLink>
                  </template>
                </Dropdown>
                <!-- ▲ Fim do menu Vendas -->

                <!-- ▼ Pagamentos (dropdown com subitens) - todos os usuários -->
                <Dropdown align="left" width="48">
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
                    <DropdownLink :href="route('pagamentos.index')" :class="{ 'font-semibold': route().current('pagamentos.*') && !route().current('financeiro.baixa-lote') }">
                      Pagamentos
                    </DropdownLink>
                    <DropdownLink :href="route('financeiro.baixa-lote')" :class="{ 'font-semibold': route().current('financeiro.baixa-lote') }">
                      Baixa em Lote
                    </DropdownLink>
                  </template>
                </Dropdown>
                <!-- ▲ Fim do menu Pagamentos -->

                <!-- Links para funcionários (não admin) -->
                <template v-if="!user.is_admin">
                  <NavLink :href="route('clientes.index')" :active="route().current('clientes.*')">
                    Clientes
                  </NavLink>
                  <!---<NavLink :href="route('produtos.index')" :active="route().current('produtos.*')">
                    Produtos
                  </NavLink>-->
                  <NavLink :href="route('servicos.index')" :active="route().current('servicos.*')">
                    Serviços
                  </NavLink>
                </template>

                <!-- Links para admin -->
                <template v-if="user.is_admin">
                  <NavLink :href="route('clientes.index')" :active="route().current('clientes.*')">
                    Clientes
                  </NavLink>
                  <NavLink :href="route('servicos.index')" :active="route().current('servicos.*')">
                    Serviços
                  </NavLink>
                  <NavLink :href="route('produtos.index')" :active="route().current('produtos.*')">
                    Produtos
                  </NavLink>
                  
                  <!-- ▼ Financeiro (dropdown com subitens) - apenas admin -->
                  <Dropdown align="left" width="48">
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
                  <!-- ▲ Fim do menu Financeiro -->
                  
                  <!-- ▼ Configurações (dropdown com subitens) - apenas admin -->
                  <Dropdown align="left" width="48">
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
                      <DropdownLink :href="route('permissoes.index')" :class="{ 'font-semibold': route().current('permissoes.*') }">
                        Permissões
                      </DropdownLink>
                    </template>
                  </Dropdown>
                  <!-- ▲ Fim do menu Configurações -->
                </template>

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

            <!-- Seção Vendas (subitens) - todos os usuários -->
            <div class="px-3 pt-2 text-xs uppercase text-dekra-300">Vendas</div>
            <ResponsiveNavLink :href="route('vendas.index')" :active="route().current('vendas.*')">
              Vendas
            </ResponsiveNavLink>
            <ResponsiveNavLink :href="route('relatorios.vendas')" :active="route().current('relatorios.vendas')">
              Relatórios
            </ResponsiveNavLink>

            <!-- Seção Pagamentos (subitens) - todos os usuários -->
            <div class="px-3 pt-2 text-xs uppercase text-dekra-300">Pagamentos</div>
            <ResponsiveNavLink :href="route('pagamentos.index')" :active="route().current('pagamentos.*') && !route().current('financeiro.baixa-lote')">
              Pagamentos
            </ResponsiveNavLink>
            <ResponsiveNavLink :href="route('financeiro.baixa-lote')" :active="route().current('financeiro.baixa-lote')">
              Baixa em Lote
            </ResponsiveNavLink>

            <!-- Links para funcionários (não admin) -->
            <template v-if="!user.is_admin">
              <ResponsiveNavLink :href="route('clientes.index')" :active="route().current('clientes.*')">
                Clientes
              </ResponsiveNavLink>
              <ResponsiveNavLink :href="route('produtos.index')" :active="route().current('produtos.*')">
                Produtos
              </ResponsiveNavLink>
              <ResponsiveNavLink :href="route('servicos.index')" :active="route().current('servicos.*')">
                Serviços
              </ResponsiveNavLink>
            </template>

            <!-- Links para admin -->
            <template v-if="user.is_admin">
              <ResponsiveNavLink :href="route('clientes.index')" :active="route().current('clientes.*')">
                Clientes
              </ResponsiveNavLink>
              <ResponsiveNavLink :href="route('servicos.index')" :active="route().current('servicos.*')">
                Serviços
              </ResponsiveNavLink>
              <ResponsiveNavLink :href="route('produtos.index')" :active="route().current('produtos.*')">
                Produtos
              </ResponsiveNavLink>
              
              <!-- Submenu Financeiro -->
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
              
              <!-- Submenu Configurações -->
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
                <ResponsiveNavLink :href="route('permissoes.index')" :active="route().current('permissoes.*')">
                  Permissões
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
