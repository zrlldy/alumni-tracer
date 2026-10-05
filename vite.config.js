import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';
import vitePluginBundleObfuscator from 'vite-plugin-bundle-obfuscator';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/filament/online/theme.css',
                'resources/js/app.js',
            ],

            refresh: true,

            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),

        tailwindcss(),

        vitePluginBundleObfuscator({
            enable: true,
            autoExcludeNodeModules: true,
            threadPool: true,

            options: {
                compact: true,

                controlFlowFlattening: true,
                controlFlowFlatteningThreshold: 0.75,

                deadCodeInjection: false,

                debugProtection: false,
                disableConsoleOutput: false,

                identifierNamesGenerator: 'hexadecimal',

                renameGlobals: false,

                selfDefending: true,
                simplify: true,

                stringArray: true,
                stringArrayCallsTransform: true,
                stringArrayCallsTransformThreshold: 0.5,
                stringArrayIndexShift: true,
                stringArrayRotate: true,
                stringArrayShuffle: true,
                stringArrayThreshold: 0.75,

                unicodeEscapeSequence: false,
            },
        }),
    ],

    build: {
        minify: 'oxc',
        cssMinify: true,
        sourcemap: false,
    },

    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});