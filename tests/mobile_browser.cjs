const assert = require('node:assert/strict');
const { chromium } = require('playwright');
(async () => {
  const base = process.argv[2];
  assert.match(base, /^http:\/\/127\.0\.0\.1:\d+$/);
  const browser = await chromium.launch();
  try {
    for (const width of [320, 375, 390, 430, 533, 767]) {
      const page = await browser.newPage({ viewport: { width, height: 820 }, isMobile: true, hasTouch: true, reducedMotion: 'reduce' });
      const open = async path => {
        const response = await page.goto(base + path);
        assert.equal(response.status(), 200, path);
      };
      await open('/');
      const trustCards = page.locator('.trust-strip .trust-grid > div');
      assert.equal(await trustCards.count(), 4);
      assert.equal(await trustCards.locator('.trust-icon svg:visible').count(), 4);
      const boxes = await trustCards.evaluateAll(cards => cards.map(card => {
        const { x, y } = card.getBoundingClientRect();
        return { x, y };
      }));
      assert.ok(boxes[0].x < boxes[1].x && boxes[0].y === boxes[1].y && boxes[2].y === boxes[3].y && boxes[2].y > boxes[0].y, 'Trust strip must show two columns at ' + width + 'px');
      const toggle = page.getByRole('button', { name: 'Open menu', exact: true });
      const assertHeader = async () => {
        assert.equal(await page.locator('[data-menu-toggle] > svg:visible').count(), 1);
        const cart = page.locator('.commerce-nav-row .bag');
        assert.equal(await cart.locator('svg:visible').count(), 1);
        assert.doesNotMatch(await cart.innerText(), /bag|cart/i);
      };
      await assertHeader();
      await toggle.click();
      await assertHeader();
      assert.equal(await page.getByRole('button', {name:'Close menu', exact:true}).locator('svg path').count(), 2);
      const menu = page.getByRole('navigation', {name:'Browse Atelier Wood'});
      await menu.getByRole('link', {name:'Shop all', exact:true}).waitFor();
      const geometry = await menu.evaluate(panel => {
        const bounds = panel.getBoundingClientRect();
        const header = document.querySelector('[data-header]').getBoundingClientRect();
        const hit = document.elementFromPoint(innerWidth / 2, innerHeight - 12);
        return { gap: Math.abs(bounds.top - header.bottom), reachesBottom: Math.abs(bounds.bottom - innerHeight) <= 2, coversPage: panel.contains(hit), scrollable: panel.scrollHeight > panel.clientHeight };
      });
      assert.ok(geometry.gap <= 2 && geometry.reachesBottom && geometry.coversPage && geometry.scrollable, 'Mobile menu must cover the viewport below the header at ' + width + 'px');
      assert.equal(await page.locator('#main').evaluate(main => main.inert), true);
      await menu.getByRole('link', {name:'Sign in / Register'}).scrollIntoViewIfNeeded();
      assert.ok(await menu.evaluate(panel => panel.scrollTop > 0));
      const links = await menu.locator('a').evaluateAll(anchors => anchors.map(a => a.href));
      for (const href of new Set(links)) {
        const response = await page.request.get(href);
        assert.equal(response.status(), 200, href);
      }
      await page.keyboard.press('Escape');
      await menu.waitFor({state:'hidden'});
      assert.equal(await page.locator('#main').evaluate(main => main.inert), false);
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
        await assertHeader();
        const overflow = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
        assert.ok(overflow <= 2, path + ' overflows by ' + overflow + 'px at ' + width);
      }
      await page.close();
    }
  } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
