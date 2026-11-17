#!/usr/bin/env python3
"""Тесты профиля оплаты и строки ST00012 для этапа 3."""

import json
import unittest
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
PROFILES = REPO / "pages" / "services" / "_shared" / "lireate-patent-payment-profiles.json"


def build_st00012(fields, sum_rub, purpose):
    sum_kop = int(round(sum_rub * 100))
    parts = [
        "ST00012",
        "Name=" + fields["Name"],
        "PersonalAcc=" + fields["PersonalAcc"],
        "BankName=" + fields["BankName"],
        "BIC=" + fields["BIC"],
        "CorrespAcc=" + fields["CorrespAcc"],
        "PayeeINN=" + fields["PayeeINN"],
        "KPP=" + fields["KPP"],
        "CBC=" + fields["CBC"],
        "OKTMO=" + fields["OKTMO"],
        "Sum=" + str(sum_kop),
        "Purpose=" + purpose.replace("|", " ").strip(),
    ]
    return "|".join(parts)


class PatentPaymentTests(unittest.TestCase):
  @classmethod
  def setUpClass(cls):
    cls.data = json.loads(PROFILES.read_text(encoding="utf-8"))
    cls.profile = cls.data["profiles"]["rospatent_patent_dues"]

  def test_profile_confirmed_and_kbk(self):
    self.assertTrue(self.profile["confirmed"])
    self.assertEqual(self.profile["fields"]["CBC"], "16811505020016000140")
    self.assertEqual(self.profile["fields"]["PayeeINN"], "7730176088")

  def test_st00012_sum_kopecks(self):
    payload = build_st00012(
      self.profile["fields"],
      35000,
      "п.2.1 регистрация заявки",
    )
    self.assertTrue(payload.startswith("ST00012|"))
    self.assertIn("|Sum=3500000|", payload)
    self.assertIn("Purpose=п.2.1 регистрация заявки", payload)

  def test_fips_profile_not_confirmed(self):
    fips = self.data["profiles"]["fips_paid_services"]
    self.assertFalse(fips["confirmed"])


if __name__ == "__main__":
  unittest.main()