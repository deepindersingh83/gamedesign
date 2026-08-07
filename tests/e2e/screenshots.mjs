/**
 * IT Store — storefront screenshot capture for CI.
 *
 * Walks a small set of front-office URLs on a running PrestaShop and writes a
 * full-page PNG for each (desktop + mobile viewports). The images are uploaded
 * as a CI artifact so a reviewer can eyeball the rendered theme + modules on a
 * real install without booting anything locally.
 *
 * Usage: BASE_URL=http://localhost:8080 node tests/e2e/screenshots.mjs
 */
import { chromium } from 'playwright';
import { mkdir } from 'node:fs/promises';

const BASE_URL = (process.env.BASE_URL || 'http://localhost:8080').replace(/\/$/, '');
const OUT_DIR = process.env.OUT_DIR || 'artifacts/screenshots';

const pages = [
  { name: 'home', url: `${BASE_URL}/` },
  { name: 'category', url: `${BASE_URL}/index.php?controller=category&id_category=2` },
  { name: 'product', url: `${BASE_URL}/index.php?controller=product&id_product=1` },
  { name: 'quote', url: `${BASE_URL}/index.php?fc=module&module=itstorefleetdeals&controller=quote` },
];

const viewports = [
  { label: 'desktop', width: 1440, height: 900 },
  { label: 'mobile', width: 390, height: 844 },
];

async function main() {
  await mkdir(OUT_DIR, { recursive: true });
  const browser = await chromium.launch();
  let failures = 0;

  for (const vp of viewports) {
    const context = await browser.newContext({
      viewport: { width: vp.width, height: vp.height },
      deviceScaleFactor: 1,
    });
    const page = await context.newPage();

    for (const p of pages) {
      const file = `${OUT_DIR}/${p.name}-${vp.label}.png`;
      try {
        const resp = await page.goto(p.url, { waitUntil: 'networkidle', timeout: 45000 });
        const status = resp ? resp.status() : 0;
        // Give lazy assets / count-up animations a beat to settle.
        await page.waitForTimeout(1200);
        await page.screenshot({ path: file, fullPage: true });
        console.log(`captured ${file} (HTTP ${status})`);
        if (status >= 500) {
          console.error(`::error::${p.url} returned HTTP ${status}`);
          failures++;
        }
      } catch (err) {
        console.error(`::error::failed to capture ${p.url}: ${err.message}`);
        failures++;
      }
    }
    await context.close();
  }

  await browser.close();
  // Screenshots are informational: don't fail the build on a capture hiccup,
  // but do surface a non-zero exit if a page actually 5xx'd.
  process.exit(failures > 0 ? 1 : 0);
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
