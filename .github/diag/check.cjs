// Geçici: canlı ve dev sitede ziyaretçi gibi son kontrol
const { chromium } = require('playwright');
(async () => {
  const b = await chromium.launch({ channel: 'chrome' });
  for (const host of ['muratvural.com', 'dev.muratvural.com']) {
    const ctx = await b.newContext({ viewport: { width: 1440, height: 900 } });
    const p = await ctx.newPage();
    const T0 = Date.now();
    await p.goto(`https://${host}/tr/`, { waitUntil: 'commit' });
    await p.waitForFunction(() => document.documentElement.lang === 'tr' && document.querySelector('[data-lang-link=en]'), null, { timeout: 30000 });
    await p.waitForTimeout(1500);
    const info = await p.evaluate(() => [document.documentElement.dataset.theme, getComputedStyle(document.body).backgroundColor, document.querySelector('link[rel=canonical]')?.href, !!document.querySelector('script[type=speculationrules]')]);
    console.log(host, 'tema/zemin/canonical/speculation', JSON.stringify(info));
    const t1 = Date.now();
    await p.click('[data-lang-link=en]', { noWaitAfter: true });
    await p.waitForFunction(() => document.documentElement.lang === 'en', null, { timeout: 30000 }).then(() => console.log(host, 'EN geldi', ((Date.now() - t1) / 1000).toFixed(1) + 's', p.url()), (e) => console.log(host, 'EN GELMEDI', e.message.slice(0, 80)));
    await p.click('[data-lang-link=tr]', { noWaitAfter: true });
    await p.waitForFunction(() => document.documentElement.lang === 'tr', null, { timeout: 30000 }).then(() => console.log(host, 'TR geri geldi', p.url()), (e) => console.log(host, 'TR GELMEDI'));
    await ctx.close();
    for (const [tz, loc] of [['Europe/Istanbul', 'en-US'], ['Asia/Ho_Chi_Minh', 'tr-TR'], ['Europe/Moscow', 'ru-RU'], ['America/New_York', 'en-US']]) {
      const c = await b.newContext({ timezoneId: tz, locale: loc });
      const q = await c.newPage();
      await q.goto(`https://${host}/`, { waitUntil: 'commit' });
      await q.waitForURL(/\/(tr|en)\/$/, { timeout: 30000 }).catch(() => {});
      console.log(host, 'ilk açılış', tz, loc, '->', new URL(q.url()).pathname);
      await c.close();
    }
    const r = await (await fetch(`https://${host}/robots.txt`)).text();
    console.log(host, 'robots:', r.replace(/\n/g, ' | '));
  }
  await b.close();
})();
