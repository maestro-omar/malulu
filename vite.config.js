import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { quasar, transformAssetUrls } from '@quasar/vite-plugin';
import { fileURLToPath } from 'url';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.scss', 'resources/js/app.js'],
            refresh: true,
            buildDirectory: 'build',
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),

        // @quasar/plugin-vite options list:
        // https://github.com/quasarframework/quasar/blob/dev/vite-plugin/index.d.ts
        quasar({
            sassVariables: fileURLToPath(
                new URL('./resources/css/quasar-variables.sass', import.meta.url)
            )
        })
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
            '@': path.resolve(__dirname, './resources/js'),
            '@images': path.resolve(__dirname, './resources/img'),
        },
    },
    build: {
        outDir: 'public/build',
        emptyOutDir: true,
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
