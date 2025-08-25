import './bootstrap';
import '../css/app.scss';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { Quasar, Dialog, Notify } from 'quasar'
import quasarLang from 'quasar/lang/es'
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

// Import icon libraries
import '@quasar/extras/material-icons/material-icons.css'

// Import Quasar css
import 'quasar/src/css/index.sass'


const appName = import.meta.env.VITE_APP_NAME || 'Malulu';

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue', { eager: true })),
  setup({ el, App, props, plugin }) {
    const app = createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(Quasar, {
        plugins: {
          Dialog,
          Notify
        }, // import Quasar plugins and add here
        lang: quasarLang,
      })
      .use(ZiggyVue)

    // Make appName available globally
    app.config.globalProperties.$appName = appName

    app.mount(el)
  },
})