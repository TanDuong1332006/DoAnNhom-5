import { writeFileSync } from 'node:fs';
import { createHash } from 'node:crypto';
import { productImageUrl, imagePlaceholder } from '../Fe/src/services/productImages.js';

const started = performance.now();
const response = await fetch('http://127.0.0.1:8000/api/client/san-phams?all=1', { headers: { Accept: 'application/json' }, signal: AbortSignal.timeout(30000) });
if (!response.ok) throw Error(`Catalog HTTP ${response.status}`);
const payload = await response.json();
if (!Array.isArray(payload.data)) throw Error('Expected complete catalog array');
const products = payload.data;
const checks = new Map();
let index = 0;
await Promise.all(Array.from({ length: 4 }, async () => {
  while (index < products.length) {
    const p = products[index++], url = productImageUrl(p.hinh_anh);
    if (url === imagePlaceholder) { checks.set(p.id, { valid: false, reason: 'missing_or_unverified' }); continue; }
    try {
      const result = await fetch(new URL(url, 'http://127.0.0.1:5173'), { signal: AbortSignal.timeout(20000) });
      const bytes = Buffer.from(await result.arrayBuffer());
      const hex = bytes.subarray(0, 8).toString('hex');
      const image = hex.startsWith('ffd8ff') || hex === '89504e470d0a1a0a' || (bytes.toString('ascii', 0, 4) === 'RIFF' && bytes.toString('ascii', 8, 12) === 'WEBP');
      checks.set(p.id, { valid: result.ok && image, status: result.status, bytes: bytes.length, sha256: createHash('sha256').update(bytes).digest('hex') });
    } catch (error) { checks.set(p.id, { valid: false, reason: error.name }); }
  }
}));
const rows = products.map(p => ({ id: p.id, sku: p.ma_san_pham, ...checks.get(p.id) }));
const hashes = rows.filter(p => p.valid).map(p => p.sha256);
const result = { checked_at: new Date().toISOString(), selling: products.length, valid_main: rows.filter(p => p.valid).length, coverage_percent: +(rows.filter(p => p.valid).length / products.length * 100).toFixed(2), duplicate_main_hashes: hashes.length - new Set(hashes).size, missing: rows.filter(p => !p.valid), elapsed_ms: Math.round(performance.now() - started), rows };
writeFileSync(new URL('./sofep-catalog-current.json', import.meta.url), JSON.stringify(result, null, 2));
console.log(JSON.stringify({ ...result, rows: undefined }, null, 2));
