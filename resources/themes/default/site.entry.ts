import { createInertiaApp } from '@inertiajs/vue3';
import { createApp, DefineComponent, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';

import './css/theme.css';

const themeSlug = 'default';
const pages = import.meta.glob<DefineComponent>('./pages/**/*.vue');

createInertiaApp({
    title: (title) => (title ? `${title} — Default Theme` : 'Default Theme'),
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
    progress: { color: '#0f9d58' },
});
