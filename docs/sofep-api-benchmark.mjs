import { readFileSync, writeFileSync } from 'node:fs';
const session = JSON.parse(readFileSync(process.argv[2], 'utf8'));
if (session.purpose !== 'SOFEP isolated MySQL UAT') throw Error('Isolated UAT fixture required');
const base = 'http://127.0.0.1:8011';
const marker = await (await fetch(base + '/__uat/status')).json();
if (marker.database !== session.database) throw Error('Wrong UAT instance');
const login = await (await fetch(base + '/api/admin-login', { method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json' }, body: JSON.stringify({ email: 'uat-admin@sofep.example.test', mat_khau: session.password }) })).json();
if (!login.token) throw Error('UAT login failed');
async function measure(path) {
  const samples = []; let bytes;
  for (let i = 0; i < 8; i++) {
    const start = performance.now();
    const response = await fetch(base + path, { headers: { Authorization: `Bearer ${login.token}`, Accept: 'application/json' } });
    const body = await response.arrayBuffer();
    if (!response.ok) throw Error('HTTP ' + response.status);
    bytes = body.byteLength; if (i) samples.push(performance.now() - start);
  }
  return { samples_ms: samples.map(n => +n.toFixed(2)), median_ms: +samples.sort((a,b) => a-b)[3].toFixed(2), bytes };
}
const result = { checked_at: new Date().toISOString(), fixture_products: 6,
  before_admin_search_full_catalog: await measure('/api/admin/san-pham/get-data'),
  after_admin_search_options: await measure('/api/admin/san-pham/get-data?search_options=1'),
  public_catalog: await measure('/api/client/san-phams?all=1'),
  limitation: 'Local UAT, 7 warm samples after 1 warmup; does not measure production load or browser page speed.' };
await fetch(base + '/api/dang-xuat', { method: 'POST', headers: { Authorization: `Bearer ${login.token}`, Accept: 'application/json' } });
writeFileSync(new URL('./sofep-api-benchmark.json', import.meta.url), JSON.stringify(result, null, 2));
console.log(JSON.stringify(result, null, 2));
