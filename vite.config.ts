import { defineConfig } from 'vite';
import laravel, { refreshPaths } from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';
import { resolve } from 'path';

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
        'packages/integrations/News/resources/assets/css/app.css',
        'packages/integrations/News/resources/assets/js/app.ts',
      ],
      refresh: [
        ...refreshPaths,
        'packages/integrations/News/resources/views/**',
        'packages/integrations/News/src/View/Components/**',
        'packages/integrations/News/routes/**',
      ],
    }),
    tailwindcss(),
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
      vue: 'vue/dist/vue.esm-bundler.js',
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
