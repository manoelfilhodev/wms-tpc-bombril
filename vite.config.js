import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: [
                'app/**/*.php',
                'routes/**/*.php',
                'resources/views/**/*.blade.php',
                'resources/css/**/*.css',
                'resources/js/**/*.js',
                'public/assets/css/**/*.css',
                'public/assets/js/**/*.js',
            ],
        }),
    ],
});
