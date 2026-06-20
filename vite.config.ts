import vue from '@vitejs/plugin-vue';
import autoprefixer from 'autoprefixer';
import fs from 'fs';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import { defineConfig } from 'vite';

/**
 * Auto-descubre todos los temas bajo resources/themes/ leyendo sus theme.json
 * y devuelve los paths relativos de sus entries para inyectarlos a Vite.
 */
function discoverThemeEntries(themesDir: string): string[] {
    if (!fs.existsSync(themesDir)) return [];
    return fs
        .readdirSync(themesDir, { withFileTypes: true })
        .filter((entry) => entry.isDirectory())
        .flatMap((entry) => {
            const manifestPath = path.join(themesDir, entry.name, 'theme.json');
            if (!fs.existsSync(manifestPath)) return [];
            try {
                const manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf-8'));
                const entryFile = manifest.entry ?? 'site.entry.ts';
                return [`resources/themes/${entry.name}/${entryFile}`];
            } catch {
                return [];
            }
        });
}

const themeEntries = discoverThemeEntries(path.resolve(__dirname, 'resources/themes'));

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.ts', 'resources/css/app.css', ...themeEntries],
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
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
        },
    },
    server: {
        host: '127.0.0.1',
        port: 5173,
        strictPort: true,
    },
    css: {
        postcss: {
            plugins: [autoprefixer],
        },
    },
});
