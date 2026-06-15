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
    server: {
        // Bind on all interfaces inside the container...
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        // ...but advertise a host-reachable URL in the Vite "hot" file, so the
        // browser loads assets from localhost:5173 (0.0.0.0 is not routable).
        origin: 'http://localhost:5173',
        // The app is served from :8080 while Vite serves from :5173. Vite 6+
        // blocks cross-origin asset/HMR requests by default, so explicitly
        // allow the app origin (any localhost/127.0.0.1 port).
        cors: {
            origin: /^https?:\/\/(localhost|127\.0\.0\.1)(:\d+)?$/,
        },
        hmr: {
            host: 'localhost',
        },
        watch: {
            // Polling is more reliable for bind-mounted files on Docker Desktop.
            usePolling: true,
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
