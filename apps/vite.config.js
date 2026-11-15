import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
  plugins: [
    laravel({
      input: [
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/css/admin/app.css',
        'resources/css/admin/pages/dashboard.css',
        'resources/css/admin/pages/couple.css',
        'resources/css/admin/pages/our-story.css',
        'resources/css/admin/pages/registry.css',
        'resources/js/admin/app.js',
        'resources/js/admin/preferences.js',
        'resources/js/admin/pages/dashboard.js',
        'resources/js/admin/pages/couple.js',
        'resources/js/admin/pages/our-story.js',
        'resources/js/admin/pages/registry.js',
      ],
      refresh: true,
    }),
    tailwindcss(),
  ],
  server: {
    watch: {
      ignored: ['**/storage/framework/views/**'],
    },
  },
});
