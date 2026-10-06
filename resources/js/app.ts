import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';

import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';

import VChart from 'vue-echarts';

import { use } from 'echarts/core';
import { CanvasRenderer } from 'echarts/renderers';

import {
    LineChart,
    BarChart,
} from 'echarts/charts';

import {
    GridComponent,
    TooltipComponent,
    LegendComponent,
} from 'echarts/components';

use([
    CanvasRenderer,
    LineChart,
    BarChart,
    GridComponent,
    TooltipComponent,
    LegendComponent,
]);

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

void createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),

    setup({ el, App, props, plugin }) {
        const vueApp = createApp({
            render: () => h(App, props),
        });

        vueApp.use(plugin);

        vueApp.component('VChart', VChart);

        if (el) {
            vueApp.mount(el);
        }
    },

    layout: (name) => {
        switch (true) {
            case name === 'Welcome':
                return null;

            case name === 'auth/Login':
            case name === 'auth/Register':
            case name === 'auth/ForgotPassword':
                return null;

            case name.startsWith('auth/'):
                return AuthLayout;

            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];

            default:
                return AppLayout;
        }
    },

    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();

// This will listen for flash toast data from the server...
initializeFlashToast();