import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import AutoImport from 'unplugin-auto-import/vite'
import Components from 'unplugin-vue-components/vite'
import { ElementPlusResolver } from 'unplugin-vue-components/resolvers'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [
    vue(),
    AutoImport({
      dts: false,
      resolvers: [ElementPlusResolver()],
    }),
    Components({
      dts: false,
      resolvers: [ElementPlusResolver()],
    }),
  ],
  define: {
    __VUE_OPTIONS_API__: true,
    __VUE_PROD_DEVTOOLS__: false,
    __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: false,
  },
  build: {
    rollupOptions: {
      output: {
        manualChunks(id) {
          if (!id.includes('node_modules')) {
            return;
          }

          if (id.includes('element-plus')) {
            return 'vendor-element-plus';
          }

          if (id.includes('@element-plus/icons-vue')) {
            return 'vendor-element-icons';
          }

          if (id.includes('vue-router')) {
            return 'vendor-vue-router';
          }

          if (id.includes('pinia')) {
            return 'vendor-pinia';
          }

          if (id.includes('axios')) {
            return 'vendor-axios';
          }

          return 'vendor-common';
        },
      },
    },
  },
})
