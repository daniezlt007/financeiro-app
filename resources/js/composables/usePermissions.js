import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

/**
 * Composable para verificar permissões do usuário
 * Admin tem acesso total; demais usam permissões do Spatie
 */
export function usePermissions() {
  const page = usePage()
  const user = computed(() => page.props?.auth?.user ?? null)
  const isAdmin = computed(() => user.value?.is_admin ?? false)
  const permissions = computed(() => page.props?.auth?.permissions ?? [])

  function can(permission) {
    if (isAdmin.value) return true
    if (!permission) return false
    return permissions.value.includes(permission)
  }

  function canAny(...perms) {
    if (isAdmin.value) return true
    return perms.some((p) => permissions.value.includes(p))
  }

  function canAll(...perms) {
    if (isAdmin.value) return true
    return perms.every((p) => permissions.value.includes(p))
  }

  return { can, canAny, canAll, isAdmin, permissions, user }
}
