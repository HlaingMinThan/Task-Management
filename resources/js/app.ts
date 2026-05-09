import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createSSRApp, createApp, h, type DefineComponent } from 'vue';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const createAppFn = typeof window !== 'undefined' ? createApp : createSSRApp;

        const appInstance = createAppFn({ render: () => h(App, props) }).use(plugin);

        if (typeof window !== 'undefined') {
            appInstance.mount(el);
        }

        return appInstance;
    },


    progress: {
        color: '#4B5563',
    },
});

