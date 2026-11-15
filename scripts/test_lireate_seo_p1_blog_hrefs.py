#!/usr/bin/env python3
"""Unit tests for blog href SQL helpers (no live MySQL)."""
import sys
import unittest
from pathlib import Path

SCRIPTS = Path(__file__).resolve().parent
sys.path.insert(0, str(SCRIPTS))

from lireate_seo_p1_blog_hrefs_lib import (
    ANNULIROVAT_OLD,
    ANNULIROVAT_NEW,
    REPLACEMENTS,
    all_replacement_pairs,
    build_replace_expression,
    mysql_string_literal,
    parse_mysql_string_literal,
    simulate_replace_chain,
    verify_story_body,
)


class MysqlLiteralRoundTripTests(unittest.TestCase):
    def test_roundtrip_special_chars(self):
        sample = (
            "line1\nline2\ttab\\backslash\n"
            "ascii ' single \" double\n"
            "\u2018typo\u2019 \u2019right\u2018\n"
            'href="/path/"'
        )
        lit = mysql_string_literal(sample)
        self.assertEqual(parse_mysql_string_literal(lit), sample)

    def test_annulirovat_literal(self):
        lit_old = mysql_string_literal(ANNULIROVAT_OLD)
        lit_new = mysql_string_literal(ANNULIROVAT_NEW)
        self.assertEqual(parse_mysql_string_literal(lit_old), ANNULIROVAT_OLD)
        self.assertEqual(parse_mysql_string_literal(lit_new), ANNULIROVAT_NEW)


class ReplaceExpressionTests(unittest.TestCase):
    def test_build_nested_replace_contains_pairs(self):
        pairs = all_replacement_pairs()
        sql_expr = build_replace_expression("full_story", pairs)
        self.assertTrue(sql_expr.startswith("REPLACE("))
        self.assertIn("full_story", sql_expr)
        self.assertIn("/blog/kak-opredelit-klass-mktu/", sql_expr)
        self.assertIn("prava_ot_dizajnera", sql_expr)

    def test_simulate_matches_single_replace(self):
        before = '<a href="/blog/kak-opredelit-klass-mktu/">x</a>\n<p>ok</p>'
        after = simulate_replace_chain(before, REPLACEMENTS)
        self.assertNotIn("/blog/kak-opredelit-klass-mktu/", after)
        self.assertIn("/struktura_mktu_kak_opredelit_klass_dlya_tovara_i_uslugi/", after)
        self.assertEqual(before.count("\n"), after.count("\n"))


class VerifyStoryTests(unittest.TestCase):
    def test_verify_ok_after_fix(self):
        old = 'x <a href="/dogovor_na_razrabotku_logotipa_kak_poluchit_prava_ot_dizajnera/">y</a>'
        new = simulate_replace_chain(old, all_replacement_pairs())
        errs = verify_story_body("slug", old, new)
        self.assertEqual(errs, [])


if __name__ == "__main__":
    raise SystemExit(unittest.main())