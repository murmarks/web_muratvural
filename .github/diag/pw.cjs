// Geçici tanı: Playwright ile gerçek tıklama, ağ ve konsol kaydı. node pw.cjs HOST WAITMS
const { chromium } = require('playwright');
(async () => {
  const [host, wait = '8000'] = process.argv.slice(2);
  const b = await chromium.launch({ channel: 'chrome' });
  const ctx = await b.newContext({ viewport: { width: 1440, height: 900 } });
  const p = await ctx.newPage();
  const T0 = Date.now(); const t = () => ((Date.now() - T0) / 1000).toFixed(1).padStart(5);
  const log = (...a) => console.log(`[${host}] ${t()}`, ...a);
  p.on('console', (m) => (m.type() === 'error' || m.type() === 'warning') && log('console', m.type(), m.text().slice(0, 200)));
  p.on('pageerror', (e) => log('pageerror', e.message.slice(0, 200)));
  p.on('framenavigated', (f) => f === p.mainFrame() && log('NAV', f.url()));
  p.on('request', (r) => r.resourceType() === 'document' && log('REQ', r.method(), r.url(), JSON.stringify(r.headers()['sec-purpose'] || '')));
  p.on('response', async (r) => {
    if (r.request().resourceType() !== 'document') return;
    const h = r.headers();
    log('RES', r.status(), r.url(), 'set-cookie=' + (h['set-cookie'] || '').slice(0, 120), 'cache=' + (h['x-hcdn-cache-status'] || ''));
  });
  p.on('requestfailed', (r) => log('FAIL', r.url(), r.failure()?.errorText));
  await p.goto(`https://${host}/tr/`, { waitUntil: 'commit' });
  await p.waitForTimeout(+wait);
  log('state', JSON.stringify(await p.evaluate(() => [location.href, document.title, document.cookie.slice(0, 200)]).catch((e) => e.message)));
  const btn = p.locator('[data-lang-link=en]');
  log('btn', JSON.stringify(await btn.boundingBox().catch(() => null)));
  await btn.hover().catch((e) => log('hover err', e.message));
  await p.waitForTimeout(600);
  await btn.click({ noWaitAfter: true }).catch((e) => log('click err', e.message));
  log('CLICKED');
  for (let i = 0; i < 30; i++) {
    await p.waitForTimeout(1000);
    const s = await p.evaluate(() => [location.pathname, document.documentElement.lang, document.title.slice(0, 40), !!document.querySelector('.lang-note')]).catch((e) => 'eval:' + e.message.slice(0, 60));
    log('tick', JSON.stringify(s));
    if (Array.isArray(s) && s[1] === 'en' && i > 3) break;
  }
  log('cookies', JSON.stringify((await ctx.cookies()).map((c) => [c.name, c.path, c.domain])));
  await b.close();
})();
