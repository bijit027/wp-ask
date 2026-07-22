import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';

export default defineConfig({
  plugins: [vue()],
  base: './',
  build: {
    outDir: 'assets',
    emptyOutDir: true,
    rollupOptions: {
      input: {
        admin: resolve(__dirname, 'src/admin/main.js'),
        frontend: resolve(__dirname, 'src/frontend/wpask.js'),
        'post-rating': resolve(__dirname, 'src/frontend/post-rating.js'),
        heatmap: resolve(__dirname, 'src/frontend/heatmap.js')
      },
      output: {
        entryFileNames: '[name]/[name].js',
        chunkFileNames: 'chunks/[name]-[hash].js',
        assetFileNames: '[name]/[name]-[hash][extname]'
      }
    }
  },
  server: {
    cors: true,
    strictPort: true,
    port: 5173,
    hmr: {
      protocol: 'ws',
      host: 'localhost'
    }
  },
  resolve: {
    alias: {
      '@': resolve(__dirname, 'src')
    }
  }
});
