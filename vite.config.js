import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    server: {
        watch: {
            // Helps avoid transient EBUSY file-lock reads on Windows during save.
            awaitWriteFinish: {
                stabilityThreshold: 180,
                pollInterval: 40,
            },
        },
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/css/style.css', 'resources/js/app.js', 'resources/js/script.js'],
            refresh: true,
        }),
        tailwindcss(),
        vue(),
    ],
});
