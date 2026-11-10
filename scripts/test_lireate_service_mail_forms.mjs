/**
 * Мок-проверка mail.php-цепочки на всех страницах услуг (без POST на прод).
 * node scripts/test_lireate_service_mail_forms.mjs
 */
import assert from "node:assert/strict";
import { readFileSync, existsSync } from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";
import vm from "node:vm";

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), "..");
const SLUGS = [
  "registraciya-tovarnogo-znaka",
  "proverka-tovarnogo-znaka",
  "prodlenie-tovarnogo-znaka",
  "registraciya-tovarnogo-znaka-s-oplatoy-za-rezultat",
  "registraciya-tovarnogo-znaka-v-kitae",
  "vozrazhenie-protiv-registracii-tovarnogo-znaka",
  "zashchita-avtorskih-prav",
];

const mailJs = readFileSync(path.join(root, "pages/services/_shared/lireate-lead-mail.js"), "utf8");
const sandbox = { window: {}, FormData: MockFormData };
vm.runInNewContext(mailJs + "\n;this.lireateLeadMail = window.lireateLeadMail;", sandbox);
const api = sandbox.window.lireateLeadMail;

function MockFormData() {
  this._m = new Map();
}
MockFormData.prototype.append = MockFormData.prototype.set = function (k, v) {
  this._m.set(k, String(v));
};

const sharedForm = readFileSync(
  path.join(root, "pages/services/_shared/blocks/lireate-contact-section.inc.php"),
  "utf8"
);
assert.match(sharedForm, /data-lx-mail-action="\/mail\.php"/);
assert.match(sharedForm, /name="tel"/);

for (const slug of SLUGS) {
  const indexPath = path.join(root, "pages/services", slug, "index.php");
  const index = readFileSync(indexPath, "utf8");
  assert.match(index, /lireate-contact-section\.inc\.php/, `${slug}: missing contact include`);
  assert.match(index, /lireate-lead-scripts\.inc\.php/, `${slug}: missing lead scripts include`);
  const contactBlock = path.join(root, "pages/services", slug, "blocks/lireate-contact-section.inc.php");
  const block = existsSync(contactBlock) ? readFileSync(contactBlock, "utf8") : sharedForm;
  assert.match(block, /data-lx-mail-action="\/mail\.php"/, `${slug}: mail action`);
  const mailAsset = path.join(root, "pages/services", slug, "assets/lireate-shared/lireate-lead-mail.js");
  assert.ok(existsSync(mailAsset), `${slug}: run lireate_service_sync_shared.py`);
  console.log("OK", slug);
}

const mockResult = await api.submitMailLead(
  {
    querySelectorAll: () => [],
    getAttribute: (n) => (n === "data-lx-mail-action" ? "/mail.php" : "")
  },
  { phoneDigits: "79600484957", mock: true }
);
assert.equal(mockResult.ok, true);
console.log("test_lireate_service_mail_forms: OK (" + SLUGS.length + " pages)");
