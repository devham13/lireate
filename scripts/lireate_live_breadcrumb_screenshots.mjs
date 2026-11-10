#!/usr/bin/env node
/**
 * Live-скриншоты крошек «Услуги» (закрыто/открыто) для всех услуг.
 * node scripts/lireate_live_breadcrumb_screenshots.mjs [outDir]
 */
import puppeteer from "puppeteer-core";
import fs from "node:fs";
import path from "node:path";

const OUT = process.argv[2] || "/opt/cursor/artifacts";
const CHROME = process.env.CHROME_PATH || "/usr/local/bin/google-chrome";
const HOST = (process.env.LIREATE_PUBLIC_SITE_URL || "").replace(/\/$/, "");
if (!HOST) {
  console.error("Set LIREATE_PUBLIC_SITE_URL");
  process.exit(2);
}

const PAGES = [
  { slug: "zashchita_avtorskih_prav", name: "zashchita" },
  { slug: "registraciya_tovarnogo_znaka", name: "registraciya" },
  { slug: "findtz", name: "findtz" },
  { slug: "prodlenie_tovarnogo_znaka", name: "prodlenie" },
  { slug: "registraciya_tovarnogo_znaka_s_oplatoy_za_rezultat", name: "s-oplatoy" },
  { slug: "registraciya_tovarnogo_znaka_v_kitae", name: "v-kitae" },
  { slug: "vozrazhenie_protiv_registracii_tovarnogo_znaka", name: "vozrazhenie" },
];

fs.mkdirSync(OUT, { recursive: true });

const browser = await puppeteer.launch({
  executablePath: CHROME,
  headless: true,
  args: ["--no-sandbox", "--disable-dev-shm-usage"],
});

const results = [];

async function snap(page, file) {
  await page.screenshot({ path: path.join(OUT, file), fullPage: false });
  results.push(file);
}

for (const vp of [
  { w: 1280, h: 900, tag: "1280" },
  { w: 375, h: 812, tag: "375" },
]) {
  for (const p of PAGES) {
    const url = `${HOST.replace(/\/$/, "")}/${p.slug}/`;
    const page = await browser.newPage();
    await page.setViewport({ width: vp.w, height: vp.h });
    const resp = await page.goto(url, { waitUntil: "networkidle0", timeout: 120000 });
    const status = resp ? resp.status() : 0;
    const cssHref = await page.$eval('link[href*="lireate-service-dark"]', (el) => el.getAttribute("href")).catch(() => "");
    await page.evaluate(() => {
      document.querySelectorAll(".lx-reveal").forEach((el) => el.classList.add("lx-active"));
    });
    await snap(page, `live-${p.name}-breadcrumbs-closed-${vp.tag}.png`);
    const btn = await page.$("#lx-breadcrumb-services-trigger");
    if (btn) {
      await btn.click();
      await page.evaluate(() => {
        const menu = document.getElementById("lx-breadcrumb-services-menu");
        const wrap = document.querySelector(".lx-breadcrumb-services");
        if (menu) menu.removeAttribute("hidden");
        if (wrap) wrap.classList.add("is-open");
      });
      await new Promise((r) => setTimeout(r, 400));
      await snap(page, `live-${p.name}-breadcrumbs-open-${vp.tag}.png`);
    }
    results.push(`# ${p.slug} HTTP ${status} css=${cssHref}`);
    await page.close();
  }
}

await browser.close();
fs.writeFileSync(path.join(OUT, "live-breadcrumbs-report.txt"), results.join("\n"), "utf8");
console.log("Wrote", results.filter((r) => r.endsWith(".png")).length, "screenshots to", OUT);
