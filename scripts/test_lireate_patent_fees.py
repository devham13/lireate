#!/usr/bin/env python3
"""Unit tests: тарифные примеры и краевые случаи этапа 1 «Патентные задачи»."""

import unittest

from lireate_patent_fees_lib import (
    changes_lines,
    normalize_class_num,
    parse_mktu_text,
    registration_lines,
    renewal_lines,
)


def counts(*pairs):
    return dict(pairs)


class PatentFeeTableTests(unittest.TestCase):
    def test_reg_1_class_10(self):
        r = registration_lines(counts((1, 10)))
        self.assertTrue(r["ok"])
        self.assertEqual(r["total"], 35000)

    def test_reg_1_class_11(self):
        r = registration_lines(counts((1, 11)))
        self.assertEqual(r["total"], 35500)

    def test_reg_2_classes_mixed(self):
        r = registration_lines(counts((1, 12), (2, 8)))
        self.assertEqual(r["total"], 39500)

    def test_reg_5_classes(self):
        r = registration_lines({i: 10 for i in range(1, 6)})
        self.assertEqual(r["total"], 49000)

    def test_reg_6_classes(self):
        r = registration_lines({i: 10 for i in range(1, 7)})
        self.assertEqual(r["total"], 54500)

    def test_reg_6_classes_positions(self):
        r = registration_lines({1: 12, 2: 11, 3: 10, 4: 10, 5: 10, 6: 10})
        self.assertEqual(r["total"], 56000)

    def test_reg_45_classes(self):
        r = registration_lines({i: 10 for i in range(1, 46)})
        self.assertEqual(r["total"], 269000)

    def test_reg_paper(self):
        r = registration_lines(counts((1, 10)), paper=True)
        self.assertEqual(r["total"], 38000)

    def test_renew_1_10(self):
        r = renewal_lines(counts((1, 10)))
        self.assertEqual(r["total"], 22000)

    def test_renew_1_11(self):
        r = renewal_lines(counts((1, 11)))
        self.assertEqual(r["total"], 22500)

    def test_renew_2_classes(self):
        r = renewal_lines(counts((1, 12), (2, 8)))
        self.assertEqual(r["total"], 23000)

    def test_renew_5_classes(self):
        r = renewal_lines({i: 10 for i in range(1, 6)})
        self.assertEqual(r["total"], 22000)

    def test_renew_6_classes(self):
        r = renewal_lines({i: 10 for i in range(1, 7)})
        self.assertEqual(r["total"], 24000)

    def test_renew_6_positions(self):
        r = renewal_lines({1: 12, 2: 11, 3: 10, 4: 10, 5: 10, 6: 10})
        self.assertEqual(r["total"], 25500)

    def test_renew_45(self):
        r = renewal_lines({i: 10 for i in range(1, 46)})
        self.assertEqual(r["total"], 102000)

    def test_renew_extra_term(self):
        r = renewal_lines(counts((1, 10)), extra_term=True)
        self.assertEqual(r["total"], 25000)

    def test_empty_reg(self):
        r = registration_lines({})
        self.assertFalse(r["ok"])

    def test_boundary_5_6_classes_reg(self):
        self.assertEqual(registration_lines({i: 10 for i in range(1, 6)})["total"], 49000)
        self.assertEqual(registration_lines({i: 10 for i in range(1, 7)})["total"], 54500)

    def test_boundary_10_11_pos(self):
        self.assertEqual(registration_lines(counts((1, 10)))["total"], 35000)
        self.assertEqual(registration_lines(counts((1, 11)))["total"], 35500)


class PatentChangesFeeTests(unittest.TestCase):
    def test_216_one_mark_one_change(self):
        r = changes_lines(marks=1, count_216=1)
        self.assertTrue(r["ok"])
        self.assertEqual(r["total"], 3000)

    def test_216_two_marks_three_changes(self):
        r = changes_lines(marks=2, count_216=3)
        self.assertEqual(r["total"], 3000 * 3 * 2)

    def test_217_with_paper(self):
        r = changes_lines(marks=2, count_217=1, paper_217=True, cert_has_info=True)
        self.assertEqual(r["total"], 2000 * 1 * 2 + 3000 * 2)

    def test_217_needs_clarification(self):
        r = changes_lines(marks=1, count_217=1, cert_has_info=False)
        self.assertTrue(any(l.get("needs_clarification") for l in r["lines"]))
        self.assertEqual(r["total"], 2000)


class PatentMktuParseTests(unittest.TestCase):
    def test_comma_in_term(self):
        text = "01\nКофе, зерновой; чай"
        p = parse_mktu_text(text)
        self.assertIn(1, p["classes"])
        terms = " ".join(p["classes"][1])
        self.assertIn("Кофе, зерновой", terms)

    def test_class_00_invalid(self):
        text = "00\nчто-то"
        p = parse_mktu_text(text)
        self.assertEqual(p["counts"], {})

    def test_class_46_invalid(self):
        self.assertIsNone(normalize_class_num("46"))

    def test_empty_list(self):
        p = parse_mktu_text("")
        self.assertEqual(p["counts"], {})


if __name__ == "__main__":
    unittest.main()