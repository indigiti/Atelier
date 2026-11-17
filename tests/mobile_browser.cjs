const assert = require('node:assert/strict');
const { chromium } = require('playwright');
(async () => {
  const base = process.argv[2];
  assert.match(base, /^http:\/\/127\.0\.0\.1:\d+$/);
  const browser = await chromium.launch();
  try {
    for (const width of [320, 375, 390, 430]) {
      const page = await browser.newPage({ viewport: { width, height: 820 }, isMobile: true, hasTouch: true, reducedMotion: 'reduce' });
      const open = async path => {
        const response = await page.goto(base + path);
        assert.equal(response.status(), 200, path);
      };
      await open('/');
      const toggle = page.getByRole('button', { name: 'Open menu', exact: true });
      await toggle.click();
      const menu = page.getByRole('navigation', {name:'Browse Atelier Wood'});
      await menu.getByRole('link', {name:'Shop all', exact:true}).waitFor();
      const links = await menu.locator('a').evaluateAll(anchors => anchors.map(a => a.href));
      for (const href of new Set(links)) {
        const response = await page.request.get(href);
        assert.equal(response.status(), 200, href);
      }
      await page.keyboard.press('Escape');
      await menu.waitFor({state:'hidden'});
      assert.equal(await toggle.getAttribute('aria-expanded'), 'false');
      await toggle.click();
      await menu.getByRole('link', {name:'Shop all', exact:true}).click();
      await page.waitForURL('**/shop');
      for (const type of ['filter','sort']) {
        await page.locator('[data-'+type+'-toggle]').click();
        const sheet = page.locator('[data-'+type+'-sheet]');
        await sheet.waitFor();
        await sheet.locator('.sheet-panel [data-sheet-close]').click();
        await sheet.waitFor({state:'hidden'});
      }
      const product = await page.locator('.product-card .product-image').first().getAttribute('href');
      assert.ok(product);
      await open(product);
      await page.locator('[data-wishlist-heart]').first().click();
      await page.waitForFunction(() => document.querySelector('[data-wishlist-inline]')?.getAttribute('aria-pressed') === 'true');
      await page.locator('.commerce-bottom a').filter({hasText:'Saved'}).click();
      await page.waitForURL('**/wishlist');
      assert.equal(await page.locator('[data-wishlist-count]').textContent(), '1 item');
      await page.locator('.commerce-nav-row .bag').click();
      await page.waitForURL('**/cart');
      for (const path of ['/','/shop','/wishlist','/cart','/account/login',product]) {
        await open(path);
        const overflow = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
        assert.ok(overflow <= 2, path + ' overflows by ' + overflow + 'px at ' + width);
      }
      await page.close();
    }
  } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
