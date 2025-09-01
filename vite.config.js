import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import * as path from 'node:path';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.ts',
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
    css: {
        preprocessorOptions: {
            scss: {
                additionalData: '@import "@scss/_variables.scss";',
            },
        },
    },
    resolve: {
        alias: {
            '@scss': path.resolve(__dirname, 'resources/scss'),
        },
    },
});
