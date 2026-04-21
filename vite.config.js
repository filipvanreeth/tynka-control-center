import { defineConfig } from 'vite';
import path from 'node:path';

export default defineConfig({
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        origin: 'https://vite.tynka-control-center.lndo.site',
        hmr: {
            host: 'vite.tynka-control-center.lndo.site',
            port: 5173,
            protocol: 'wss',
        },
    },
    build: {
        outDir: path.resolve(__dirname, 'public/build'),
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            input: {
                app: path.resolve(__dirname, 'resources/js/app.js'),
            },
        },
    },
});