<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Gestão de Permissões</h1>

    <!-- Papéis (Roles) -->
    <Card title="Papéis e Permissões" variant="elevated" class="mb-8">
      <div class="space-y-6">
        <div v-for="role in roles" :key="role.id" class="border rounded-lg p-4">
          <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold text-lg">{{ role.name }}</h3>
            <button
              v-if="!editingRole[role.id]"
              @click="editingRole[role.id] = true"
              class="text-indigo-600 hover:text-indigo-800 text-sm"
            >
              Editar permissões
            </button>
            <div v-else class="flex gap-2">
              <button
                @click="saveRolePermissions(role)"
                class="px-3 py-1 bg-green-600 text-white rounded text-sm hover:bg-green-700"
              >
                Salvar
              </button>
              <button
                @click="editingRole[role.id] = false"
                class="px-3 py-1 bg-gray-300 text-gray-700 rounded text-sm hover:bg-gray-400"
              >
                Cancelar
              </button>
            </div>
          </div>
          <div v-if="editingRole[role.id]" class="grid grid-cols-2 md:grid-cols-4 gap-2 max-h-60 overflow-y-auto">
            <label
              v-for="perm in permissionList"
              :key="perm"
              class="flex items-center gap-2 text-sm"
            >
              <input
                type="checkbox"
                :checked="rolePermissions[role.id]?.includes(perm)"
                @change="toggleRolePermission(role.id, perm, $event.target.checked)"
                class="rounded"
              />
              <span>{{ perm }}</span>
            </label>
          </div>
          <div v-else class="flex flex-wrap gap-1">
            <span
              v-for="p in role.permissions"
              :key="p.id"
              class="px-2 py-0.5 bg-gray-100 rounded text-xs"
            >
              {{ p.name }}
            </span>
            <span v-if="!role.permissions?.length" class="text-gray-400 text-sm">Nenhuma permissão</span>
          </div>
        </div>
      </div>
    </Card>

    <!-- Usuários e Papéis -->
    <Card title="Usuários e Papéis" variant="elevated">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="text-left p-3 border-b font-medium text-gray-900">Usuário</th>
              <th class="text-left p-3 border-b font-medium text-gray-900">Email</th>
              <th class="text-left p-3 border-b font-medium text-gray-900">Tipo</th>
              <th class="text-left p-3 border-b font-medium text-gray-900">Papéis</th>
              <th class="text-center p-3 border-b font-medium text-gray-900">Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="u in users" :key="u.id" class="border-b">
              <td class="p-3">{{ u.name }}</td>
              <td class="p-3">{{ u.email }}</td>
              <td class="p-3">
                <span :class="[
                  'px-2 py-1 rounded-full text-xs',
                  u.is_admin ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'
                ]">
                  {{ u.is_admin ? 'Admin' : 'Funcionário' }}
                </span>
              </td>
              <td class="p-3">
                <div v-if="editingUser[u.id]" class="flex flex-wrap gap-1">
                  <label v-for="r in roles" :key="r.id" class="flex items-center gap-1 text-xs">
                    <input
                      type="checkbox"
                      :checked="userRoles[u.id]?.includes(r.name)"
                      @change="toggleUserRole(u.id, r.name, $event.target.checked)"
                      class="rounded"
                    />
                    {{ r.name }}
                  </label>
                </div>
                <div v-else class="flex flex-wrap gap-1">
                  <span
                    v-for="r in u.roles"
                    :key="r.id"
                    class="px-2 py-0.5 bg-gray-100 rounded text-xs"
                  >
                    {{ r.name }}
                  </span>
                  <span v-if="u.is_admin" class="px-2 py-0.5 bg-purple-100 rounded text-xs">Todas</span>
                  <span v-else-if="!u.roles?.length" class="text-gray-400 text-xs">Nenhum</span>
                </div>
              </td>
              <td class="p-3 text-center">
                <button
                  v-if="!u.is_admin"
                  @click="toggleEditUser(u)"
                  class="text-indigo-600 hover:text-indigo-800 text-sm"
                >
                  {{ editingUser[u.id] ? 'Cancelar' : 'Editar papéis' }}
                </button>
                <button
                  v-if="!u.is_admin && editingUser[u.id]"
                  @click="saveUserRoles(u)"
                  class="ml-2 text-green-600 hover:text-green-800 text-sm"
                >
                  Salvar
                </button>
                <span v-else class="text-gray-400 text-sm">-</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </Card>
  </div>
</template>

<script setup>
import Card from '@/Components/Card.vue'
import { ref, reactive, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  roles: Array,
  permissions: Array,
  users: Array,
  permissionList: Array,
})

const editingRole = reactive({})
const editingUser = reactive({})
const rolePermissions = reactive({})
const userRoles = reactive({})

onMounted(() => {
  props.roles?.forEach(r => {
    rolePermissions[r.id] = [...(r.permissions?.map(p => p.name) || [])]
  })
  props.users?.forEach(u => {
    if (!u.is_admin) {
      userRoles[u.id] = [...(u.roles?.map(r => r.name) || [])]
    }
  })
})

function toggleRolePermission(roleId, perm, checked) {
  if (!rolePermissions[roleId]) rolePermissions[roleId] = []
  if (checked) {
    rolePermissions[roleId].push(perm)
  } else {
    rolePermissions[roleId] = rolePermissions[roleId].filter(p => p !== perm)
  }
}

function toggleUserRole(userId, roleName, checked) {
  if (!userRoles[userId]) userRoles[userId] = []
  if (checked) {
    userRoles[userId].push(roleName)
  } else {
    userRoles[userId] = userRoles[userId].filter(r => r !== roleName)
  }
}

function toggleEditUser(user) {
  if (editingUser[user.id]) {
    editingUser[user.id] = false
  } else {
    editingUser[user.id] = true
    if (!userRoles[user.id]) {
      userRoles[user.id] = [...(user.roles?.map(r => r.name) || [])]
    }
  }
}

function saveRolePermissions(role) {
  router.put(route('permissoes.roles.update', role), {
    permissions: rolePermissions[role.id] || [],
  }, {
    preserveScroll: true,
    onSuccess: () => { editingRole[role.id] = false }
  })
}

function saveUserRoles(user) {
  router.put(route('permissoes.users.update', user), {
    roles: userRoles[user.id] || [],
  }, {
    preserveScroll: true,
    onSuccess: () => { editingUser[user.id] = false }
  })
}
</script>
