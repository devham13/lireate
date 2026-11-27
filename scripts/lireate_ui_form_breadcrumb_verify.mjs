#!/usr/bin/env node
/**
 * Скриншоты и проверки формы #contact и крошек (до=live, после=live + патч CSS из репо).
 * Без отправки форм. LIREATE_PUBLIC_SITE_URL обязателен для live.
 */
import puppeteer from "puppeteer-core";
import fs from "node:fs";
import path from "node:path";

const OUT = process.argv[2] || "/opt/cursor/artifacts/ui-form-breadcrumbs";
const CHROME = process.env.CHROME_PATH || "/usr/local/bin/google-chrome";
const HOST = (process.env.LIREATE_PUBLIC_SITE_URL || "").replace(/\/$/, "");
if (!HOST) {
  console.error("Set LIREATE_PUBLIC_SITE_URL");
  process.exit(2);
}
const REPO = path.resolve(path.dirname(new URL(import.meta.url).pathname), "..");
const PATCH_CSS = fs.readFileSync(
  path.join(REPO, "pages/services/_shared/lireate-service-dark.css"),
  "utf8"
);

const WIDTHS = [360, 390, 768, 1024, 1440];
const URLS = [
  { path: "/prodlenie_tovarnogo_znaka/", tag: "prodlenie" },
  { path: "/", tag: "home", contact: "#lh4-lead-form" },
];

fs.mkdirSync(OUT, { recursive: true });

const browser = await puppeteer.launch({
  executablePath: CHROME,
  headless: true,
  args: ["--no-sandbox", "--disable-dev-shm-usage"],
});

const report = [];

async function audit(page, mode) {
  const contact = page.locator("#contact form, #lh4-lead-form");
  const boxes = await page.$$eval("label.lx-consent", (labels) =>
    labels.map((lb) => {
      const input = lb.querySelector('input[type="checkbox"]');
      const ir = input?.getBoundingClientRect();
      const lr = lb.getBoundingClientRect();
      const flex = getComputedStyle(lb).display === "flex";
      const sameRow = ir && ir.top < lr.top + 40;
      return { flex, sameRow, consentRequest: input?.name === "consent_request" || input?.required };
    })
  );
  const trigger = await page.$("#lx-breadcrumb-services-trigger");
  const uslugiLink = await page.$('.lx-breadcrumbs a[href="/uslugi/"]');
  const overflow = await page.evaluate(
    () => document.documentElement.scrollWidth > window.innerWidth + 1
  );
  return { boxes, hasDropdown: !!trigger, uslugiLink: !!uslugiLink, overflow, mode };
}

for (const u of URLS) {
  for (const w of WIDTHS) {
    for (const phase of ["before", "after"]) {
      const page = await browser.newPage();
      await page.setViewport({ width: w, height: 900, deviceScaleFactor: 1 });
      await page.goto(HOST + u.path, { waitUntil: "networkidle2", timeout: 120000 });
      await page.evaluate(() => {
        document.querySelectorAll(".lx-reveal").forEach((el) => el.classList.add("lx-active"));
      });
      if (phase === "after" && u.tag === "prodlenie") {
        await page.addStyleTag({ content: PATCH_CSS });
        await page.evaluate(() => {
          const nav = document.querySelector(".lx-breadcrumbs--in-page ol");
          if (nav && document.getElementById("lx-breadcrumb-services-trigger")) {
            nav.innerHTML =
              '<li><a href="/">Главная</a></li><li><a href="/uslugi/">Услуги</a></li>' +
              nav.querySelector("[aria-current]")?.outerHTML;
          }
          document.querySelectorAll("label.lx-consent").forEach((lb) => {
            if (!lb.querySelector(".lx-consent-text")) {
              const input = lb.querySelector("input");
              const rest = [...lb.childNodes].filter((n) => n !== input);
              const span = document.createElement("span");
              span.className = "lx-consent-text";
              rest.forEach((n) => span.appendChild(n));
              lb.appendChild(span);
            }
          });
        });
      }
      if (phase === "after" && u.tag === "home") {
        await page.addStyleTag({
          content: fs.readFileSync(
            path.join(REPO, "template/css/lireate-home-light-v4.css"),
            "utf8"
          ),
        });
      }
      const contactSel = u.contact || "#contact";
      await page.$(contactSel).then((el) => el?.scrollIntoView());
      await new Promise((r) => setTimeout(r, 300));
      const file = `${u.tag}-w${w}-${phase}.png`;
      await page.screenshot({ path: path.join(OUT, file) });
      const metrics = await audit(page, phase);
      report.push({ url: u.path, w, phase, file, ...metrics });
      await page.close();
    }
  }
}

await browser.close();
fs.writeFileSync(path.join(OUT, "report.json"), JSON.stringify(report, null, 2));
const fails = report.filter(
  (r) =>
    r.phase === "after" &&
    (r.hasDropdown || r.overflow || r.boxes.some((b) => !b.flex || !b.sameRow))
);
console.log("screenshots:", OUT, "rows:", report.length, "fails:", fails.length);
if (fails.length) {
  console.error(JSON.stringify(fails, null, 2));
  process.exit(1);
}