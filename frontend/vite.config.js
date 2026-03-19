import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'
import { fileURLToPath } from 'url'

const __dirname = path.dirname(fileURLToPath(import.meta.url))
/** Корень репозитория (родитель frontend/) — там index.html, src/, public/ */
const repoRoot = path.resolve(__dirname, '..')

export default defineConfig(({ mode }) => ({
  root: repoRoot,
  plugins: [vue()],
  resolve: {
    alias: {
      '@': path.join(repoRoot, 'src'),
    },
  },
  build: {
    outDir: path.join(repoRoot, 'dist'),
    emptyOutDir: true,
    sourcemap: mode !== 'production',
  },
  publicDir: path.join(repoRoot, 'public'),
  server: {
    proxy: {
      '/api': {
        target: 'http://127.0.0.1:8000',
        changeOrigin: true,
      },
    },
  },
}))
