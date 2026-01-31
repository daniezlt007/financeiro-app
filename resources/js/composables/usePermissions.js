import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

/**
 * Composable para verificar permissões do usuário
 * Admin tem todas as permissões automaticamente
 */
export function usePermissions() {
  const page = usePage()
  
  const permissions = computed(() => {
    return page.props?.auth?.permissions ?? []
  })

  const user = computed(() => page.props?.auth?.user ?? null)

  const isAdmin = computed(() => user.value?.is_admin ?? false)

  function can(permission) {
    if (isAdmin.value) return true
    return permissions.value.includes(permission)
  }

  function canAny(permList) {
    if (isAdmin.value) return true
    return permList.some(p => permissions.value.includes(p))
  }

  function canAll(permList) {
    if (isAdmin.value) return true
    return permList.every(p => permissions.value.includes(p))
  }

  return { can, canAny, canAll, permissions, isAdmin, user }
}
