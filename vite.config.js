import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        vue(),
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/poll-dashboard.js',
                'resources/js/poll-dashboard-integrated.js',
                // On ajoute notre nouvel entrypoint pour le formulaire
                'resources/js/poll-form.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host: true,
        hmr: {
            host: 'localhost'
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
