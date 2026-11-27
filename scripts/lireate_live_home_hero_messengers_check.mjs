#!/usr/bin/env node
import puppeteer from "puppeteer-core";
import fs from "node:fs";
import path from "node:path";

const HOST = (process.env.LIREATE_PUBLIC_SITE_URL || "").replace(/\/$/, "");
if (!HOST) {
  console.error("Set LIREATE_PUBLIC_SITE_URL");
  process.exit(2);
}
const OUT = process.env.OUT_DIR || "/opt/cursor/artifacts/home-hero-pr137";
const CHROME = process.env.CHROME_PATH || "/usr/local/bin/google-chrome";
fs.mkdirSync(OUT, { recursive: true });

const WIDTHS = [360, 390, 768, 1024, 1440];
const browser = await puppeteer.launch({
  executablePath: CHROME,
  headless: true,
  args: ["--no-sandbox", "--disable-dev-shm-usage"],
});

const report = { ok: false, links: [], checks: {} };

async function audit(page) {
  return page.evaluate(() => {
    const perf = document.querySelector('meta[name="lireate-performance-patch"]')?.content;
    const h1 = document.querySelector("h1, .lh4-root h1, .hero h1");
    const messengerWrap = document.querySelector(".lh4-hero-messengers");
    const oldCta = [...document.querySelectorAll(".hero-actions a")].map((a) => a.textContent.trim());
    const btns = [...document.querySelectorAll(".lh4-hero-messengers a")].map((a) => ({
      label: a.querySelector("span")?.textContent?.trim() || a.getAttribute("aria-label"),
      href: a.href,
      goal: a.getAttribute("data-lx-ym-goal") || "",
    }));
    const overflow = document.documentElement.scrollWidth > window.innerWidth + 1;
  return { perf, h1: h1?.textContent?.trim(), messengerWrap: !!messengerWrap, oldCta, btns, overflow };
  });
}

for (const w of WIDTHS) {
  const page = await browser.newPage();
  await page.setViewport({ width: w, height: 900 });
  await page.goto(HOST + "/", { waitUntil: "networkidle2", timeout: 120000 });
  if (w === 390) {
    await page.screenshot({ path: path.join(OUT, "home-hero-mobile-390.png") });
  }
  if (w === 1440) {
    await page.screenshot({ path: path.join(OUT, "home-hero-desktop-1440.png") });
  }
  const data = await audit(page);
  report.checks[w] = data;
  await page.close();
}

report.links = report.checks[1440]?.btns || report.checks[390]?.btns || [];
report.ok =
  report.links.length === 3 &&
  report.links.some((b) => /whatsapp/i.test(b.label || "")) &&
  report.links.some((b) => /max/i.test(b.label || "")) &&
  report.links.some((b) => /telegram/i.test(b.label || "")) &&
  !Object.values(report.checks).some((c) => c.overflow) &&
  report.checks[390]?.perf === "seo-perf-20261127-v2";

await browser.close();
fs.writeFileSync(path.join(OUT, "report.json"), JSON.stringify(report, null, 2));
console.log(JSON.stringify({ ok: report.ok, links: report.links }, null, 2));
process.exit(report.ok ? 0 : 1);