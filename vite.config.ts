import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'

export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },
  server: {
    port: 3000,
    host: true,
  },
  preview: {
    host: '0.0.0.0',
    port: 4173,
    allowedHosts: ['marketbw.ru', '.marketbw.ru'],
  },
  build: {
    outDir: 'dist',
    sourcemap: true,
  },
})
