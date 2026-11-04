import { spawn } from 'node:child_process';
import { writeFileSync } from 'node:fs';
import { randomUUID } from 'node:crypto';
import assert from 'node:assert/strict';

const database = process.argv[2];
assert.match(database || '', /^sofep_uat_[0-9]{8}_[a-f0-9]{12}$/);
function run(...args) {
  return new Promise((resolve, reject) => {
    const proc = spawn('php', ['tests/Support/MySqlUat.php', ...args], { windowsHide: true });
    let output = '', error = '';
    proc.stdout.on('data', chunk => { output += chunk; }); proc.stderr.on('data', chunk => { error += chunk; });
    proc.on('error', reject); proc.on('close', code => {
      if (code !== 0) return reject(new Error(`UAT child failed (${code}): ${error || output}`));
      try { resolve(JSON.parse(output.trim())); } catch { reject(new Error(`Unexpected UAT output: ${output}`)); }
    });
  });
}
const before = await run('inspect', database);
assert.equal(before.orders, 0, 'Use a fresh disposable UAT fixture.');
const stockRace = await Promise.all([run('worker', database, '1'), run('worker', database, '1')]);
assert.deepEqual(stockRace.map(r => r.status).sort(), [201, 422]);
const couponRace = await Promise.all([run('worker', database, '2', 'LAST'), run('worker', database, '3', 'LAST')]);
assert.deepEqual(couponRace.map(r => r.status).sort(), [201, 422]);
const key = randomUUID();
const duplicateRace = await Promise.all([run('worker', database, '4', '', key), run('worker', database, '4', '', key)]);
assert.deepEqual(duplicateRace.map(r => r.status), [201, 201]);
assert.equal(duplicateRace[0].order, duplicateRace[1].order);
const afterRace = await run('inspect', database);
assert.equal(afterRace.orders, 3); assert.equal(afterRace.stock['1'], 0); assert.equal(afterRace.stock['4'], 19); assert.equal(afterRace.coupons.LAST, 1);
const perCustomerRace = await Promise.all([run('worker', database, '5', 'ONCE', '', '1'), run('worker', database, '5', 'ONCE', '', '1')]);
assert.deepEqual(perCustomerRace.map(r => r.status).sort(), [201, 422]);
const lifecycle = await run('lifecycle', database);
assert.equal(lifecycle.cancellation_restored_once, true); assert.equal(lifecycle.voucher_reusable_after_cancel, true); assert.equal(lifecycle.demo_payment_repeatable, true);
const result = { database, checked_at: new Date().toISOString(), stockRace, couponRace, duplicateRace, perCustomerRace, afterRace, lifecycle, passed: true };
writeFileSync('../docs/sofep-mysql-uat.json', JSON.stringify(result, null, 2));
console.log(JSON.stringify(result, null, 2));
