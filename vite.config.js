import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    server: {
        hmr: {
            host: 'localhost',
        },
        host: '0.0.0.0',
        cors: true,
        headers: {
            'Access-Control-Allow-Origin': '*',
        },
    },
    resolve: {
        alias: {
            '@': '/resources/js',
            '@images': '/resources/img',
        },
    },
    build: {
        chunkSizeWarningLimit: 1600,
        rollupOptions: {
            output: {
                manualChunks: {
                    'vue-i18n': ['vue-i18n']
                }
            }
        }
    }
});
