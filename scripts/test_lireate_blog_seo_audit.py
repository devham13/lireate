#!/usr/bin/env python3
"""Unit tests for lireate_blog_seo_audit HTML helpers (no network)."""
import sys
import unittest
from pathlib import Path

SCRIPTS = Path(__file__).resolve().parent
sys.path.insert(0, str(SCRIPTS))

from lireate_blog_seo_audit import (  # noqa: E402
    ends_mid_word_cyrillic,
    extract_json_ld_types,
    has_proper_ending,
    parse_page_seo,
    title_length_truncation_risk,
    truncation_flags,
    visible_text,
)

SAMPLE_HTML = """<!DOCTYPE html>
<html lang="ru">
<head>
  <title>Регистрация товарного знака в Роспатенте — LIREATE</title>
  <meta name="description" content="Пошаговая инструкция: как подать заявку и дождаться решения по охране знака.">
  <meta property="og:title" content="Регистрация ТЗ">
  <meta property="og:description" content="Инструкция для предпринимателей">
  <meta property="og:type" content="article">
  <link rel="canonical" href="https://example.test/registraciya-tz/">
  <script type="application/ld+json">
  {"@context":"https://schema.org","@type":"Article","headline":"Регистрация ТЗ"}
  </script>
  <style>.hidden{display:none}</style>
</head>
<body>
  <h1>Первый заголовок</h1>
  <p>Видимый абзац для аудита SEO.</p>
  <script>var x = 1;</script>
  <h1>Второй H1</h1>
</body>
</html>
"""

TRUNCATED_TITLE = "A" * 69 + "ж"

TRUNCATED_DESC = (
    "Длинное описание статьи про товарные знаки и патентное право в РФ "
    "с рекомендациями юристов для малого бизнеса и самозанятых предпринимател"
)


class ParsePageSeoTests(unittest.TestCase):
    def test_extracts_core_fields(self):
        data = parse_page_seo(SAMPLE_HTML)
        self.assertIn("Регистрация товарного знака", data["title"])
        self.assertIn("Пошаговая инструкция", data["meta_description"])
        self.assertEqual(data["canonical"], "https://example.test/registraciya-tz/")
        self.assertEqual(data["h1_count"], "2")
        self.assertIn("Первый заголовок", data["h1_text"])
        self.assertIn("Второй H1", data["h1_text"])
        self.assertIn("og:title=", data["og_tags"])
        self.assertIn("Article", data["json_ld_types"])

    def test_visible_text_skips_script_and_style(self):
        text = visible_text(SAMPLE_HTML, 200)
        self.assertIn("Видимый абзац", text)
        self.assertNotIn("var x", text)
        self.assertNotIn("display:none", text)


class JsonLdTests(unittest.TestCase):
    def test_graph_types(self):
        html = (
            '<script type="application/ld+json">'
            '{"@graph":[{"@type":"WebPage"},{"@type":"BreadcrumbList"}]}'
            "</script>"
        )
        types = extract_json_ld_types(html)
        self.assertEqual(types, ["WebPage", "BreadcrumbList"])


class TruncationTests(unittest.TestCase):
    def test_proper_ending(self):
        self.assertTrue(has_proper_ending("Заголовок статьи."))
        self.assertTrue(has_proper_ending('Ответ на вопрос?'))
        self.assertFalse(has_proper_ending("Обрезанный заголовок без точки"))

    def test_title_length_69_71(self):
        t69 = "x" * 69
        self.assertTrue(title_length_truncation_risk(t69))
        self.assertFalse(title_length_truncation_risk(t69 + "."))

    def test_ends_mid_word_cyrillic_by_length(self):
        self.assertTrue(ends_mid_word_cyrillic(TRUNCATED_TITLE))
        self.assertTrue(ends_mid_word_cyrillic(TRUNCATED_DESC))

    def test_truncation_flags(self):
        flags = truncation_flags(TRUNCATED_TITLE, "Нормальное описание.")
        self.assertIn("title_truncated", flags)
        flags2 = truncation_flags("Короткий title", TRUNCATED_DESC)
        self.assertIn("description_truncated", flags2)


if __name__ == "__main__":
    raise SystemExit(unittest.main())