import test from 'node:test';
import assert from 'node:assert/strict';
import vm from 'node:vm';
import { readFileSync } from 'node:fs';

function boot(optionalPlugin) {
  let handlers = 0;
  const chain = new Proxy({}, {
    get(_, key) {
      if (key === 'is') return () => false;
      if (key === 'on') return () => { handlers++; return chain; };
      return () => chain;
    }
  });
  const context = {
    $: value => { if (typeof value === 'function') value(); return chain; },
    window: { location: 'http://uat.test/admin/san-pham' },
    document: { querySelector: () => null },
    ...(optionalPlugin ? { PerfectScrollbar: optionalPlugin } : {})
  };
  vm.runInNewContext(readFileSync(new URL('../src/assets/js/app.js', import.meta.url), 'utf8'), context);
  return handlers;
}

test('Admin startup tiếp tục đăng ký handler khi plugin scrollbar tùy chọn không tồn tại', () => {
  assert.ok(boot() > 0);
});

test('không khởi tạo scrollbar vào các vùng thông báo đã bị bỏ khỏi giao diện', () => {
  let calls = 0;
  boot(function () { calls++; });
  assert.equal(calls, 0);
});
