import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
  plugins: [
    tailwindcss(),
  ],
  build: {
    outDir: 'dist',
    emptyOutDir: true,
    rollupOptions: {
      input: [
        'resources/css/app.css',
        'resources/js/app.js'
      ],
      output: {
        entryFileNames: 'assets/app.js',
        assetFileNames: 'assets/[name].[ext]', 
      },
    },
  },
});