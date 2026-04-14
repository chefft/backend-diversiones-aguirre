import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    build: {
        chunkSizeWarningLimit: 900,
        rollupOptions: {
            output: {
                manualChunks: {
                    three: ['three'],
                    threeExtras: [
                        'three/examples/jsm/controls/OrbitControls.js',
                        'three/examples/jsm/loaders/GLTFLoader.js',
                    ],
                },
            },
        },
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
