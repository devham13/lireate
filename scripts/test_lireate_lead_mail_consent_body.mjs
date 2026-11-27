/**
 * buildMailLeadBody: ПДн только при checked; marketing off by default (mock FormData, no fetch).
 */
import assert from "node:assert/strict";
import { readFileSync } from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";
import vm from "node:vm";

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), "..");
const mailJs = readFileSync(path.join(root, "pages/services/_shared/lireate-lead-mail.js"), "utf8");

function MockFormData() {
  this._m = new Map();
}
MockFormData.prototype.append = function (k, v) {
  this._m.set(k, String(v));
};
MockFormData.prototype.set = function (k, v) {
  this._m.set(k, String(v));
};
MockFormData.prototype.delete = function (k) {
  this._m.delete(k);
};
MockFormData.prototype.get = function (k) {
  return this._m.has(k) ? this._m.get(k) : null;
};

function mockForm(opts) {
  const pdChecked = opts.pdChecked !== false;
  const mktOn = opts.mktOn === true;
  return {
    querySelectorAll(sel) {
      if (sel !== 'input[type="hidden"]') {
        return [];
      }
      const nodes = [
        { name: "mail_token", value: "t" },
        { name: "mail", value: "mailorder" },
        { name: "form-type", value: "order" },
        { name: "tomail", value: "topic" },
        { name: "consent_form", value: "test" },
        {
          name: "consent_marketing_data",
          value: mktOn ? "1" : "",
          disabled: !mktOn,
        },
        {
          name: "consent_ads",
          value: mktOn ? "1" : "",
          disabled: !mktOn,
        },
      ];
      return nodes;
    },
    querySelector(sel) {
      if (sel === 'input[name="consent_request"]') {
        return { type: "checkbox", checked: pdChecked, value: "1" };
      }
      if (sel === 'input[name="consent_marketing_data"]') {
        return {
          disabled: !mktOn,
          value: mktOn ? "1" : "",
        };
      }
      if (sel === 'input[name="consent_ads"]') {
        return {
          disabled: !mktOn,
          value: mktOn ? "1" : "",
        };
      }
      if (sel === 'input[name="fuck_spamers"]') {
        return { value: "guard" };
      }
      return null;
    },
  };
}

const sandbox = { window: { lireateMailSpamGuard: "g" }, FormData: MockFormData };
vm.runInNewContext(mailJs + "\n;this.lireateLeadMail = window.lireateLeadMail;", sandbox);
const api = sandbox.window.lireateLeadMail;

const offPd = api.buildMailLeadBody(mockForm({ pdChecked: false }), "79600484957", "Test");
assert.equal(offPd.get("consent_request"), null);
assert.equal(offPd.get("consent_marketing_data"), null);

const pdOnly = api.buildMailLeadBody(mockForm({ pdChecked: true }), "79600484957", "Test");
assert.equal(pdOnly.get("consent_request"), "1");
assert.equal(pdOnly.get("consent_marketing_data"), null);

const withMkt = api.buildMailLeadBody(mockForm({ pdChecked: true, mktOn: true }), "79600484957", "Test");
assert.equal(withMkt.get("consent_marketing_data"), "1");
assert.equal(withMkt.get("consent_ads"), "1");

console.log("ok lead mail consent body");