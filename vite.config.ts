import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';
import tailwindcss from 'tailwindcss';
import autoprefixer from 'autoprefixer';

export default defineConfig({
  plugins: [
    laravel({
      input: [
        'resources/css/app.css',
        'resources/js/app.ts',
        'packages/core/Admin/resources/assets/css/admin.css',
        'packages/core/Admin/resources/assets/js/app.ts',
        'packages/core/WebApp/resources/assets/css/webapp.css',
        'packages/core/WebApp/resources/assets/js/app.ts',
      ],
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
    postcss: {
      plugins: [tailwindcss(), autoprefixer()],
    },
  },
  resolve: {
    alias: {
      '@': resolve(__dirname, 'resources'),
      '@ugm-core': resolve(__dirname, 'packages/core'),
      '@ugm-integrations': resolve(__dirname, 'packages/integrations'),
    },
  },
  server: {
    host: '0.0.0.0',
    port: 5173,
    watch: {
      usePolling: true,
    },
    hmr: {
      host: 'localhost',
    },
  },
});
