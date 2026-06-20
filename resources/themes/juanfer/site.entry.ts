import { createInertiaApp } from '@inertiajs/vue3';
import { createApp, DefineComponent, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';

import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import './css/theme.css';

const themeSlug = 'juanfer';
const pages = import.meta.glob<DefineComponent>('./pages/**/*.vue');

createInertiaApp({
    title: (title) => (title ? `${title} — JuanFer Seguros` : 'JuanFer Seguros'),
    resolve: (name) => {
        const file = `./pages/${name}.vue`;
        const loader = pages[file];
        if (!loader) {
            throw new Error(`[theme:${themeSlug}] Page "${name}" not found at ${file}`);
        }
        return loader();
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: { color: '#2f71b3' },
});
