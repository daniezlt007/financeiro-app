import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

/**
 * Composable para verificar se o usuário é admin
 * Sistema simplificado: apenas admin ou não
 */
export function usePermissions() {
  const page = usePage()
  const user = computed(() => page.props?.auth?.user ?? null)
  const isAdmin = computed(() => user.value?.is_admin ?? false)

  function can() {
    return isAdmin.value
  }

  function canAny() {
    return isAdmin.value
  }

  function canAll() {
    return isAdmin.value
  }

  return { can, canAny, canAll, isAdmin, user }
}
