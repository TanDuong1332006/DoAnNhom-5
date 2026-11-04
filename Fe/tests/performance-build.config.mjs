import { defineConfig } from 'vite';
import base from '../vite.config.js';
import { resolve } from 'node:path';
import { tmpdir } from 'node:os';

const baseline = process.env.SOFEP_PERF_BASELINE === '1';
export default defineConfig({
  ...base,
  plugins: [...base.plugins, {
    name: 'compare-admin-layout-loading', enforce: 'pre',
    transform(code, id) {
      if (!baseline || !id.replaceAll('\\', '/').endsWith('/src/main.js')) return;
      return code.replace("const Default = defineAsyncComponent(() => import('./layout/wrapper/index.vue'))", "import Default from './layout/wrapper/index.vue'")
        .replace("const Blank = defineAsyncComponent(() => import('./layout/wrapper/blank.vue'))", "import Blank from './layout/wrapper/blank.vue'")
        .replace('router.isReady().then(() => app.mount("#app"))', 'app.mount("#app")');
    },
    transformIndexHtml() {
      // Chỉ hai bản build đo thử có output DOM này; ứng dụng thường không bị chèn script.
      return [{ tag: 'script', injectTo: 'head-prepend', children: `
        (() => {
          function update() {
            if (!document.body) return;
            let output = document.getElementById('sofep-benchmark');
            if (!output) { output = document.createElement('output'); output.id = 'sofep-benchmark'; output.hidden = true; document.body.appendChild(output); }
            const navigation = performance.getEntriesByType('navigation')[0];
            if (!navigation) return;
            const resources = performance.getEntriesByType('resource');
            const scripts = resources.filter(r => new URL(r.name).pathname.endsWith('.js'));
            const catalog = resources.find(r => r.name.includes('/api/client/san-phams?all=1'));
            output.textContent = JSON.stringify({ dom_content_loaded_ms: navigation.domContentLoadedEventEnd, load_event_ms: navigation.loadEventEnd, js_encoded_bytes: scripts.reduce((sum, r) => sum + r.encodedBodySize, 0), js_requests: scripts.length, catalog_response_end_ms: catalog ? catalog.startTime + catalog.duration : null });
            output.dataset.ready = String(Boolean(catalog && navigation.domContentLoadedEventEnd));
          }
          addEventListener('DOMContentLoaded', () => setTimeout(update, 0));
          addEventListener('load', () => setTimeout(update, 0));
          new PerformanceObserver(update).observe({ type: 'resource', buffered: true });
        })();
      ` }];
    },
  }],
  build: { outDir: resolve(tmpdir(), 'sofep-performance-20261103', baseline ? 'before' : 'after'), emptyOutDir: false },
});
