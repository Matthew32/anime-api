#!/usr/bin/env node
const fs = require('fs');
const path = require('path');
const puppeteerExtra = require('puppeteer-extra');
const stealth = require('puppeteer-extra-plugin-stealth');
puppeteerExtra.use(stealth());

async function main() {
  const seriesUrl = process.argv[2] || 'https://hianime.to/watch/one-piece-100';
  const outFile = process.argv[3] || path.join(__dirname, '..', 'storage', 'app', 'private', 'onepiece-embeds.json');

  // Simple flag parsing for proxy support: --proxy=http://host:port
  const proxyFlag = process.argv.find(a => a.startsWith('--proxy='));
  const proxyUrl = proxyFlag ? proxyFlag.split('=')[1] : (process.env.HTTP_PROXY || process.env.http_proxy || null);

  const launchArgs = ['--no-sandbox','--disable-setuid-sandbox'];
  if (proxyUrl) {
    launchArgs.push(`--proxy-server=${proxyUrl}`);
    console.log('Using proxy:', proxyUrl);
  }

  const browser = await puppeteerExtra.launch({ headless: 'new', args: launchArgs });
  const page = await browser.newPage();
  await page.setUserAgent('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36');

  console.log('Navigating to', seriesUrl);
  await page.goto(seriesUrl, { waitUntil: 'domcontentloaded', timeout: 120000 });

  // Wait for any episode anchors to appear
  try {
    await page.waitForSelector('a[href*="?ep="]', { timeout: 45000 });
  } catch (e) {
    console.warn('Episode anchors did not appear within timeout; continuing anyway.');
  }

  // Attempt to scroll to bottom to trigger lazy loads
  try {
    await page.evaluate(async () => {
      const sleep = ms => new Promise(r => setTimeout(r, ms));
      let lastHeight = 0;
      for (let i = 0; i < 10; i++) {
        window.scrollTo(0, document.body.scrollHeight);
        await sleep(1000);
        const h = document.body.scrollHeight;
        if (h === lastHeight) break;
        lastHeight = h;
      }
    });
  } catch (e) {}

  // Collect episode URLs from anchors and from the full HTML via regex
  const urls = await page.evaluate(() => {
    const origin = location.origin;
    const set = new Set();

    const anchors = Array.from(document.querySelectorAll('a[href*="?ep="]'));
    anchors.forEach(a => {
      const href = a.getAttribute('href');
      if (!href) return;
      try {
        const u = new URL(href, origin).toString();
        if (/\/watch\/one-piece-100\?ep=\d+/i.test(u)) set.add(u);
      } catch (e) {}
    });

    const html = document.body.innerHTML;
    const re = /\/watch\/one-piece-100\?ep=(\d+)/gi;
    let m;
    while ((m = re.exec(html)) !== null) {
      const u = new URL(m[0], origin).toString();
      set.add(u);
    }

    return Array.from(set);
  });

  console.log('Found', urls.length, 'episode URLs');

  // If no episode URLs were detected, treat the input as a direct watch page
  if (urls.length === 0 && /^https?:\/\//i.test(seriesUrl)) {
    console.log('No episode list detected; scraping provided page as a single episode.');
    urls.push(seriesUrl);
  }

  // Visit each episode watch URL and try to extract iframe/video sources and AJAX payloads
  const results = [];
  for (const u of urls) {
    const ep = { watch_url: u, iframe_urls: [], video_sources: [], ajax_payloads: [] };
    const p = await browser.newPage();
    await p.setUserAgent('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36');

    // Capture AJAX responses that might include embeds or sources
    p.on('response', async (resp) => {
      try {
        const url = resp.url();
        if (/\/ajax\/episode|embed|sources|server/i.test(url)) {
          const ct = resp.headers()['content-type'] || '';
          let body;
          if (ct.includes('application/json')) {
            body = await resp.json();
          } else {
            body = await resp.text();
          }
          ep.ajax_payloads.push({ url, content_type: ct, body });
        }
      } catch (e) {
        // ignore
      }
    });

    console.log('Visiting episode:', u);
    try {
      await p.goto(u, { waitUntil: 'domcontentloaded', timeout: 120000 });
    } catch (e) {
      console.warn('Failed to load episode page:', u, e.message);
      await p.close();
      results.push(ep);
      continue;
    }

    // Wait a bit for player to initialize
    await new Promise(r => setTimeout(r, 2000));

    // Try to extract iframe and video sources from DOM
    try {
      const domData = await p.evaluate(() => {
        const iframes = Array.from(document.querySelectorAll('iframe')).map(i => i.src).filter(Boolean);
        const videoTags = Array.from(document.querySelectorAll('video')); 
        const videoSrcs = [];
        videoTags.forEach(v => {
          const s = v.getAttribute('src');
          if (s) videoSrcs.push(s);
          const sources = Array.from(v.querySelectorAll('source')).map(s => s.src).filter(Boolean);
          sources.forEach(x => videoSrcs.push(x));
        });

        // Attempt to read data attributes that might contain ids
        const dataAttrs = [];
        document.querySelectorAll('*').forEach(el => {
          for (const attr of el.attributes || []) {
            if (/^data-/.test(attr.name)) {
              dataAttrs.push({ name: attr.name, value: attr.value });
            }
          }
        });

        return { iframes, videoSrcs, dataAttrs };
      });

      ep.iframe_urls = domData.iframes;
      ep.video_sources = domData.videoSrcs;
      ep.data_attributes = domData.dataAttrs;
    } catch (e) {
      // ignore
    }

    await p.close();
    results.push(ep);
  }

  await browser.close();

  // Ensure directory exists and save detailed results
  fs.mkdirSync(path.dirname(outFile), { recursive: true });
  fs.writeFileSync(outFile, JSON.stringify(results, null, 2), 'utf8');
  console.log('Saved details to', outFile);
}

main().catch(err => {
  console.error('Scrape failed:', err);
  process.exit(1);
});