import { createApp, h, markRaw } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { createPinia } from 'pinia'
import { ZiggyVue } from 'ziggy-js'
import AuthenticatedLayout from './Layouts/AuthenticatedLayout.vue'
import '../css/app.css'

const LayoutRaw = markRaw(AuthenticatedLayout)

createInertiaApp({
  resolve: (name) => {
    const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
    const page = pages[`./Pages/${name}.vue`]

    // 🔑 Aplique o layout padrão só quando a página NÃO definiu "layout"
    // (não sobrescreve se a página usar "layout: null" ou um layout próprio)
    if (page?.default && page.default.layout === undefined) {
      page.default.layout = LayoutRaw
    }

    return page
  },
  setup({ el, App, props, plugin }) {
    const pinia = createPinia()
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(pinia)
      .use(ZiggyVue)
      .mount(el)
  },
})
