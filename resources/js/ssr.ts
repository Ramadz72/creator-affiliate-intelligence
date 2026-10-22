import { createSSRApp, h } from 'vue';
import { renderToString } from '@vue/server-renderer';
import { createInertiaApp } from '@inertiajs/vue3';
import createServer from '@inertiajs/vue3/server';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

import VChart from '@/lib/echarts';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

const pages = import.meta.glob('./pages/**/*.vue');

createServer((page) =>
    createInertiaApp({
        page,
        render: renderToString,
        title: (title) => (title ? `${title} - ${appName}` : appName),
        resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, pages),
        setup({ App, props, plugin }) {
            const vueApp = createSSRApp({
                render: () => h(App, props),
            });

            vueApp.use(plugin);
            vueApp.component('VChart', VChart);

            return vueApp;
        },
    }),
);
