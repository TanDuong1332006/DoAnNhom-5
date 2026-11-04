import { createServer } from 'node:http';
import { readFile } from 'node:fs/promises';
import { resolve, extname, sep } from 'node:path';
import { tmpdir } from 'node:os';
const mime = { '.html': 'text/html', '.js': 'application/javascript', '.css': 'text/css', '.svg': 'image/svg+xml', '.woff': 'font/woff', '.woff2': 'font/woff2', '.ttf': 'font/ttf', '.png': 'image/png', '.jpg': 'image/jpeg' };
for (const [name, port] of [['before', 5180], ['after', 5181]]) {
  const root = resolve(tmpdir(), 'sofep-performance-20261103', name);
  createServer(async (req, res) => {
    const pathname = decodeURIComponent(new URL(req.url, 'http://localhost').pathname);
    const target = resolve(root, '.' + pathname);
    if (!target.startsWith(root + sep) && target !== root) { res.writeHead(403); res.end(); return; }
    try {
      const file = extname(target) ? target : resolve(root, 'index.html');
      const bytes = await readFile(file);
      res.setHeader('Content-Type', mime[extname(file)] || 'application/octet-stream');
      res.setHeader('Cache-Control', 'no-store'); res.end(bytes);
    } catch { res.writeHead(404); res.end(); }
  }).listen(port, '127.0.0.1', () => console.log(`${name}: http://127.0.0.1:${port}`));
}
