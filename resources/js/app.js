import './bootstrap';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';

createInertiaApp({
    resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        const component = pages[`./Pages/${name}.vue`];

        if (!component) {
            console.error(`Component not found: ./Pages/${name}.vue`);
            console.log('Available pages:', Object.keys(pages));
            throw new Error(`Page component not found: ${name}`);
        }

        return component.default || component;
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#2563eb', // Blue-600
        showSpinner: true,
    },
});
